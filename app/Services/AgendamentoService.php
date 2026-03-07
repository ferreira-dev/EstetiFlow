<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\ItemAgendamento;
use App\Models\HistoricoAgendamento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgendamentoService
{
    /**
     * Lista agendamentos do usuário autenticado com relacionamentos.
     */
    public function listarDoUsuario(int $usuarioId): Collection
    {
        return Agendamento::where('cliente_id', $usuarioId)
            ->with([
                'profissional.estabelecimento',
                'itens',
            ])
            ->orderByDesc('data_hora_inicio')
            ->get();
    }

    /**
     * Últimos N agendamentos confirmados do usuário (para Home).
     */
    public function recentesConfirmados(int $usuarioId, int $limite = 3): Collection
    {
        return Agendamento::where('cliente_id', $usuarioId)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('data_hora_inicio', '>=', now())
            ->with([
                'profissional.estabelecimento',
                'itens',
            ])
            ->orderBy('data_hora_inicio')
            ->limit($limite)
            ->get();
    }

    /**
     * Cria um agendamento com snapshot do serviço em itens_agendamentos.
     */
    public function criar(array $dados): Agendamento
    {
        return DB::transaction(function () use ($dados) {
            $dataHoraInicio = Carbon::parse($dados['data_hora_inicio']);
            $tempoMinutos = (int) $dados['tempo_execucao_minutos'];
            $dataHoraFim = $dataHoraInicio->copy()->addMinutes($tempoMinutos);
            $preco = (float) $dados['preco'];

            // Criar agendamento
            $agendamento = Agendamento::create([
                'cliente_id'        => $dados['cliente_id'],
                'profissional_id'   => $dados['profissional_id'],
                'data_hora_inicio'  => $dataHoraInicio,
                'data_hora_fim'     => $dataHoraFim,
                'status'            => 'confirmado',
                'valor_total'       => $preco,
            ]);

            // Snapshot do serviço
            ItemAgendamento::create([
                'agendamento_id'        => $agendamento->id,
                'servico_id'            => $dados['servico_id'],
                'nome_servico'          => $dados['nome_servico'],
                'preco_praticado'       => $preco,
                'tempo_execucao_minutos' => $tempoMinutos,
            ]);

            // Histórico: pendente → confirmado
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
     * Cancela um agendamento do usuário.
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
                'status'            => 'cancelado_cliente',
                'cancelado_por_id'  => $usuarioId,
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
}
