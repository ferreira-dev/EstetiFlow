<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'isFirstTime' => $estabelecimento === null,
        ]);
    }

    /**
     * Cria ou atualiza o estabelecimento do profissional logado.
     */
    public function salvarEstabelecimento(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $profissional = $user->profissional;
        $estabelecimentoAtual = $profissional?->estabelecimento;
        $urlInformada = trim((string) $request->input('url_personalizada', ''));
        $urlPersonalizada = Str::slug($urlInformada ?: (string) $request->input('nome_fantasia'));

        if ($urlInformada === '') {
            $urlPersonalizada = $this->gerarUrlPersonalizadaUnica(
                $urlPersonalizada,
                $estabelecimentoAtual?->id
            );
        }

        $request->merge(['url_personalizada' => $urlPersonalizada]);

        $validated = $request->validate([
            'nome_fantasia' => ['required', 'string', 'max:150'],
            'url_personalizada' => [
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('estabelecimentos', 'url_personalizada')->ignore($estabelecimentoAtual?->id),
            ],
            'agenda_online'      => ['required', 'boolean'],
            'cnpj' => ['nullable', 'string', 'max:14'],
            'descricao' => ['nullable', 'string'],
            'telefone_principal' => ['required', 'string', 'max:20'],
            'telefone_secundario' => ['nullable', 'string', 'max:20'],
            'cep' => ['required', 'string', 'max:8'],
            'logradouro' => ['required', 'string', 'max:200'],
            'numero' => ['required', 'string', 'max:10'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['required', 'string', 'max:100'],
            'cidade' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:2'],
        ]);

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

    private function gerarUrlPersonalizadaUnica(string $baseSlug, ?int $ignorarId = null): string
    {
        $baseSlug = $baseSlug ?: 'estabelecimento';
        $slug = $baseSlug;
        $sufixo = 2;

        while ($this->urlPersonalizadaExiste($slug, $ignorarId)) {
            $slug = "{$baseSlug}-{$sufixo}";
            $sufixo++;
        }

        return $slug;
    }

    private function urlPersonalizadaExiste(string $slug, ?int $ignorarId = null): bool
    {
        return Estabelecimento::withTrashed()
            ->where('url_personalizada', $slug)
            ->when($ignorarId, fn ($query) => $query->where('id', '!=', $ignorarId))
            ->exists();
    }
}
