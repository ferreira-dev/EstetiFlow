<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\EstabelecimentoController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\CompletarCadastroController;
use App\Http\Controllers\Profissional\ProfissionalController;
use App\Http\Controllers\Profissional\ServicoController;
use App\Http\Controllers\Profissional\HorarioController;
use App\Http\Controllers\Profissional\BloqueioController;
use App\Http\Controllers\Profissional\AgendamentoProfissionalController;
use App\Http\Controllers\Profissional\DashboardController;
use App\Http\Controllers\Profissional\FinanceiroController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

// Públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/estabelecimentos', [EstabelecimentoController::class, 'index'])->name('estabelecimentos');
Route::get('/estabelecimentos/{id}', [EstabelecimentoController::class, 'show'])
    ->whereNumber('id')
    ->name('estabelecimento.show');
Route::get('/agendar/{urlPersonalizada}', [EstabelecimentoController::class, 'showPorUrl'])
    ->where('urlPersonalizada', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('estabelecimento.agendar');

// Auth
Route::get('/login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/registro', [RegistroController::class, 'create'])->name('registro')->middleware('guest');
Route::post('/registro', [RegistroController::class, 'store'])->middleware('guest');
Route::get('/completar-cadastro/{token}', [CompletarCadastroController::class, 'create'])->name('completar-cadastro')->middleware('guest');
Route::post('/completar-cadastro', [CompletarCadastroController::class, 'store'])->middleware('guest');

// Protegidas — Cliente + Profissional
Route::middleware('auth')->group(function () {
    Route::get('/agendamentos', [AgendamentoController::class, 'index'])->name('agendamentos');
    Route::post('/agendamentos', [AgendamentoController::class, 'store'])->name('agendamentos.store');
    Route::delete('/agendamentos/{id}', [AgendamentoController::class, 'destroy'])->name('agendamentos.destroy');

    // Perfil
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/senha', [PerfilController::class, 'alterarSenha'])->name('perfil.senha');
});

// Protegidas — Profissional
Route::middleware(['auth', 'role:profissional'])
    ->prefix('profissional')
    ->name('profissional.')
    ->group(function () {
        // Dashboard (página inicial do painel)
        Route::redirect('', '/profissional/dashboard')->name('redirect');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Estabelecimento
        Route::get('/estabelecimento', [ProfissionalController::class, 'estabelecimento'])->name('estabelecimento');
        Route::post('/estabelecimento', [ProfissionalController::class, 'salvarEstabelecimento'])->name('estabelecimento.salvar');

        // Serviços
        Route::get('/servicos', [ServicoController::class, 'index'])->name('servicos');
        Route::post('/servicos', [ServicoController::class, 'store'])->name('servicos.store');
        Route::put('/servicos/{id}', [ServicoController::class, 'update'])->name('servicos.update');
        Route::delete('/servicos/{id}', [ServicoController::class, 'destroy'])->name('servicos.destroy');

        // Horários de funcionamento
        Route::get('/horarios', [HorarioController::class, 'index'])->name('horarios');
        Route::post('/horarios', [HorarioController::class, 'salvar'])->name('horarios.salvar');

        // Bloqueios de agenda
        Route::get('/bloqueios', [BloqueioController::class, 'index'])->name('bloqueios');
        Route::post('/bloqueios', [BloqueioController::class, 'store'])->name('bloqueios.store');
        Route::delete('/bloqueios/{id}', [BloqueioController::class, 'destroy'])->name('bloqueios.destroy');

        // Agendamentos do profissional
        Route::get('/agendamentos', [AgendamentoProfissionalController::class, 'index'])->name('agendamentos.profissional');
        Route::put('/agendamentos/{id}/status', [AgendamentoProfissionalController::class, 'alterarStatus'])->name('agendamentos.status');
        Route::get('/agendamentos/buscar-cliente', [AgendamentoProfissionalController::class, 'buscarCliente'])->name('agendamentos.buscarCliente');
        Route::post('/agendamentos/pre-cadastro', [AgendamentoProfissionalController::class, 'preCadastrarCliente'])->name('agendamentos.preCadastro');
        Route::post('/agendamentos', [AgendamentoProfissionalController::class, 'criarAgendamento'])->name('agendamentos.store');

        // Financeiro
        Route::get('/financeiro', [FinanceiroController::class, 'index'])->name('financeiro');
    });
