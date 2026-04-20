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
     * Lista os bloqueios do profissional logado (recorrentes + futuros/ativos).
     */
    public function index(): Response
    {
        $profissional = Auth::user()->profissional;

        $bloqueios = BloqueioAgenda::where('profissional_id', $profissional->id)
            ->where(fn($q) =>
                $q->where('recorrente', true)           // Recorrentes: sempre ativos
                  ->orWhere('data_fim', '>=', now())    // Ou não-recorrentes ainda válidos
            )
            ->orderByRaw('recorrente DESC, data_inicio ASC')
            ->get();

        return Inertia::render('Profissional/Bloqueios', [
            'bloqueios' => $bloqueios,
        ]);
    }

    /**
     * Cria um novo bloqueio de agenda (pontual com hora ou recorrente).
     */
    public function store(Request $request): RedirectResponse
    {
        $profissional = Auth::user()->profissional;
        $recorrente   = $request->boolean('recorrente');

        // Regras condicionais por tipo de bloqueio
        $rules = [
            'recorrente'    => ['required', 'boolean'],
            'tipo'          => ['required', 'in:ferias,folga,almoco,outro'],
            'motivo'        => ['nullable', 'string', 'max:255'],
            'dias_semana'   => ['nullable', 'array'],
            'dias_semana.*' => ['integer', 'min:0', 'max:6'],
        ];

        if (!$recorrente) {
            $rules['data_inicio'] = ['required', 'date'];
            $rules['data_fim']    = ['required', 'date', 'after_or_equal:data_inicio'];
        } else {
            $rules['hora_inicio'] = ['required', 'date_format:H:i'];
            $rules['hora_fim']    = ['required', 'date_format:H:i'];
        }

        $validated = $request->validate($rules);

        BloqueioAgenda::create([
            'profissional_id' => $profissional->id,
            'recorrente'      => $recorrente,
            'tipo'            => $validated['tipo'],
            'motivo'          => $validated['motivo'] ?? null,
            'data_inicio'     => !$recorrente ? $validated['data_inicio'] : null,
            'data_fim'        => !$recorrente ? $validated['data_fim']    : null,
            'hora_inicio'     => $recorrente  ? $validated['hora_inicio'] : null,
            'hora_fim'        => $recorrente  ? $validated['hora_fim']    : null,
            'dias_semana'     => !empty($validated['dias_semana']) ? $validated['dias_semana'] : null,
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
