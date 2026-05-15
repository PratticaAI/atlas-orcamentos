<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Página pública de planos
     */
    public function plans()
    {
        $plans = Plan::where('active', true)->orderBy('price_cents')->get();
        return view('subscription.plans', compact('plans'));
    }

    /**
     * Página de assinatura do usuário logado
     */
    public function index()
    {
        $user         = Auth::user();
        $subscription = $user->activeSubscription();
        $plans        = Plan::where('active', true)->orderBy('price_cents')->get();

        return view('subscription.index', compact('user', 'subscription', 'plans'));
    }

    /**
     * Inicia checkout no Pagar.me
     * TODO: integrar com Pagar.me Checkout v5
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan_slug' => 'required|exists:plans,slug',
        ]);

        $plan = Plan::where('slug', $request->plan_slug)->firstOrFail();

        // TODO: criar sessão de checkout no Pagar.me e redirecionar
        // $checkoutUrl = app(PagarmeService::class)->createCheckout($plan, Auth::user());
        // return redirect($checkoutUrl);

        return back()->with('info', 'Integração Pagar.me em implementação. Entre em contato via WhatsApp.');
    }

    /**
     * Retorno após pagamento aprovado
     */
    public function success()
    {
        return view('subscription.success');
    }
}
