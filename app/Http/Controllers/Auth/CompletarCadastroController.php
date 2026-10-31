<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ConviteCadastroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class CompletarCadastroController extends Controller
{
    public function __construct(
        private ConviteCadastroService $convites,
    ) {}

    public function create(string $token)
    {
        $convite = $this->convites->buscarValido($token);

        if (!$convite) {
            return redirect()->route('login')
                ->with('error', 'Link inválido ou expirado. Peça um novo link ao seu profissional.');
        }

        return Inertia::render('CompletarCadastro', [
            'token'    => $token,
            'nome'     => $convite->usuario->nome_completo,
            'telefone' => $convite->usuario->perfil->telefone ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = $this->convites->completar($validated['token'], $validated['email'], $validated['password']);

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Link inválido ou expirado. Peça um novo link ao seu profissional.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Cadastro completado com sucesso!');
    }
}
