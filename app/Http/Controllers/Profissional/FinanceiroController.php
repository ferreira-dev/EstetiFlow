<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FinanceiroController extends Controller
{
    public function index(Request $request): Response
    {
        $profissional = Auth::user()->profissional;

        // Período selecionado (padrão: mês atual)
        $periodo  = $request->input('periodo', 'mes');
        [$inicio, $fim] = $this->calcularPeriodo($periodo, $request);

        $agendamentos = [];
        $stats        = $this->statsVazias();

        if ($profissional) {
            $agendamentos = Agendamento::where('profissional_id', $profissional->id)
                ->whereIn('status', ['confirmado', 'concluido', 'em_atendimento'])
                ->whereBetween('data_hora_inicio', [$inicio->startOfDay(), $fim->endOfDay()])
                ->with(['cliente', 'itens'])
                ->orderBy('data_hora_inicio')
                ->get();

            $stats = [
                'receita_total'      => (float) $agendamentos->where('status', 'concluido')->sum('valor_total'),
                'receita_prevista'   => (float) $agendamentos->whereIn('status', ['confirmado', 'em_atendimento'])->sum('valor_total'),
                'total_atendimentos' => $agendamentos->count(),
                'ticket_medio'       => $agendamentos->count() > 0
                    ? (float) ($agendamentos->sum('valor_total') / $agendamentos->count())
                    : 0,
            ];
        }

        return Inertia::render('Profissional/Financeiro', [
            'agendamentos'    => $agendamentos,
            'stats'           => $stats,
            'periodoAtual'    => $periodo,
            'dataInicio'      => $inicio->toDateString(),
            'dataFim'         => $fim->toDateString(),
        ]);
    }

    private function calcularPeriodo(string $periodo, Request $request): array
    {
        return match ($periodo) {
            'hoje'    => [Carbon::today(), Carbon::today()],
            'semana'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'mes'     => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'custom'  => [
                Carbon::parse($request->input('data_inicio', Carbon::now()->startOfMonth())),
                Carbon::parse($request->input('data_fim', Carbon::now()->endOfMonth())),
            ],
            default   => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    private function statsVazias(): array
    {
        return [
            'receita_total'      => 0.0,
            'receita_prevista'   => 0.0,
            'total_atendimentos' => 0,
            'ticket_medio'       => 0.0,
        ];
    }
}
