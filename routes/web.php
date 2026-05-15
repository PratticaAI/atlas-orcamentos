<?php

use App\Http\Controllers\Budget\BudgetController;
use App\Http\Controllers\Subscription\WebhookController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ------------------------------------------------------------------
// Públicas
// ------------------------------------------------------------------
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/planos', [SubscriptionController::class, 'plans'])->name('planos');

// Webhook Pagar.me — sem CSRF (validado por HMAC internamente)
Route::post('/webhook/pagarme', [WebhookController::class, 'handle'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// ------------------------------------------------------------------
// Autenticadas — requer e-mail verificado + assinatura ativa
// ------------------------------------------------------------------
Route::middleware(['auth', 'verified', 'check.subscription'])->group(function () {

    // Dashboard (Tela 1)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Orçamentos (Telas 2, 3, 4)
    Route::resource('orcamentos', BudgetController::class)
        ->except(['edit', 'update']);

    Route::post('/orcamentos/{budget}/item/{item}', [BudgetController::class, 'updateItem'])
        ->name('orcamentos.item.update');

    Route::get('/orcamentos/{budget}/pdf', [BudgetController::class, 'downloadPdf'])
        ->name('orcamentos.pdf');

    Route::post('/orcamentos/{budget}/duplicar', [BudgetController::class, 'duplicate'])
        ->name('orcamentos.duplicate');

    // Perfil e marca (Tela 5)
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('perfil.update');
    Route::post('/perfil/logo', [ProfileController::class, 'uploadLogo'])->name('perfil.logo');

    // Assinatura
    Route::get('/assinatura', [SubscriptionController::class, 'index'])->name('assinatura');
    Route::post('/assinatura/checkout', [SubscriptionController::class, 'checkout'])->name('assinatura.checkout');
    Route::get('/assinatura/sucesso', [SubscriptionController::class, 'success'])->name('assinatura.sucesso');
});

// ------------------------------------------------------------------
// Auth (gerado pelo Breeze)
// ------------------------------------------------------------------
require __DIR__ . '/auth.php';
