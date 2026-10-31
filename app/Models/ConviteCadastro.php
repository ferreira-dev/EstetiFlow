<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConviteCadastro extends Model
{
    protected $table = 'convites_cadastro';

    protected $fillable = [
        'usuario_id',
        'criado_por_id',
        'token_hash',
        'expira_em',
        'usado_em',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'expira_em' => 'datetime',
        'usado_em'  => 'datetime',
    ];

    /**
     * Convites ainda utilizáveis: não usados e dentro da validade.
     */
    public function scopeValidos(Builder $query): Builder
    {
        return $query->whereNull('usado_em')->where('expira_em', '>', now());
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }
}
