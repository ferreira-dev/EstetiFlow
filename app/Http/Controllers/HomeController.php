<?php

namespace App\Http\Controllers;

use App\Services\EstabelecimentoService;
use App\Services\AgendamentoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private EstabelecimentoService $estabelecimentoService,
        private AgendamentoService $agendamentoService,
    ) {}

    public function index(Request $request): Response
    {
        $estabelecimentos = $this->estabelecimentoService->destaques(6);

        $agendamentos = $request->user()
            ? $this->agendamentoService->recentesConfirmados($request->user()->id, 3)
            : collect();

        return Inertia::render('Home', [
            'estabelecimentos' => $estabelecimentos,
            'agendamentos'     => $agendamentos,
        ]);
    }
}
