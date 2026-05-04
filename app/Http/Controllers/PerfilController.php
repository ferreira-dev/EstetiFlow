<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PerfilController extends Controller
{
    /**
     * Exibe o perfil do usuário autenticado.
     */
    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('perfil');

        return Inertia::render('Perfil', [
            'usuario' => [
                'id'           => $user->id,
                'nome_completo' => $user->nome_completo,
                'email'        => $user->email,
            ],
            'perfil' => $user->perfil ?? [],
        ]);
    }

    /**
     * Atualiza os dados pessoais e de endereço do usuário.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'nome_completo'   => ['required', 'string', 'max:150'],
            'telefone'        => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date'],
            'genero'          => ['nullable', 'in:masculino,feminino,outro,prefiro_nao_informar'],
            'cep'             => ['nullable', 'string', 'max:9'],
            'logradouro'      => ['nullable', 'string', 'max:200'],
            'numero'          => ['nullable', 'string', 'max:10'],
            'complemento'     => ['nullable', 'string', 'max:100'],
            'bairro'          => ['nullable', 'string', 'max:100'],
            'cidade'          => ['nullable', 'string', 'max:100'],
            'estado'          => ['nullable', 'string', 'size:2'],
        ]);

        // Atualiza nome no usuário
        $user->update(['nome_completo' => $request->nome_completo]);

        // Atualiza ou cria perfil
        $user->perfil()->updateOrCreate(
            ['usuario_id' => $user->id],
            $request->only([
                'telefone', 'data_nascimento', 'genero',
                'cep', 'logradouro', 'numero', 'complemento',
                'bairro', 'cidade', 'estado',
            ])
        );

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Altera a senha do usuário autenticado.
     */
    public function alterarSenha(Request $request): RedirectResponse
    {
        $request->validate([
            'senha_atual'       => ['required', 'string'],
            'nova_senha'        => ['required', 'confirmed', Password::min(8)],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->senha_atual, $user->password)) {
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }

        $user->update(['password' => $request->nova_senha]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }
}
