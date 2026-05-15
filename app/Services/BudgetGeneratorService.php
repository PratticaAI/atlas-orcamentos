<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetGeneratorService
{
    public function __construct(
        private readonly ClaudeService $claude,
        private readonly SinapiService $sinapi,
    ) {}

    /**
     * Ponto de entrada principal.
     * Orquestra: validação de limite → IA → SINAPI → persistência → cálculo de totais.
     *
     * @throws \RuntimeException
     */
    public function generate(User $user, array $input): Budget
    {
        $this->assertUserCanGenerate($user);

        // 1. Gera itens via IA
        $aiResult = $this->claude->generateBudgetItems(
            workType:    $input['work_type'],
            areaM2:      (float) $input['area_m2'],
            standard:    $input['standard'],
            state:       $input['state'],
            description: $input['description'] ?? '',
        );

        // 2. Enriquece com preços SINAPI
        $enrichedItems = $this->sinapi->enrichItems(
            items: $aiResult['items'],
            state: $input['state'],
        );

        // 3. Persiste orçamento + itens em transação
        return DB::transaction(function () use ($user, $input, $enrichedItems) {
            $bdi = (float) ($input['bdi_percent'] ?? config('atlas.sinapi_default_bdi', 25));

            $budget = Budget::create([
                'user_id'     => $user->id,
                'title'       => $input['title'] ?? $this->generateTitle($input),
                'work_type'   => $input['work_type'],
                'area_m2'     => $input['area_m2'],
                'standard'    => $input['standard'],
                'state'       => strtoupper($input['state']),
                'description' => $input['description'] ?? null,
                'bdi_percent' => $bdi,
                'status'      => 'draft',
                'ai_model'    => config('services.claude.model'),
            ]);

            $subtotal = 0;
            foreach ($enrichedItems as $index => $item) {
                $totalPrice = round($item['quantity'] * $item['unit_price'], 2);
                $subtotal  += $totalPrice;

                BudgetItem::create([
                    'budget_id'   => $budget->id,
                    'sinapi_code' => $item['sinapi_code'] ?? null,
                    'description' => $item['description'],
                    'unit'        => $item['unit'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $totalPrice,
                    'source'      => $item['source'] ?? 'ai',
                    'sort_order'  => $index,
                ]);
            }

            $bdiValue = round($subtotal * ($bdi / 100), 2);
            $total    = round($subtotal + $bdiValue, 2);

            $budget->update([
                'subtotal'  => $subtotal,
                'bdi_value' => $bdiValue,
                'total'     => $total,
                'status'    => 'generated',
            ]);

            // Incrementa contador mensal do usuário
            $this->incrementUserBudgetCount($user);

            return $budget->fresh(['items']);
        });
    }

    /**
     * Recalcula totais após edição inline de itens (Tela 3).
     */
    public function recalculateTotals(Budget $budget): Budget
    {
        return DB::transaction(function () use ($budget) {
            $subtotal = $budget->items()->sum(
                DB::raw('quantity * unit_price')
            );

            $bdiValue = round($subtotal * ($budget->bdi_percent / 100), 2);
            $total    = round($subtotal + $bdiValue, 2);

            $budget->update([
                'subtotal'  => round($subtotal, 2),
                'bdi_value' => $bdiValue,
                'total'     => $total,
            ]);

            return $budget->fresh();
        });
    }

    // ------------------------------------------------------------------

    private function assertUserCanGenerate(User $user): void
    {
        // Verifica trial ativo
        if ($user->trial_ends_at && $user->trial_ends_at->isFuture()) {
            return;
        }

        // Verifica assinatura ativa
        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$subscription) {
            throw new \RuntimeException('Seu período de trial expirou. Assine um plano para continuar gerando orçamentos.');
        }

        // Verifica limite do plano Solo (10/mês)
        if ($user->plan && $user->plan->budget_limit !== null) {
            $this->resetMonthlyCountIfNeeded($user);

            if ($user->budgets_this_month >= $user->plan->budget_limit) {
                throw new \RuntimeException(
                    "Você atingiu o limite de {$user->plan->budget_limit} orçamentos do plano {$user->plan->name} este mês."
                );
            }
        }
    }

    private function incrementUserBudgetCount(User $user): void
    {
        $this->resetMonthlyCountIfNeeded($user);
        $user->increment('budgets_this_month');
    }

    private function resetMonthlyCountIfNeeded(User $user): void
    {
        if (!$user->budgets_reset_at || $user->budgets_reset_at->month !== now()->month) {
            $user->update([
                'budgets_this_month' => 0,
                'budgets_reset_at'   => now()->startOfMonth(),
            ]);
        }
    }

    private function generateTitle(array $input): string
    {
        $types = [
            'residential' => 'Residência',
            'commercial'  => 'Comercial',
            'renovation'  => 'Reforma',
            'industrial'  => 'Galpão',
        ];

        $label = $types[$input['work_type']] ?? 'Obra';
        return "{$label} {$input['area_m2']}m² — " . strtoupper($input['state']);
    }
}
