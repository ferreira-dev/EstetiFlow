<?php

namespace App\Http\Controllers\Profissional;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use App\Models\ServicoProfissional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServicoController extends Controller
{
    /**
     * Lista os serviços vinculados ao profissional logado
     * e o catálogo global para o select de adicionar.
     */
    public function index(): Response
    {
        $profissional = Auth::user()->profissional;

        $servicos = ServicoProfissional::where('profissional_id', $profissional->id)
            ->with('servico')
            ->orderBy('created_at', 'desc')
            ->get();

        // Catálogo global — exclui os que o profissional já possui
        $idsVinculados = $servicos->pluck('servico_id')->toArray();
        $catalogoServicos = Servico::where('ativo', true)
            ->whereNotIn('id', $idsVinculados)
            ->orderBy('categoria')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Profissional/Servicos', [
            'servicos'         => $servicos,
            'catalogoServicos' => $catalogoServicos,
        ]);
    }

    /**
     * Vincula um serviço existente ou cria um novo + vincula ao profissional.
     */
    public function store(Request $request): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $validated = $request->validate([
            // Serviço existente OU novo
            'servico_id'             => ['nullable', 'exists:servicos,id'],
            'novo_nome'              => ['required_without:servico_id', 'nullable', 'string', 'max:150'],
            'novo_descricao'         => ['nullable', 'string'],
            'novo_categoria'         => ['required_without:servico_id', 'nullable', 'string', Rule::in([
                'cabelo', 'barba', 'manicure', 'pedicure', 'sobrancelha',
                'cilio', 'estetica', 'massagem', 'maquiagem', 'depilacao',
                'hidratacao', 'outro',
            ])],
            // Dados do profissional para o pivot
            'preco'                  => ['required', 'numeric', 'min:0.01'],
            'tempo_execucao_minutos' => ['required', 'integer', 'min:5', 'max:480'],
        ]);

        // Determinar servico_id (existente ou novo)
        if (!empty($validated['servico_id'])) {
            $servicoId = $validated['servico_id'];
        } else {
            $servico = Servico::create([
                'nome'      => $validated['novo_nome'],
                'descricao' => $validated['novo_descricao'] ?? null,
                'categoria' => $validated['novo_categoria'],
            ]);
            $servicoId = $servico->id;
        }

        // Verificar duplicata
        $jaExiste = ServicoProfissional::where('profissional_id', $profissional->id)
            ->where('servico_id', $servicoId)
            ->exists();

        if ($jaExiste) {
            return back()->withErrors([
                'servico_id' => 'Você já possui esse serviço vinculado.',
            ]);
        }

        ServicoProfissional::create([
            'profissional_id'        => $profissional->id,
            'servico_id'             => $servicoId,
            'preco'                  => $validated['preco'],
            'tempo_execucao_minutos' => $validated['tempo_execucao_minutos'],
        ]);

        return redirect()->route('profissional.servicos')
            ->with('success', 'Serviço adicionado com sucesso!');
    }

    /**
     * Atualiza preço e tempo de execução de um vínculo existente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $sp = ServicoProfissional::where('id', $id)
            ->where('profissional_id', $profissional->id)
            ->firstOrFail();

        $validated = $request->validate([
            'preco'                  => ['required', 'numeric', 'min:0.01'],
            'tempo_execucao_minutos' => ['required', 'integer', 'min:5', 'max:480'],
        ]);

        $sp->update($validated);

        return redirect()->route('profissional.servicos')
            ->with('success', 'Serviço atualizado com sucesso!');
    }

    /**
     * Remove o vínculo do serviço com o profissional.
     */
    public function destroy(int $id): RedirectResponse
    {
        $profissional = Auth::user()->profissional;

        $sp = ServicoProfissional::where('id', $id)
            ->where('profissional_id', $profissional->id)
            ->firstOrFail();

        $sp->delete();

        return redirect()->route('profissional.servicos')
            ->with('success', 'Serviço removido com sucesso!');
    }
}
