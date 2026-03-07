<?php

namespace App\Services;

use App\Models\Estabelecimento;
use Illuminate\Database\Eloquent\Collection;

class EstabelecimentoService
{
    /**
     * Lista todos os estabelecimentos com filtros opcionais.
     */
    public function listar(string $q = '', string $categoria = ''): Collection
    {
        return Estabelecimento::query()
            ->where('ativo', true)
            ->when($q, fn($query) => $query->where('nome_fantasia', 'like', "%{$q}%"))
            ->when($categoria, fn($query) => $query->whereHas(
                'profissionais.servicosProfissionais.servico',
                fn($q2) => $q2->where('categoria', $categoria)->where('ativo', true)
            ))
            ->with([
                'profissionais' => fn($q) => $q->where('aceita_agendamentos', true)
                    ->with([
                        'servicosProfissionais' => fn($q2) => $q2->where('ativo', true)
                            ->with('servico'),
                    ]),
            ])
            ->get();
    }

    /**
     * Busca um estabelecimento com seus profissionais e serviços ativos.
     */
    public function buscarComServicos(int $id): ?Estabelecimento
    {
        return Estabelecimento::where('ativo', true)
            ->with([
                'profissionais' => fn($q) => $q->where('aceita_agendamentos', true)
                    ->with([
                        'servicosProfissionais' => fn($q2) => $q2->where('ativo', true)
                            ->with('servico'),
                    ]),
            ])
            ->findOrFail($id);
    }

    /**
     * Retorna os estabelecimentos mais recentes como destaques do MVP.
     * (sem colunas recomendado/popular/avaliacao — módulo de engajamento é pós-MVP)
     */
    public function destaques(int $limite = 6): Collection
    {
        return Estabelecimento::where('ativo', true)
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get();
    }
}
