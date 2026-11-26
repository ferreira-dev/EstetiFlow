<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class EsqueciSenhaController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('EsqueciSenha');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Usuários inativos não recebem link
        Password::sendResetLink([
            'email' => $validated['email'],
            'ativo' => true,
        ]);

        // Resposta sempre neutra: não revela se o e-mail está cadastrado
        return back()->with(
            'success',
            'Se o e-mail estiver cadastrado, você receberá um link para redefinir sua senha em instantes.'
        );
    }
}
