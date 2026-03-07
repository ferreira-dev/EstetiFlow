<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'profissional_id',
        'data_hora_inicio',
        'data_hora_fim',
        'status',
        'valor_total',
        'cancelado_por_id',
        'motivo_cancelamento',
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim'    => 'datetime',
        'valor_total'      => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class);
    }

    public function canceladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelado_por_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemAgendamento::class);
    }

    public function historico(): HasMany
    {
        return $this->hasMany(HistoricoAgendamento::class);
    }

    public function avaliacao()
    {
        return $this->hasOne(Avaliacao::class);
    }
}
