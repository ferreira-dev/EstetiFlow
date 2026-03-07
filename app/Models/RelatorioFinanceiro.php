<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatorioFinanceiro extends Model
{
    protected $table = 'relatorios_financeiros';

    protected $fillable = [
        'profissional_id',
        'data_referencia',
        'periodo_tipo',
        'total_agendamentos',
        'receita_total',
        'receita_confirmada',
    ];

    protected $casts = [
        'data_referencia'     => 'date',
        'total_agendamentos'  => 'integer',
        'receita_total'       => 'decimal:2',
        'receita_confirmada'  => 'decimal:2',
    ];

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class);
    }
}
