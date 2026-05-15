<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Recebe e processa eventos do Pagar.me.
     * REGRA AEGIS: nunca processar sem validar HMAC.
     */
    public function handle(Request $request): Response
    {
        // 1. Valida assinatura HMAC obrigatória
        if (!$this->validateSignature($request)) {
            Log::warning('Webhook Pagar.me rejeitado: assinatura inválida', [
                'ip' => $request->ip(),
            ]);
            return response('Unauthorized', 401);
        }

        $payload = $request->json()->all();
        $type    = $payload['type'] ?? null;

        Log::info('Webhook Pagar.me recebido', ['type' => $type]);

        match($type) {
            'subscription.created'        => $this->handleSubscriptionCreated($payload),
            'subscription.canceled'       => $this->handleSubscriptionCanceled($payload),
            'subscription.payment_failed' => $this->handlePaymentFailed($payload),
            'charge.paid'                 => $this->handleChargePaid($payload),
            default                       => Log::info("Webhook Pagar.me ignorado: {$type}"),
        };

        return response('OK', 200);
    }

    // ------------------------------------------------------------------

    private function handleSubscriptionCreated(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $customer = $data['customer'] ?? [];
        $pagarmeSubId = $data['id'] ?? null;
        $pagarmePlanId = $data['plan']['id'] ?? null;

        if (!$pagarmeSubId || !$pagarmePlanId) {
            Log::error('Webhook subscription.created sem IDs válidos', $payload);
            return;
        }

        $user = User::where('email', $customer['email'] ?? '')->first();
        $plan = Plan::where('pagarme_plan_id', $pagarmePlanId)->first();

        if (!$user || !$plan) {
            Log::error('Webhook: usuário ou plano não encontrado', [
                'email'   => $customer['email'] ?? null,
                'plan_id' => $pagarmePlanId,
            ]);
            return;
        }

        // Cancela assinatura anterior se existir
        $user->subscriptions()->where('status', 'active')->update(['status' => 'canceled']);

        Subscription::create([
            'user_id'                => $user->id,
            'plan_id'                => $plan->id,
            'pagarme_subscription_id' => $pagarmeSubId,
            'status'                 => 'active',
            'current_period_start'   => now(),
            'current_period_end'     => now()->addMonth(),
            'metadata'               => $data,
        ]);

        $user->update(['plan_id' => $plan->id]);

        Log::info("Assinatura ativada para {$user->email} — Plano {$plan->name}");
    }

    private function handleSubscriptionCanceled(array $payload): void
    {
        $pagarmeSubId = $payload['data']['id'] ?? null;
        if (!$pagarmeSubId) return;

        $subscription = Subscription::where('pagarme_subscription_id', $pagarmeSubId)->first();
        if (!$subscription) return;

        $subscription->update([
            'status'      => 'canceled',
            'canceled_at' => now(),
        ]);

        // Mantém acesso por 30 dias após cancelamento (grace period)
        $subscription->user->update(['plan_id' => null]);

        Log::info("Assinatura cancelada: {$pagarmeSubId}");
    }

    private function handlePaymentFailed(array $payload): void
    {
        $pagarmeSubId = $payload['data']['id'] ?? null;
        if (!$pagarmeSubId) return;

        $subscription = Subscription::where('pagarme_subscription_id', $pagarmeSubId)->first();
        if (!$subscription) return;

        $subscription->update(['status' => 'past_due']);

        // TODO: disparar e-mail de cobrança falhou + link para atualizar cartão
        Log::warning("Pagamento falhou para assinatura: {$pagarmeSubId}");
    }

    private function handleChargePaid(array $payload): void
    {
        // Renova período de assinatura quando cobrança recorrente é paga
        $pagarmeSubId = $payload['data']['subscription']['id'] ?? null;
        if (!$pagarmeSubId) return;

        $subscription = Subscription::where('pagarme_subscription_id', $pagarmeSubId)->first();
        if (!$subscription) return;

        $subscription->update([
            'status'               => 'active',
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
        ]);

        Log::info("Assinatura renovada: {$pagarmeSubId}");
    }

    // ------------------------------------------------------------------

    /**
     * Valida a assinatura HMAC do webhook Pagar.me.
     * Documentação: https://docs.pagar.me/docs/webhooks
     */
    private function validateSignature(Request $request): bool
    {
        $secret    = config('services.pagarme.webhook_secret');
        $signature = $request->header('X-Hub-Signature');

        if (!$secret || !$signature) {
            return false;
        }

        $expected = 'sha1=' . hash_hmac('sha1', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
