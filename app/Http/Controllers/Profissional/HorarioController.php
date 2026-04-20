<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Models\HorarioFuncionamento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HorarioController extends Controller
{
    /**
     * Exibe a grade de horários do profissional logado.
     */
    public function index(): Response
    {
        $profissional = Auth::user()->profissional;

        $horarios = HorarioFuncionamento::where('profissional_id', $profissional->id)
            ->get()
            ->keyBy('dia_semana')
            ->toArray();

        return Inertia::render('Profissional/Horarios', [
            'horarios' => $horarios,
        ]);
    }

    /**
     * Salva (substitui) a grade completa de horários do profissional.
     *
     * Recebe array de itens: [{ dia_semana, hora_inicio, hora_fim, ativo }]
     */
    public function salvar(Request $request): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $validated = $request->validate([
            'horarios'                     => ['required', 'array'],
            'horarios.*.dia_semana'        => ['required', 'integer', 'min:0', 'max:6'],
            'horarios.*.hora_inicio'       => ['required_if:horarios.*.ativo,true', 'nullable', 'date_format:H:i'],
            'horarios.*.hora_fim'          => ['required_if:horarios.*.ativo,true', 'nullable', 'date_format:H:i', 'after:horarios.*.hora_inicio'],
            'horarios.*.ativo'             => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($profissional, $validated) {
            // Remove todos os horários existentes e recria apenas os ativos
            HorarioFuncionamento::where('profissional_id', $profissional->id)->delete();

            $ativos = array_filter($validated['horarios'], fn($h) => $h['ativo']);

            foreach ($ativos as $horario) {
                HorarioFuncionamento::create([
                    'profissional_id' => $profissional->id,
                    'dia_semana'      => $horario['dia_semana'],
                    'hora_inicio'     => $horario['hora_inicio'],
                    'hora_fim'        => $horario['hora_fim'],
                ]);
            }
        });

        return redirect()->route('profissional.horarios')
            ->with('success', 'Horários de funcionamento salvos com sucesso!');
    }
}
