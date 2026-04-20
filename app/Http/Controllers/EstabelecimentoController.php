<?php

namespace App\Http\Controllers;

use App\Services\EstabelecimentoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EstabelecimentoController extends Controller
{
    public function __construct(
        private EstabelecimentoService $service,
    ) {}

    public function index(Request $request): Response
    {
        $q         = $request->string('q')->toString();
        $categoria = $request->string('categoria')->toString();

        $estabelecimentos = $this->service->listar($q, $categoria);

        return Inertia::render('Estabelecimentos', [
            'estabelecimentos' => $estabelecimentos,
            'q'                => $q,
            'categoria'        => $categoria,
        ]);
    }

    public function show(int $id): Response
    {
        $estabelecimento = $this->service->buscarComServicos($id);

        // Agrega horários de todos os profissionais (união por dia da semana)
        $horariosFuncionamento = collect($estabelecimento->profissionais)
            ->flatMap(fn($p) => $p->horarios_funcionamento ?? collect())
            ->groupBy(fn($h) => $h->dia_semana)
            ->map(fn($grupo, $dia) => [
                'dia_semana'  => (int) $dia,
                'hora_inicio' => $grupo->map(fn($h) => $h->hora_inicio)->min(),
                'hora_fim'    => $grupo->map(fn($h) => $h->hora_fim)->max(),
            ])
            ->values();

        return Inertia::render('EstabelecimentoDetail', [
            'estabelecimento'       => $estabelecimento,
            'horariosFuncionamento' => $horariosFuncionamento,
        ]);
    }
}
