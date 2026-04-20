<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Services\AgendamentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AgendamentoProfissionalController extends Controller
{
    public function __construct(
        private AgendamentoService $service,
    ) {}

    /**
     * Lista os agendamentos do profissional logado com filtro opcional por status.
     */
    public function index(Request $request): Response
    {
        $profissional = Auth::user()->profissional;

        $status = $request->input('status', 'todos');

        $agendamentos = $this->service->listarDoProfissional($profissional->id, $status);

        return Inertia::render('Profissional/AgendamentosProfissional', [
            'agendamentos' => $agendamentos,
            'filtroAtual'  => $status,
        ]);
    }

    /**
     * Altera o status de um agendamento com validação de transição.
     */
    public function alterarStatus(Request $request, int $id): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $validated = $request->validate([
            'status' => ['required', 'in:confirmado,em_atendimento,concluido,cancelado_profissional,nao_compareceu'],
        ]);

        try {
            $this->service->alterarStatus(
                $id,
                $profissional->id,
                $validated['status'],
                Auth::id(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status do agendamento atualizado com sucesso!');
    }
}
