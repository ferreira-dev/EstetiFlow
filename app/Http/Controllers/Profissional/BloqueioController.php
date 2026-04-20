<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Models\BloqueioAgenda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BloqueioController extends Controller
{
    /**
     * Lista os bloqueios de agenda do profissional logado.
     */
    public function index(): Response
    {
        $profissional = Auth::user()->profissional;

        $bloqueios = BloqueioAgenda::where('profissional_id', $profissional->id)
            ->orderBy('data_inicio', 'desc')
            ->get();

        return Inertia::render('Profissional/Bloqueios', [
            'bloqueios' => $bloqueios,
        ]);
    }

    /**
     * Cria um novo bloqueio de agenda.
     */
    public function store(Request $request): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $validated = $request->validate([
            'data_inicio' => ['required', 'date'],
            'data_fim'    => ['required', 'date', 'after_or_equal:data_inicio'],
            'tipo'        => ['required', 'in:ferias,folga,almoco,outro'],
            'motivo'      => ['nullable', 'string', 'max:255'],
        ]);

        BloqueioAgenda::create([
            'profissional_id' => $profissional->id,
            'data_inicio'     => $validated['data_inicio'],
            'data_fim'        => $validated['data_fim'],
            'tipo'            => $validated['tipo'],
            'motivo'          => $validated['motivo'] ?? null,
        ]);

        return redirect()->route('profissional.bloqueios')
            ->with('success', 'Bloqueio criado com sucesso!');
    }

    /**
     * Remove um bloqueio (somente se pertencer ao profissional logado).
     */
    public function destroy(int $id): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $bloqueio = BloqueioAgenda::where('id', $id)
            ->where('profissional_id', $profissional->id)
            ->firstOrFail();

        $bloqueio->delete();

        return redirect()->route('profissional.bloqueios')
            ->with('success', 'Bloqueio removido com sucesso!');
    }
}
