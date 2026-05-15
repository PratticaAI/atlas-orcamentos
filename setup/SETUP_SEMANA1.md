# ATLAS — Setup Semana 1
# Guia completo para o desenvolvedor iniciar o projeto do zero

## 1. Criar projeto Laravel 12

```bash
composer create-project laravel/laravel atlas-orcamentos "12.*"
cd atlas-orcamentos
```

## 2. Instalar dependências PHP

```bash
# Auth (Breeze com Blade)
composer require laravel/breeze --dev
php artisan breeze:install blade

# Dependências de produção
composer require guzzlehttp/guzzle          # HTTP client para Claude API
composer require pagarme/pagarme-php        # SDK Pagar.me

# Atualizar lock file ANTES de commitar
composer update
```

## 3. Configurar banco PostgreSQL

```bash
# Criar banco
createdb atlas_db

# Configurar .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=atlas_db
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

## 4. Criar estrutura de pastas

```bash
mkdir -p app/Http/Controllers/{Budget,Sinapi,Subscription,Profile}
mkdir -p app/Services
mkdir -p app/Jobs
mkdir -p resources/views/{budgets,subscription,profile}
mkdir -p database/migrations
```

## 5. Instalar WeasyPrint (PDF)

```bash
# No servidor Hostinger (Ubuntu)
pip install weasyprint --break-system-packages

# Verificar instalação
weasyprint --version

# Configurar .env
WEASYPRINT_PATH=/usr/local/bin/weasyprint
```

## 6. Configurar Redis (cache SINAPI + Queue)

```bash
# Instalar extensão PHP Redis
sudo apt-get install php8.2-redis

# .env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## 7. Rodar migrations

```bash
# Copiar migrations de docs/migrations-reference.php
# Criar cada arquivo individualmente em database/migrations/
php artisan migrate
```

## 8. Adicionar config em config/services.php

```php
'claude' => [
    'api_key'    => env('CLAUDE_API_KEY'),
    'model'      => env('CLAUDE_MODEL', 'claude-sonnet-4-20250514'),
    'max_tokens' => env('CLAUDE_MAX_TOKENS', 2000),
],

'pagarme' => [
    'api_key'        => env('PAGARME_API_KEY'),
    'public_key'     => env('PAGARME_PUBLIC_KEY'),
    'webhook_secret' => env('PAGARME_WEBHOOK_SECRET'),
    'environment'    => env('PAGARME_ENVIRONMENT', 'sandbox'),
],
```

## 9. Adicionar config em config/atlas.php (criar arquivo)

```php
<?php
return [
    'sinapi_cache_days'  => env('SINAPI_CACHE_DAYS', 30),
    'sinapi_default_bdi' => env('SINAPI_DEFAULT_BDI', 25),
    'pdf_signed_minutes' => env('PDF_SIGNED_URL_MINUTES', 15),
    'trial_days'         => 14,
    'founder_discount'   => 0.40,
    'founder_limit'      => 50,
];
```

## 10. Configurar rotas principais (routes/web.php)

```php
// Públicas
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/planos', [SubscriptionController::class, 'plans'])->name('planos');
Route::post('/webhook/pagarme', [WebhookController::class, 'handle']);

// Autenticadas
Route::middleware(['auth', 'verified', 'check.subscription'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('orcamentos', BudgetController::class);
    Route::post('/orcamentos/{budget}/recalcular', [BudgetController::class, 'recalculate']);
    Route::get('/orcamentos/{budget}/pdf', [BudgetController::class, 'downloadPdf']);
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('perfil.update');
    Route::get('/assinatura', [SubscriptionController::class, 'index'])->name('assinatura');
});
```

## 11. Middleware CheckSubscription (criar)

```php
// app/Http/Middleware/CheckSubscription.php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    // Trial válido — passa
    if ($user->trial_ends_at && $user->trial_ends_at->isFuture()) {
        return $next($request);
    }

    // Assinatura ativa — passa
    $active = $user->subscriptions()->where('status', 'active')->exists();
    if ($active) {
        return $next($request);
    }

    // Sem acesso — redireciona para planos
    return redirect()->route('planos')->with('warning', 'Seu período de acesso expirou. Escolha um plano para continuar.');
}
```

## 12. Iniciar Queue Worker (desenvolvimento)

```bash
php artisan queue:work --queue=default,pdf
```

## 13. Verificação final

```bash
php artisan route:list          # confirmar todas as rotas
php artisan config:cache        # cachear config
php artisan migrate:status      # confirmar migrations
php artisan queue:monitor       # monitorar jobs
```

## Ordem de implementação recomendada (Semana 1)

1. [ ] Setup Laravel + Breeze + migrations
2. [ ] ClaudeService + teste manual no tinker
3. [ ] SinapiService + seed com 100 itens SINAPI para MS
4. [ ] BudgetGeneratorService integrando os dois
5. [ ] BudgetController (store) + formulário básico (Tela 2)
6. [ ] Preview de itens sem edição ainda (Tela 3 básica)
7. [ ] Commit e push — demo interno para Ricardo

## Notas para o desenvolvedor

- **Nunca** usar `cat >>` em arquivos PHP — sempre edição cirúrgica
- **Sempre** `hasColumn()`/`hasTable()` em migrations
- **Sempre** `DB::transaction()` em operações multi-tabela  
- Claude API: testar com 5-10 chamadas antes de integrar — verificar formato JSON
- Pagar.me: usar ambiente **sandbox** até lançamento, nunca produção em desenvolvimento
- WeasyPrint: testar geração de PDF simples antes de criar template completo
