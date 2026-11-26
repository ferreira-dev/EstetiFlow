<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estabelecimento extends Model
{
    use SoftDeletes;

    /**
     * Paletas do tema Aura disponíveis como cor primária do estabelecimento.
     * Manter em sincronia com resources/js/Constants/coresTema.js.
     */
    public const CORES_PRIMARIAS = [
        'emerald', 'green', 'teal', 'cyan', 'sky', 'blue', 'indigo',
        'violet', 'purple', 'fuchsia', 'pink', 'rose', 'orange', 'amber',
    ];

    /**
     * Comodidades que o dono pode marcar no painel (exibidas na página pública).
     * Manter em sincronia com resources/js/Constants/caracteristicas.js.
     */
    public const CARACTERISTICAS = [
        'wifi', 'estacionamento', 'cartao', 'pix', 'climatizado',
        'acessivel', 'domicilio', 'kids', 'cortesia',
    ];

    /** Pasta das fotos de capa no disk `public`. */
    public const PASTA_CAPAS = 'capas';

    protected $fillable = [
        'nome_fantasia',
        'caracteristicas',
        'url_personalizada',
        'agenda_online',
        'cor_primaria',
        'cnpj',
        'descricao',
        'foto_capa_url',
        'telefone_principal',
        'telefone_secundario',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'latitude',
        'longitude',
        'ativo',
    ];

    protected $casts = [
        'ativo'        => 'boolean',
        'agenda_online' => 'boolean',
        'caracteristicas' => 'array',
        'latitude'    => 'decimal:7',
        'longitude'    => 'decimal:7',
    ];

    public function getUrlPublicaAttribute(): string
    {
        return "/agendar/{$this->url_personalizada}";
    }

    /**
     * Estabelecimento pronto para receber clientes: ativo e com ao menos um profissional
     * que aceita agendamentos, tem ≥ 1 serviço ativo e ≥ 1 dia de horário configurado.
     */
    public function scopePublicavel(Builder $query): Builder
    {
        return $query->where('ativo', true)
            ->whereHas('profissionais', fn (Builder $p) => $p
                ->where('aceita_agendamentos', true)
                ->whereHas('servicosProfissionais', fn (Builder $sp) => $sp
                    ->where('ativo', true)
                    ->whereHas('servico', fn (Builder $s) => $s->where('ativo', true)))
                ->whereHas('horariosFuncionamento'));
    }

    public function isPublicavel(): bool
    {
        return static::publicavel()->whereKey($this->getKey())->exists();
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function profissionais(): HasMany
    {
        return $this->hasMany(Profissional::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }
}
