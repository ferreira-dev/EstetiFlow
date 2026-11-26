<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\BloqueioAgenda;
use App\Models\HorarioFuncionamento;
use App\Models\ItemAgendamento;
use App\Models\HistoricoAgendamento;
use App\Models\Profissional;
use App\Models\ServicoProfissional;
use App\Models\User;
use App\Notifications\Agendamento\NovaReservaNotification;
use App\Notifications\Agendamento\ReservaCanceladaPeloClienteNotification;
use App\Notifications\Agendamento\ReservaCanceladaPeloProfissionalNotification;
use App\Notifications\Agendamento\ReservaConfirmadaNotification;
use App\Support\Telefone;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgendamentoService
{
    private const MSG_SERVICO_INDISPONIVEL = 'Este serviço não está mais disponível para agendamento. Atualize a página e tente novamente.';

    /** Quantos dias à frente o cliente pode reservar pela página pública. */
    public const JANELA_RESERVA_DIAS = 30;

    /**
     * Lista agendamentos do cliente autenticado.
     */
    public function listarDoUsuario(int $usuarioId): Collection
    {
        return Agendamento::where('cliente_id', $usuarioId)
            ->with(['profissional.estabelecimento', 'itens'])
            ->orderByDesc('data_hora_inicio')
            ->get();
    }

    /**
     * Últimos N agendamentos confirmados/pendentes futuros do cliente (para Home).
     */
    public function recentesConfirmados(int $usuarioId, int $limite = 3): Collection
    {
        return Agendamento::where('cliente_id', $usuarioId)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('data_hora_inicio', '>=', now())
            ->with(['profissional.estabelecimento', 'itens'])
            ->orderBy('data_hora_inicio')
            ->limit($limite)
            ->get();
    }

    /**
     * Verifica se o slot [inicio, fim] está disponível para o profissional.
     *
     * @throws \RuntimeException com mensagem amigável se indisponível.
     */
    public function verificarDisponibilidade(int $profissionalId, Carbon $inicio, Carbon $fim): void
    {
        $diaSemana = $inicio->dayOfWeek; // 0=Dom … 6=Sáb

        // ── 1. Horário de funcionamento ───────────────────────────────────
        $horario = HorarioFuncionamento::where('profissional_id', $profissionalId)
            ->where('dia_semana', $diaSemana)
            ->first();

        if (!$horario) {
            throw new \RuntimeException('O profissional não atende neste dia da semana.');
        }

        // Compara apenas hora/minuto, independente da data
        $funcInicio = Carbon::createFromTimeString($horario->hora_inicio);
        $funcFim    = Carbon::createFromTimeString($horario->hora_fim);
        $slotInicio = Carbon::createFromTimeString($inicio->format('H:i:s'));
        $slotFim    = Carbon::createFromTimeString($fim->format('H:i:s'));

        if ($slotInicio->lt($funcInicio) || $slotFim->gt($funcFim)) {
            throw new \RuntimeException(
                "Este horário está fora do período de atendimento ({$horario->hora_inicio} – {$horario->hora_fim})."
            );
        }

        // ── 2. Bloqueios não-recorrentes —————————————————————————————————
        // Sobreposição: NOT (data_fim <= inicio OR data_inicio >= fim)
        $bloqueado = BloqueioAgenda::where('profissional_id', $profissionalId)
            ->where('recorrente', false)
            ->whereNotNull('data_inicio')
            ->where('data_inicio', '<', $fim)
            ->where('data_fim', '>', $inicio)
            ->exists();

        if ($bloqueado) {
            throw new \RuntimeException('Este horário está bloqueado na agenda do profissional.');
        }

        // ── 3. Bloqueios recorrentes ──────────────────────────────────────
        $bloqueiosRec = BloqueioAgenda::where('profissional_id', $profissionalId)
            ->where('recorrente', true)
            ->get();

        foreach ($bloqueiosRec as $b) {
            // Verifica se aplica ao dia da semana
            if ($b->dias_semana !== null && !in_array($diaSemana, $b->dias_semana)) {
                continue;
            }

            // Verifica sobreposição de horário
            $bInicio = Carbon::createFromTimeString($b->hora_inicio);
            $bFim    = Carbon::createFromTimeString($b->hora_fim);

            $overlap = !($slotFim->lte($bInicio) || $slotInicio->gte($bFim));
            if ($overlap) {
                throw new \RuntimeException(
                    "Este horário está bloqueado ({$b->hora_inicio} – {$b->hora_fim})."
                );
            }
        }

        // ── 4. Conflito com agendamento existente ─────────────────────────
        $conflito = Agendamento::where('profissional_id', $profissionalId)
            ->whereIn('status', Agendamento::STATUS_OCUPAM_AGENDA)
            ->where('data_hora_inicio', '<', $fim)
            ->where('data_hora_fim', '>', $inicio)
            ->exists();

        if ($conflito) {
            throw new \RuntimeException('Já existe um agendamento confirmado para este horário.');
        }
    }

    /**
     * Cria um agendamento validando disponibilidade antes de inserir.
     *
     * Preço, duração e nome do serviço são sempre derivados de servicos_profissionais/servicos
     * (nunca do request). Com $profissionalId informado (fluxo do painel), o serviço precisa
     * pertencer a esse profissional e as regras de agenda online não se aplicam.
     *
     * @param array $dados servico_profissional_id, cliente_id, data_hora_inicio, alterado_por_id
     * @throws \RuntimeException com mensagem amigável se o serviço ou o horário estiver indisponível.
     */
    public function criar(array $dados, ?int $profissionalId = null): Agendamento
    {
        $agendamento = DB::transaction(function () use ($dados, $profissionalId) {
            $servicoProfissional = ServicoProfissional::with('servico')
                ->find($dados['servico_profissional_id']);

            if (!$servicoProfissional) {
                throw new \RuntimeException(self::MSG_SERVICO_INDISPONIVEL);
            }

            // Trava a linha do profissional: serializa a criação de agendamentos por profissional
            // (evita reserva dupla por condição de corrida) sem bloquear os demais.
            $profissional = Profissional::with('estabelecimento')
                ->whereKey($servicoProfissional->profissional_id)
                ->lockForUpdate()
                ->first();

            $this->validarServicoAgendavel($servicoProfissional, $profissional, $profissionalId);

            $dataHoraInicio = Carbon::parse($dados['data_hora_inicio']);
            $tempoMinutos   = $servicoProfissional->tempo_execucao_minutos;
            $dataHoraFim    = $dataHoraInicio->copy()->addMinutes($tempoMinutos);
            $preco          = $servicoProfissional->preco;

            // Fluxo do cliente: limita a antecedência da reserva (o painel do profissional não tem limite)
            if ($profissionalId === null && $dataHoraInicio->gt(now()->addDays(self::JANELA_RESERVA_DIAS)->endOfDay())) {
                throw new \RuntimeException(
                    'Reservas online podem ser feitas com até ' . self::JANELA_RESERVA_DIAS . ' dias de antecedência.'
                );
            }

            // Valida disponibilidade — lança RuntimeException se indisponível
            $this->verificarDisponibilidade(
                $profissional->id,
                $dataHoraInicio,
                $dataHoraFim,
            );

            $agendamento = Agendamento::create([
                'cliente_id'       => $dados['cliente_id'],
                'profissional_id'  => $profissional->id,
                'data_hora_inicio' => $dataHoraInicio,
                'data_hora_fim'    => $dataHoraFim,
                'status'           => 'confirmado',
                'valor_total'      => $preco,
            ]);

            ItemAgendamento::create([
                'agendamento_id'         => $agendamento->id,
                'servico_id'             => $servicoProfissional->servico_id,
                'nome_servico'           => $servicoProfissional->servico->nome,
                'preco_praticado'        => $preco,
                'tempo_execucao_minutos' => $tempoMinutos,
            ]);

            HistoricoAgendamento::create([
                'agendamento_id'  => $agendamento->id,
                'status_anterior' => 'pendente',
                'status_novo'     => 'confirmado',
                'alterado_por_id' => $dados['alterado_por_id'],
            ]);

            return $agendamento->load(['profissional.estabelecimento', 'itens']);
        });

        // E-mails só para reservas feitas pelo cliente na página pública
        // (no painel o profissional avisa o cliente pelo botão de WhatsApp)
        if ($profissionalId === null) {
            $agendamento->load(['cliente', 'profissional.usuario']);
            $this->notificar($agendamento->profissional->usuario, new NovaReservaNotification($agendamento));
            $this->notificar($agendamento->cliente, new ReservaConfirmadaNotification($agendamento));
        }

        return $agendamento;
    }

    /**
     * Envia o e-mail (via fila) apenas para usuários com e-mail — pré-cadastros não têm.
     */
    private function notificar(?User $destinatario, Notification $notificacao): void
    {
        if ($destinatario?->email) {
            $destinatario->notify($notificacao);
        }
    }

    /**
     * Garante que o vínculo serviço/profissional pode receber agendamentos.
     *
     * @throws \RuntimeException
     */
    private function validarServicoAgendavel(
        ServicoProfissional $servicoProfissional,
        ?Profissional $profissional,
        ?int $profissionalId,
    ): void {
        if (!$profissional || !$servicoProfissional->ativo || !$servicoProfissional->servico?->ativo) {
            throw new \RuntimeException(self::MSG_SERVICO_INDISPONIVEL);
        }

        // Fluxo do painel: o profissional só agenda os próprios serviços
        if ($profissionalId !== null) {
            if ($profissional->id !== $profissionalId) {
                throw new \RuntimeException(self::MSG_SERVICO_INDISPONIVEL);
            }

            return;
        }

        // Fluxo do cliente: respeita as configurações de agendamento online
        $estabelecimento = $profissional->estabelecimento;

        if (
            !$profissional->aceita_agendamentos
            || !$estabelecimento
            || !$estabelecimento->ativo
            || !$estabelecimento->agenda_online
        ) {
            throw new \RuntimeException(self::MSG_SERVICO_INDISPONIVEL);
        }
    }

    /**
     * Cancela um agendamento pelo cliente.
     */
    public function cancelar(int $agendamentoId, int $usuarioId): bool
    {
        $agendamento = DB::transaction(function () use ($agendamentoId, $usuarioId) {
            $agendamento = Agendamento::where('id', $agendamentoId)
                ->where('cliente_id', $usuarioId)
                ->whereIn('status', ['pendente', 'confirmado'])
                ->firstOrFail();

            $statusAnterior = $agendamento->status;

            $agendamento->update([
                'status'           => 'cancelado_cliente',
                'cancelado_por_id' => $usuarioId,
            ]);

            HistoricoAgendamento::create([
                'agendamento_id'  => $agendamento->id,
                'status_anterior' => $statusAnterior,
                'status_novo'     => 'cancelado_cliente',
                'alterado_por_id' => $usuarioId,
            ]);

            return $agendamento;
        });

        $agendamento->load(['cliente', 'itens', 'profissional.usuario', 'profissional.estabelecimento']);
        $this->notificar($agendamento->profissional?->usuario, new ReservaCanceladaPeloClienteNotification($agendamento));

        return true;
    }

    // ── Painel do Profissional ─────────────────────────────────────────────

    private array $transicoesValidas = [
        'pendente'       => ['confirmado', 'cancelado_profissional'],
        'confirmado'     => ['em_atendimento', 'cancelado_profissional', 'nao_compareceu'],
        'em_atendimento' => ['concluido'],
    ];

    /**
     * Lista agendamentos do profissional com filtros (status, cliente, datas).
     */
    public function listarDoProfissional(int $profissionalId, array $filtros = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Agendamento::where('profissional_id', $profissionalId)
            ->with(['cliente.perfil', 'itens'])
            ->orderByDesc('data_hora_inicio');

        if (!empty($filtros['status']) && $filtros['status'] !== 'todos') {
            $query->where('status', $filtros['status']);
        }

        if (!empty($filtros['cliente'])) {
            $termo          = $filtros['cliente'];
            $termoTelefone  = Telefone::normalizar($termo);
            $query->whereHas('cliente', function ($q) use ($termo, $termoTelefone) {
                $q->where('nome_completo', 'like', "%{$termo}%")
                  ->orWhere('email', 'like', "%{$termo}%")
                  ->when($termoTelefone, fn ($q1) => $q1->orWhereHas('perfil', function ($q2) use ($termoTelefone) {
                      $q2->where('telefone', 'like', "%{$termoTelefone}%");
                  }));
            });
        }

        if (!empty($filtros['data_inicio'])) {
            $query->where('data_hora_inicio', '>=', Carbon::parse($filtros['data_inicio'])->startOfDay());
        }

        if (!empty($filtros['data_fim'])) {
            $query->where('data_hora_inicio', '<=', Carbon::parse($filtros['data_fim'])->endOfDay());
        }

        return $query->paginate(15);
    }

    /**
     * Altera o status de um agendamento respeitando as transições válidas.
     *
     * @throws \InvalidArgumentException quando a transição não é permitida.
     */
    public function alterarStatus(int $agendamentoId, int $profissionalId, string $novoStatus, int $userId): Agendamento
    {
        $agendamento = DB::transaction(function () use ($agendamentoId, $profissionalId, $novoStatus, $userId) {
            $agendamento = Agendamento::where('id', $agendamentoId)
                ->where('profissional_id', $profissionalId)
                ->firstOrFail();

            $statusAtual = $agendamento->status;
            $permitidos  = $this->transicoesValidas[$statusAtual] ?? [];

            if (!in_array($novoStatus, $permitidos)) {
                throw new \InvalidArgumentException(
                    "Transição inválida: '{$statusAtual}' → '{$novoStatus}'."
                );
            }

            $agendamento->update(['status' => $novoStatus]);

            HistoricoAgendamento::create([
                'agendamento_id'  => $agendamento->id,
                'status_anterior' => $statusAtual,
                'status_novo'     => $novoStatus,
                'alterado_por_id' => $userId,
            ]);

            return $agendamento->refresh();
        });

        // Cancelamento/recusa pelo profissional avisa o cliente
        if ($novoStatus === 'cancelado_profissional') {
            $agendamento->load(['cliente', 'itens', 'profissional.estabelecimento']);
            $this->notificar($agendamento->cliente, new ReservaCanceladaPeloProfissionalNotification($agendamento));
        }

        return $agendamento;
    }
}
