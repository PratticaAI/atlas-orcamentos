<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;

    public function __construct()
    {
        $this->apiKey   = config('services.claude.api_key');
        $this->model    = config('services.claude.model', 'claude-sonnet-4-20250514');
        $this->maxTokens = (int) config('services.claude.max_tokens', 2000);
    }

    /**
     * Gera lista de itens de orçamento para uma obra.
     *
     * @return array{items: array<int, array{sinapi_code: string|null, description: string, unit: string, quantity: float}>}
     * @throws \RuntimeException
     */
    public function generateBudgetItems(
        string $workType,
        float  $areaM2,
        string $standard,
        string $state,
        string $description = ''
    ): array {
        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt   = $this->buildUserPrompt($workType, $areaM2, $standard, $state, $description);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model'      => $this->model,
                'max_tokens' => $this->maxTokens,
                'system'     => $systemPrompt,
                'messages'   => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

            if ($response->failed()) {
                Log::error('Claude API falhou', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException('Serviço de IA indisponível. Tente novamente em instantes.');
            }

            $content = $response->json('content.0.text', '');
            $items   = $this->parseItems($content);

            if (empty($items)) {
                throw new \RuntimeException('IA não retornou itens válidos. Tente detalhar melhor a descrição da obra.');
            }

            return ['items' => $items];

        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erro inesperado no ClaudeService', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Erro ao conectar com o serviço de IA.');
        }
    }

    // ------------------------------------------------------------------

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
Você é um especialista sênior em orçamentos de obras civis brasileiros com 20 anos de experiência.

REGRAS ABSOLUTAS:
1. Retorne APENAS um array JSON válido — sem texto antes, sem texto depois, sem markdown, sem ```json
2. Cada item do array deve ter exatamente estas chaves: sinapi_code, description, unit, quantity
3. sinapi_code: código real da tabela SINAPI quando possível, null se não souber
4. description: descrição técnica clara em português, seguindo nomenclatura SINAPI
5. unit: unidade de medida (m², m, un, kg, m³, vb, cj, l, t)
6. quantity: número decimal, calculado proporcionalmente à área informada
7. Gere entre 15 e 35 itens — cobrindo todos os serviços relevantes para o tipo de obra
8. Organize os itens na ordem lógica de execução da obra (serviços preliminares → fundação → estrutura → vedação → instalações → acabamentos)
PROMPT;
    }

    private function buildUserPrompt(
        string $workType,
        float  $areaM2,
        string $standard,
        string $state,
        string $description
    ): string {
        $workTypeLabel = match($workType) {
            'residential' => 'Edificação Residencial Unifamiliar',
            'commercial'  => 'Edificação Comercial',
            'renovation'  => 'Reforma / Ampliação',
            'industrial'  => 'Edificação Industrial / Galpão',
            default       => $workType,
        };

        $standardLabel = match($standard) {
            'simple' => 'Simples (padrão popular)',
            'medium' => 'Médio (padrão normal)',
            'high'   => 'Alto (padrão alto)',
            default  => $standard,
        };

        $prompt = "Gere itens de orçamento para:\n";
        $prompt .= "Tipo: {$workTypeLabel}\n";
        $prompt .= "Área total: {$areaM2} m²\n";
        $prompt .= "Padrão construtivo: {$standardLabel}\n";
        $prompt .= "Estado (SINAPI regional): {$state}\n";

        if (!empty($description)) {
            $prompt .= "Descrição adicional do cliente: {$description}\n";
        }

        return $prompt;
    }

    /**
     * Faz parse do JSON retornado pela Claude API.
     * Tolerante a pequenas variações de formato.
     */
    private function parseItems(string $content): array
    {
        $content = trim($content);

        // Remove possíveis blocos markdown que vieram mesmo com instrução
        $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
        $content = preg_replace('/\s*```$/m', '', $content);
        $content = trim($content);

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::warning('Claude retornou JSON inválido', ['content' => substr($content, 0, 500)]);
            return [];
        }

        // Valida estrutura mínima de cada item
        return array_filter($decoded, function ($item) {
            return isset($item['description'], $item['unit'], $item['quantity'])
                && is_string($item['description'])
                && is_string($item['unit'])
                && is_numeric($item['quantity'])
                && $item['quantity'] > 0;
        });
    }
}
