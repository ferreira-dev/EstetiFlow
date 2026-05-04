<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $profissional = Auth::user()->profissional;

        if (!$profissional) {
            return Inertia::render('Profissional/Dashboard', [
                'agendamentosHoje'   => [],
                'proximosAtendimentos' => [],
                'stats'              => $this->statsVazias(),
            ]);
        }

        $hoje = Carbon::today();

        // Agendamentos de hoje (todos os status exceto cancelados)
        $agendamentosHoje = Agendamento::where('profissional_id', $profissional->id)
            ->whereDate('data_hora_inicio', $hoje)
            ->whereNotIn('status', ['cancelado_cliente', 'cancelado_profissional'])
            ->with(['cliente', 'itens'])
            ->orderBy('data_hora_inicio')
            ->get();

        // Próximos atendimentos (a partir de amanhã, pendentes ou confirmados)
        $proximosAtendimentos = Agendamento::where('profissional_id', $profissional->id)
            ->whereDate('data_hora_inicio', '>', $hoje)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->with(['cliente', 'itens'])
            ->orderBy('data_hora_inicio')
            ->limit(5)
            ->get();

        // Stats do dia
        $receitaRealizadaHoje = Agendamento::where('profissional_id', $profissional->id)
            ->whereDate('data_hora_inicio', $hoje)
            ->where('status', 'concluido')
            ->sum('valor_total');

        $receitaPrevistaHoje = Agendamento::where('profissional_id', $profissional->id)
            ->whereDate('data_hora_inicio', $hoje)
            ->whereIn('status', ['confirmado', 'em_atendimento'])
            ->sum('valor_total');

        $totalHoje = $agendamentosHoje->count();
        $pendentesHoje = $agendamentosHoje->where('status', 'pendente')->count();

        // Total de clientes únicos atendidos (agendamentos concluídos)
        $totalClientes = Agendamento::where('profissional_id', $profissional->id)
            ->where('status', 'concluido')
            ->distinct('cliente_id')
            ->count('cliente_id');

        // Total de agendamentos desta semana
        $totalSemana = Agendamento::where('profissional_id', $profissional->id)
            ->whereBetween('data_hora_inicio', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->whereNotIn('status', ['cancelado_cliente', 'cancelado_profissional'])
            ->count();

        return Inertia::render('Profissional/Dashboard', [
            'agendamentosHoje'     => $agendamentosHoje,
            'proximosAtendimentos' => $proximosAtendimentos,
            'stats'                => [
                'totalHoje'           => $totalHoje,
                'pendentesHoje'       => $pendentesHoje,
                'receitaRealizadaHoje'=> (float) $receitaRealizadaHoje,
                'receitaPrevistaHoje' => (float) $receitaPrevistaHoje,
                'totalSemana'         => $totalSemana,
                'totalClientes'       => $totalClientes,
            ],
        ]);
    }

    private function statsVazias(): array
    {
        return [
            'totalHoje'           => 0,
            'pendentesHoje'       => 0,
            'receitaRealizadaHoje'=> 0.0,
            'receitaPrevistaHoje' => 0.0,
            'totalSemana'         => 0,
            'totalClientes'       => 0,
        ];
    }
}
