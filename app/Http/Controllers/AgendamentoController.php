<?php

namespace App\Http\Controllers;

use App\Services\AgendamentoService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AgendamentoController extends Controller
{
    public function __construct(
        private AgendamentoService $service,
    ) {}

    public function index(Request $request): Response
    {
        $agendamentos = $this->service->listarDoUsuario($request->user()->id);

        return Inertia::render('Agendamentos', [
            'agendamentos' => $agendamentos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profissional_id'        => ['required', 'exists:profissionais,id'],
            'servico_id'             => ['required', 'exists:servicos,id'],
            'data_hora_inicio'       => ['required', 'date', 'after:now'],
            'preco'                  => ['required', 'numeric', 'min:0'],
            'tempo_execucao_minutos' => ['required', 'integer', 'min:1'],
            'nome_servico'           => ['required', 'string', 'max:150'],
        ]);

        $this->service->criar(array_merge($validated, [
            'cliente_id' => $request->user()->id,
        ]));

        return redirect()->route('agendamentos')->with('success', 'Reserva criada com sucesso!');
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        $this->service->cancelar($id, $request->user()->id);

        return back()->with('success', 'Reserva cancelada com sucesso!');
    }
}
