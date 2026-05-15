<?php

namespace App\Services;

use App\Models\SinapiItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SinapiService
{
    private int $cacheDays;

    public function __construct()
    {
        $this->cacheDays = (int) config('atlas.sinapi_cache_days', 30);
    }

    /**
     * Enriquece itens gerados pela IA com preços reais da tabela SINAPI.
     * Para itens sem código SINAPI reconhecido, aplica preço estimado por tipo.
     *
     * @param  array<int, array{sinapi_code: string|null, description: string, unit: string, quantity: float}> $items
     * @return array<int, array{sinapi_code: string|null, description: string, unit: string, quantity: float, unit_price: float, source: string}>
     */
    public function enrichItems(array $items, string $state): array
    {
        $state  = strtoupper($state);
        $month  = now()->format('Y-m');

        return array_map(function (array $item) use ($state, $month) {
            $price  = null;
            $source = 'ai';

            // Tenta código SINAPI exato
            if (!empty($item['sinapi_code'])) {
                $price  = $this->getPriceBySinapiCode($item['sinapi_code'], $state, $month);
                $source = 'sinapi';
            }

            // Fallback: busca por similaridade de descrição
            if ($price === null) {
                $price  = $this->getPriceByDescription($item['description'], $item['unit'], $state, $month);
                $source = $price !== null ? 'sinapi' : 'ai';
            }

            // Fallback final: estimativa paramétrica por unidade
            if ($price === null) {
                $price  = $this->getEstimatedPrice($item['unit'], $item['description']);
                $source = 'estimated';
            }

            return array_merge($item, [
                'unit_price' => round($price, 2),
                'source'     => $source,
            ]);
        }, $items);
    }

    /**
     * Busca preço exato por código SINAPI + UF + mês de referência.
     * Cache Redis por 30 dias.
     */
    private function getPriceBySinapiCode(string $code, string $state, string $month): ?float
    {
        $cacheKey = "sinapi:{$code}:{$state}:{$month}";

        // Nunca armazena null no cache — item pode ser inserido depois
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return (float) $cached;
        }

        $item = SinapiItem::where('code', $code)
            ->where('state', $state)
            ->where('reference_month', $month)
            ->first();

        if ($item) {
            Cache::put($cacheKey, $item->price, now()->addDays($this->cacheDays));
            return (float) $item->price;
        }

        return null;
    }

    /**
     * Busca preço por similaridade de descrição (LIKE) quando código SINAPI é desconhecido.
     */
    private function getPriceByDescription(string $description, string $unit, string $state, string $month): ?float
    {
        // Extrai palavras-chave (remove stopwords curtas)
        $keywords = array_filter(
            explode(' ', strtolower($description)),
            fn($w) => strlen($w) > 3
        );

        if (empty($keywords)) {
            return null;
        }

        $cacheKey = 'sinapi:desc:' . md5($description . $state . $month);

        return Cache::remember($cacheKey, now()->addDays($this->cacheDays), function () use ($keywords, $unit, $state, $month) {
            $query = SinapiItem::where('state', $state)
                ->where('reference_month', $month)
                ->where('unit', $unit);

            foreach (array_slice($keywords, 0, 3) as $kw) {
                $query->where('description', 'ilike', "%{$kw}%");
            }

            $item = $query->first();
            return $item ? (float) $item->price : null;
        });
    }

    /**
     * Preço estimado como fallback final.
     * Valores médios nacionais calibrados — revisados mensalmente pelo time.
     */
    private function getEstimatedPrice(string $unit, string $description): float
    {
        $desc = strtolower($description);

        // Serviços de maior valor — identificados por palavras-chave
        $highValue = [
            'elevador'        => 85000.00,
            'ar condicionado' => 3500.00,
            'ar-condicionado' => 3500.00,
            'impermeabilização' => 85.00,
            'fachada'         => 350.00,
            'estrutura metálica' => 180.00,
        ];

        foreach ($highValue as $keyword => $price) {
            if (str_contains($desc, $keyword)) {
                return $price;
            }
        }

        // Estimativas por unidade de medida
        return match($unit) {
            'm²'  => 280.00,
            'm³'  => 420.00,
            'm'   => 95.00,
            'kg'  => 12.00,
            'un'  => 150.00,
            'cj'  => 320.00,
            'vb'  => 1200.00,
            'l'   => 18.00,
            't'   => 380.00,
            default => 200.00,
        };
    }
}
