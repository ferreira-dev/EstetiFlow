<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoAgendamento extends Model
{
    protected $table = 'historico_agendamentos';

    public $timestamps = false;

    protected $fillable = [
        'agendamento_id',
        'status_anterior',
        'status_novo',
        'alterado_por_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class);
    }

    public function alteradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alterado_por_id');
    }
}
