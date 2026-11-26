<?php

namespace App\Models;

use App\Support\Telefone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perfil extends Model
{
    protected $table = 'perfis';

    protected $fillable = [
        'usuario_id',
        'foto_url',
        'data_nascimento',
        'telefone',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
    ];

    /**
     * Telefone sempre gravado apenas com dígitos (permite busca por match exato).
     */
    protected function telefone(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => Telefone::normalizar($value),
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
