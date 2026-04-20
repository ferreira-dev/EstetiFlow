<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueioAgenda extends Model
{
    protected $table = 'bloqueios_agenda';

    protected $fillable = [
        'profissional_id',
        'data_inicio',
        'data_fim',
        'motivo',
        'tipo',
        'recorrente',
        'hora_inicio',
        'hora_fim',
        'dias_semana',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim'    => 'datetime',
        'recorrente'  => 'boolean',
        'dias_semana' => 'array',   // JSON → PHP array automaticamente
    ];

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class);
    }
}
