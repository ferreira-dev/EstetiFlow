<?php

namespace App\Services;

use App\Models\ConviteCadastro;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConviteCadastroService
{
    public const VALIDADE_HORAS = 48;

    /**
     * Gera um novo link de "completar cadastro" para o cliente pré-cadastrado.
     * Convites anteriores ainda abertos são revogados (só o último link funciona).
     */
    public function gerar(User $cliente, User $criadoPor): string
    {
        $token = Str::random(64);

        DB::transaction(function () use ($cliente, $criadoPor, $token) {
            ConviteCadastro::where('usuario_id', $cliente->id)
                ->validos()
                ->update(['expira_em' => now()]);

            ConviteCadastro::create([
                'usuario_id'    => $cliente->id,
                'criado_por_id' => $criadoPor->id,
                'token_hash'    => $this->hash($token),
                'expira_em'     => now()->addHours(self::VALIDADE_HORAS),
            ]);
        });

        return route('completar-cadastro', ['token' => $token]);
    }

    /**
     * Convite válido (não usado, não expirado) de um usuário ainda sem senha.
     */
    public function buscarValido(string $token, bool $travar = false): ?ConviteCadastro
    {
        $convite = ConviteCadastro::validos()
            ->where('token_hash', $this->hash($token))
            ->when($travar, fn ($q) => $q->lockForUpdate())
            ->with('usuario.perfil')
            ->first();

        if (!$convite || !$convite->usuario || $convite->usuario->password !== null) {
            return null;
        }

        return $convite;
    }

    /**
     * Completa o cadastro (e-mail + senha) consumindo o convite. Uso único.
     */
    public function completar(string $token, string $email, string $senha): ?User
    {
        return DB::transaction(function () use ($token, $email, $senha) {
            $convite = $this->buscarValido($token, travar: true);

            if (!$convite) {
                return null;
            }

            $usuario = $convite->usuario;
            $usuario->update([
                'email'    => $email,
                'password' => $senha,
            ]);

            $convite->update(['usado_em' => now()]);

            return $usuario;
        });
    }

    private function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
