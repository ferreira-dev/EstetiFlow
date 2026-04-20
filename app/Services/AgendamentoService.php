<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\BloqueioAgenda;
use App\Models\HorarioFuncionamento;
use App\Models\ItemAgendamento;
use App\Models\HistoricoAgendamento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgendamentoService
{
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
            ->whereIn('status', ['pendente', 'confirmado', 'em_atendimento'])
            ->where('data_hora_inicio', '<', $fim)
            ->where('data_hora_fim', '>', $inicio)
            ->exists();

        if ($conflito) {
            throw new \RuntimeException('Já existe um agendamento confirmado para este horário.');
        }
    }

    /**
     * Cria um agendamento validando disponibilidade antes de inserir.
     */
    public function criar(array $dados): Agendamento
    {
        return DB::transaction(function () use ($dados) {
            $dataHoraInicio = Carbon::parse($dados['data_hora_inicio']);
            $tempoMinutos   = (int) $dados['tempo_execucao_minutos'];
            $dataHoraFim    = $dataHoraInicio->copy()->addMinutes($tempoMinutos);
            $preco          = (float) $dados['preco'];

            // Valida disponibilidade — lança RuntimeException se indisponível
            $this->verificarDisponibilidade(
                $dados['profissional_id'],
                $dataHoraInicio,
                $dataHoraFim,
            );

            $agendamento = Agendamento::create([
                'cliente_id'       => $dados['cliente_id'],
                'profissional_id'  => $dados['profissional_id'],
                'data_hora_inicio' => $dataHoraInicio,
                'data_hora_fim'    => $dataHoraFim,
                'status'           => 'confirmado',
                'valor_total'      => $preco,
            ]);

            ItemAgendamento::create([
                'agendamento_id'         => $agendamento->id,
                'servico_id'             => $dados['servico_id'],
                'nome_servico'           => $dados['nome_servico'],
                'preco_praticado'        => $preco,
                'tempo_execucao_minutos' => $tempoMinutos,
            ]);

            HistoricoAgendamento::create([
                'agendamento_id'  => $agendamento->id,
                'status_anterior' => 'pendente',
                'status_novo'     => 'confirmado',
                'alterado_por_id' => $dados['cliente_id'],
            ]);

            return $agendamento->load(['profissional.estabelecimento', 'itens']);
        });
    }

    /**
     * Cancela um agendamento pelo cliente.
     */
    public function cancelar(int $agendamentoId, int $usuarioId): bool
    {
        return DB::transaction(function () use ($agendamentoId, $usuarioId) {
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

            return true;
        });
    }

    // ── Painel do Profissional ─────────────────────────────────────────────

    private array $transicoesValidas = [
        'pendente'       => ['confirmado', 'cancelado_profissional'],
        'confirmado'     => ['em_atendimento', 'cancelado_profissional', 'nao_compareceu'],
        'em_atendimento' => ['concluido'],
    ];

    /**
     * Lista agendamentos do profissional com filtro opcional por status.
     */
    public function listarDoProfissional(int $profissionalId, ?string $status = null): Collection
    {
        return Agendamento::where('profissional_id', $profissionalId)
            ->when($status && $status !== 'todos', fn($q) => $q->where('status', $status))
            ->with(['cliente', 'itens'])
            ->orderByDesc('data_hora_inicio')
            ->get();
    }

    /**
     * Altera o status de um agendamento respeitando as transições válidas.
     *
     * @throws \InvalidArgumentException quando a transição não é permitida.
     */
    public function alterarStatus(int $agendamentoId, int $profissionalId, string $novoStatus, int $userId): Agendamento
    {
        return DB::transaction(function () use ($agendamentoId, $profissionalId, $novoStatus, $userId) {
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
    }
}
