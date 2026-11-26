<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\RetornoReserva;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    private const MAX_TENTATIVAS = 5;

    public function create(Request $request): Response
    {
        // Login iniciado a partir de uma reserva: volta para a página do estabelecimento
        $retorno = RetornoReserva::validar($request->query('redirect'));
        if ($retorno) {
            $request->session()->put('url.intended', url($retorno));
        }

        return Inertia::render('Login', [
            'retornoReserva' => $retorno,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Throttle por e-mail + IP (força bruta)
        $chaveThrottle = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($chaveThrottle, self::MAX_TENTATIVAS)) {
            $segundos = RateLimiter::availableIn($chaveThrottle);

            return back()->withErrors([
                'email' => "Muitas tentativas de login. Tente novamente em {$segundos} segundos.",
            ])->onlyInput('email');
        }

        // Usuário inativo não autentica (mesma mensagem neutra de credenciais inválidas)
        if (Auth::attempt([...$credentials, 'ativo' => true], $request->boolean('remember'))) {
            RateLimiter::clear($chaveThrottle);
            $request->session()->regenerate();

            $destino = Auth::user()->hasRole('profissional')
                ? route('profissional.dashboard')
                : route('home');

            return redirect()->intended($destino);
        }

        RateLimiter::hit($chaveThrottle);

        return back()->withErrors([
            'email' => 'As credenciais fornecidas estão incorretas.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
