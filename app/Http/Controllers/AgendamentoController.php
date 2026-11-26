<?php

namespace App\Http\Controllers;

use App\Http\Requests\CriarAgendamentoRequest;
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

    public function store(CriarAgendamentoRequest $request): RedirectResponse
    {
        try {
            $this->service->criar(array_merge($request->validated(), [
                'cliente_id'      => $request->user()->id,
                'alterado_por_id' => $request->user()->id,
            ]));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['data_hora_inicio' => $e->getMessage()]);
        }

        return redirect()->route('agendamentos')->with('success', 'Reserva criada com sucesso!');
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        $this->service->cancelar($id, $request->user()->id);

        return back()->with('success', 'Reserva cancelada com sucesso!');
    }
}
