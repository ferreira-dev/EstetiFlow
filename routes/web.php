<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\EstabelecimentoController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Profissional\ProfissionalController;
use Illuminate\Support\Facades\Route;

// Públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/estabelecimentos', [EstabelecimentoController::class, 'index'])->name('estabelecimentos');
Route::get('/estabelecimentos/{id}', [EstabelecimentoController::class, 'show'])->name('estabelecimento.show');

// Auth
Route::get('/login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/registro', [RegistroController::class, 'create'])->name('registro')->middleware('guest');
Route::post('/registro', [RegistroController::class, 'store'])->middleware('guest');

// Protegidas — Cliente
Route::middleware('auth')->group(function () {
    Route::get('/agendamentos', [AgendamentoController::class, 'index'])->name('agendamentos');
    Route::post('/agendamentos', [AgendamentoController::class, 'store'])->name('agendamentos.store');
    Route::delete('/agendamentos/{id}', [AgendamentoController::class, 'destroy'])->name('agendamentos.destroy');
});

// Protegidas — Profissional
Route::middleware(['auth', 'role:profissional'])
    ->prefix('profissional')
    ->name('profissional.')
    ->group(function () {
        Route::get('/estabelecimento', [ProfissionalController::class, 'estabelecimento'])->name('estabelecimento');
        Route::post('/estabelecimento', [ProfissionalController::class, 'salvarEstabelecimento'])->name('estabelecimento.salvar');
    });
