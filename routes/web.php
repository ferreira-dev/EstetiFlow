<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\EstabelecimentoController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/estabelecimentos', [EstabelecimentoController::class, 'index'])->name('estabelecimentos');
Route::get('/estabelecimentos/{id}', [EstabelecimentoController::class, 'show'])->name('estabelecimento.show');

// Auth
Route::get('/login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Protegidas
Route::middleware('auth')->group(function () {
    Route::get('/agendamentos', [AgendamentoController::class, 'index'])->name('agendamentos');
    Route::post('/agendamentos', [AgendamentoController::class, 'store'])->name('agendamentos.store');
    Route::delete('/agendamentos/{id}', [AgendamentoController::class, 'destroy'])->name('agendamentos.destroy');
});
