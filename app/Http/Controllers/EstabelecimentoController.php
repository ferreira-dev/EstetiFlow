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

        return Inertia::render('EstabelecimentoDetail', [
            'estabelecimento' => $estabelecimento,
        ]);
    }
}
