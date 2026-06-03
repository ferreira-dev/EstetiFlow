<?php

namespace App\Http\Controllers;

use App\Models\Estabelecimento;
use App\Services\EstabelecimentoService;
use Illuminate\Http\RedirectResponse;
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
        $q = $request->string('q')->toString();
        $categoria = $request->string('categoria')->toString();

        $estabelecimentos = $this->service->listar($q, $categoria);

        return Inertia::render('Estabelecimentos', [
            'estabelecimentos' => $estabelecimentos,
            'q' => $q,
            'categoria' => $categoria,
        ]);
    }

    public function show(int $id): RedirectResponse
    {
        $estabelecimento = $this->service->buscarComServicos($id);

        return redirect($estabelecimento->url_publica, 301);
    }

    public function showPorUrl(string $urlPersonalizada): Response
    {
        $estabelecimento = $this->service->buscarComServicosPorUrl($urlPersonalizada);

        return $this->renderDetalhe($estabelecimento);
    }

    private function renderDetalhe(Estabelecimento $estabelecimento): Response
    {
        // Agrega horários de todos os profissionais (união por dia da semana)
        $horariosFuncionamento = collect($estabelecimento->profissionais)
            ->flatMap(fn ($p) => $p->horarios_funcionamento ?? collect())
            ->groupBy(fn ($h) => $h->dia_semana)
            ->map(fn ($grupo, $dia) => [
                'dia_semana' => (int) $dia,
                'hora_inicio' => $grupo->map(fn ($h) => $h->hora_inicio)->min(),
                'hora_fim' => $grupo->map(fn ($h) => $h->hora_fim)->max(),
            ])
            ->values();

        return Inertia::render('EstabelecimentoDetail', [
            'estabelecimento' => $estabelecimento,
            'horariosFuncionamento' => $horariosFuncionamento,
        ]);
    }
}
