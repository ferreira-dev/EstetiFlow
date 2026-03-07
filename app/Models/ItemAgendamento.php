<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemAgendamento extends Model
{
    protected $table = 'itens_agendamentos';

    public $timestamps = false;

    protected $fillable = [
        'agendamento_id',
        'servico_id',
        'nome_servico',
        'preco_praticado',
        'tempo_execucao_minutos',
    ];

    protected $casts = [
        'preco_praticado'        => 'decimal:2',
        'tempo_execucao_minutos' => 'integer',
        'created_at'             => 'datetime',
    ];

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
