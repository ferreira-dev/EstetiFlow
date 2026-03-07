<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servico extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function profissionaisPivot(): HasMany
    {
        return $this->hasMany(ServicoProfissional::class);
    }
}
