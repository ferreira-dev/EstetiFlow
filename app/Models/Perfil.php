<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perfil extends Model
{
    protected $table = 'perfis';

    protected $fillable = [
        'usuario_id',
        'foto_url',
        'data_nascimento',
        'genero',
        'telefone',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
