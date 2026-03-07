<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicoProfissional extends Model
{
    protected $table = 'servicos_profissionais';

    protected $fillable = [
        'profissional_id',
        'servico_id',
        'preco',
        'tempo_execucao_minutos',
        'ativo',
    ];

    protected $casts = [
        'preco'                 => 'decimal:2',
        'tempo_execucao_minutos' => 'integer',
        'ativo'                 => 'boolean',
    ];

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
