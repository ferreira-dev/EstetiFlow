<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estabelecimento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome_fantasia',
        'url_personalizada',
        'agenda_online',
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
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
    ];

    public function getUrlPublicaAttribute(): string
    {
        return "/agendar/{$this->url_personalizada}";
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
