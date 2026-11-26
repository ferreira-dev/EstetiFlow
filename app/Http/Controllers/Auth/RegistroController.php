<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\Profissional;
use App\Models\User;
use App\Support\RetornoReserva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegistroController extends Controller
{
    public function create(Request $request): Response
    {
        // Registro iniciado a partir de uma reserva: formulário só de cliente + retorno à página
        $retorno = RetornoReserva::validar($request->query('redirect'));
        if ($retorno) {
            $request->session()->put('url.intended', url($retorno));
        }

        return Inertia::render('Registro', [
            'retornoReserva' => $retorno,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // ── Validação Base ────────────────────────────────────────────────
        $rules = [
            'nome_completo' => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', 'max:100', 'unique:users,email'],
            'password'      => ['required', 'confirmed', Password::min(6)],
            'tipo'          => ['required', 'in:cliente,profissional'],
            'telefone'      => ['nullable', 'string', 'max:20'],
        ];

        // Campos extras para profissional
        if ($request->input('tipo') === 'profissional') {
            $rules['especialidade'] = ['nullable', 'string', 'max:150'];
        }

        $validated = $request->validate($rules);

        // Telefone de pré-cadastro NÃO dá acesso à conta existente (evita sequestro de conta):
        // cria-se uma conta nova. O vínculo com o pré-cadastro só ocorre pelo link gerado
        // pelo profissional (ConviteCadastroService).

        // ── Criação do Usuário ────────────────────────────────────────────
        $user = User::create([
            'nome_completo' => $validated['nome_completo'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'tipo'          => $validated['tipo'],
        ]);

        // ── Criação do Perfil ─────────────────────────────────────────────
        Perfil::create([
            'usuario_id' => $user->id,
            'telefone'   => $validated['telefone'] ?? null,
        ]);

        // ── Role (Spatie) + Registro profissional ─────────────────────────
        if ($validated['tipo'] === 'profissional') {
            // Login ANTES de assignRole para garantir sessão ativa
            Auth::login($user);
            $request->session()->regenerate();

            $user->assignRole('profissional');

            Profissional::create([
                'usuario_id'           => $user->id,
                'estabelecimento_id'   => null,   // preenchido depois no form de Gestão
                'nome_profissional'    => $validated['nome_completo'],
                'especialidade'        => $validated['especialidade'] ?? null,
            ]);

            // Limpar cache de permissões do Spatie para garantir que o middleware role:profissional
            // reconheça o papel imediatamente no próximo request
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Profissional segue para o onboarding, ignorando um eventual retorno de reserva
            $request->session()->forget('url.intended');

            return redirect('/profissional/estabelecimento')
                ->with('success', 'Cadastro realizado! Preencha os dados do seu estabelecimento.');
        }

        $user->assignRole('cliente');
        Auth::login($user);
        $request->session()->regenerate();

        // Se veio de uma reserva, volta para a página do estabelecimento com o serviço aberto
        return redirect()->intended(route('home'))
            ->with('success', 'Bem-vindo(a) ao EstetiFlow, ' . $user->nome_completo . '!');
    }
}
