<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profissional extends Model
{
    use SoftDeletes;

    protected $table = 'profissionais';

    protected $fillable = [
        'usuario_id',
        'estabelecimento_id',
        'nome_profissional',
        'especialidade',
        'foto_url',
        'aceita_agendamentos',
    ];

    protected $casts = [
        'aceita_agendamentos' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function estabelecimento(): BelongsTo
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    public function servicosProfissionais(): HasMany
    {
        return $this->hasMany(ServicoProfissional::class);
    }

    public function horariosFuncionamento(): HasMany
    {
        return $this->hasMany(HorarioFuncionamento::class);
    }

    public function bloqueiosAgenda(): HasMany
    {
        return $this->hasMany(BloqueioAgenda::class);
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }
}
