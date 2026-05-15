<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'         => 'Solo',
                'slug'         => 'solo',
                'price_cents'  => 9700,   // R$97,00
                'budget_limit' => 10,     // 10 orçamentos/mês
                'user_limit'   => 1,
                'features'     => [
                    'Até 10 orçamentos por mês',
                    'PDF com sua marca',
                    'SINAPI atualizada',
                    'Templates básicos',
                    'Histórico 3 meses',
                ],
                'active' => true,
            ],
            [
                'name'         => 'Profissional',
                'slug'         => 'pro',
                'price_cents'  => 19700,  // R$197,00
                'budget_limit' => null,   // ilimitado
                'user_limit'   => 3,
                'features'     => [
                    'Orçamentos ilimitados',
                    'PDF profissional personalizado',
                    'SINAPI atualizada',
                    'Todos os templates',
                    'Memorial descritivo com IA',
                    'Histórico completo',
                    'Relatório de margem',
                ],
                'active' => true,
            ],
            [
                'name'         => 'Escritório',
                'slug'         => 'office',
                'price_cents'  => 39700,  // R$397,00
                'budget_limit' => null,   // ilimitado
                'user_limit'   => 10,
                'features'     => [
                    'Tudo do Profissional',
                    'Até 10 usuários',
                    'Dashboard gerencial',
                    'Exportação Excel',
                    'Suporte prioritário',
                    'Onboarding dedicado',
                ],
                'active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Planos ATLAS criados: Solo · Profissional · Escritório');
    }
}
