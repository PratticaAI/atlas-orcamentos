<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Trial ativo — libera acesso
        if ($user->trial_ends_at && $user->trial_ends_at->isFuture()) {
            return $next($request);
        }

        // Assinatura ativa — libera acesso
        $hasActive = $user->subscriptions()
            ->where('status', 'active')
            ->exists();

        if ($hasActive) {
            return $next($request);
        }

        // Sem acesso — redireciona para planos
        return redirect()
            ->route('planos')
            ->with('warning', 'Seu período de acesso expirou. Escolha um plano para continuar gerando orçamentos.');
    }
}
