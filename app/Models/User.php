<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'nome_completo',
        'email',
        'password',
        'tipo',
        'ativo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'ativo'             => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────

    public function perfil()
    {
        return $this->hasOne(Perfil::class, 'usuario_id');
    }

    public function profissional()
    {
        return $this->hasOne(Profissional::class, 'usuario_id');
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'cliente_id');
    }

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'usuario_id');
    }
}
