<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class CompletarCadastroController extends Controller
{
    public function create(string $token)
    {
        try {
            $userId = decrypt($token);
            $user = User::with('perfil')->findOrFail($userId);

            if ($user->password !== null) {
                return redirect()->route('login')->with('info', 'Seu cadastro já está completo. Faça login.');
            }

            return Inertia::render('CompletarCadastro', [
                'token' => $token,
                'nome' => $user->nome_completo,
                'telefone' => $user->perfil->telefone ?? null,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('registro')->with('error', 'Link inválido ou expirado.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'confirmed', Password::min(6)],
        ]);

        try {
            $userId = decrypt($request->token);
            $user = User::findOrFail($userId);

            if ($user->password !== null) {
                return redirect()->route('login')->with('info', 'Seu cadastro já está completo. Faça login.');
            }

            $user->update([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('home')->with('success', 'Cadastro completado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('registro')->with('error', 'Não foi possível completar o cadastro. Link inválido.');
        }
    }
}
