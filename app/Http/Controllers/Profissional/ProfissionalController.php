<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfissionalController extends Controller
{
    /**
     * Exibe o formulário de gestão do estabelecimento do profissional logado.
     */
    public function estabelecimento(): Response
    {
        $user = Auth::user();
        $profissional = $user->profissional;
        $estabelecimento = $profissional?->estabelecimento;

        return Inertia::render('Profissional/Estabelecimento', [
            'estabelecimento' => $estabelecimento,
            'isFirstTime'     => $estabelecimento === null,
        ]);
    }

    /**
     * Cria ou atualiza o estabelecimento do profissional logado.
     */
    public function salvarEstabelecimento(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nome_fantasia'       => ['required', 'string', 'max:150'],
            'cnpj'                => ['nullable', 'string', 'max:14'],
            'descricao'           => ['nullable', 'string'],
            'telefone_principal'  => ['required', 'string', 'max:20'],
            'telefone_secundario' => ['nullable', 'string', 'max:20'],
            'cep'                 => ['required', 'string', 'max:8'],
            'logradouro'          => ['required', 'string', 'max:200'],
            'numero'              => ['required', 'string', 'max:10'],
            'complemento'         => ['nullable', 'string', 'max:100'],
            'bairro'              => ['required', 'string', 'max:100'],
            'cidade'              => ['required', 'string', 'max:100'],
            'estado'              => ['required', 'string', 'max:2'],
        ]);

        $user = Auth::user();
        $profissional = $user->profissional;

        // Upsert do estabelecimento
        if ($profissional->estabelecimento_id) {
            // Atualiza
            $profissional->estabelecimento->update($validated);
        } else {
            // Cria novo e vincula ao profissional
            $estabelecimento = Estabelecimento::create($validated);
            $profissional->update(['estabelecimento_id' => $estabelecimento->id]);
        }

        return redirect()->route('profissional.estabelecimento')
            ->with('success', 'Dados do estabelecimento salvos com sucesso!');
    }
}
