<?php
// =============================================================
// ATLAS — Migrations de referência (Semana 1)
// Criar cada arquivo individualmente com os nomes abaixo:
//
// 2026_05_15_000001_create_plans_table.php
// 2026_05_15_000002_add_plan_fields_to_users_table.php
// 2026_05_15_000003_create_subscriptions_table.php
// 2026_05_15_000004_create_user_profiles_table.php
// 2026_05_15_000005_create_budgets_table.php
// 2026_05_15_000006_create_budget_items_table.php
// 2026_05_15_000007_create_sinapi_items_table.php
// =============================================================

// ---------------------------------------------------------------
// 1. plans
// ---------------------------------------------------------------
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name');                        // Solo, Profissional, Escritório
    $table->string('slug')->unique();              // solo, pro, office
    $table->integer('price_cents');                // 9700, 19700, 39700
    $table->integer('budget_limit')->nullable();   // null = ilimitado
    $table->integer('user_limit')->default(1);     // max usuários no plano
    $table->json('features')->nullable();          // array de features
    $table->string('pagarme_plan_id')->nullable(); // ID do plano no Pagar.me
    $table->boolean('active')->default(true);
    $table->timestamps();
});

// ---------------------------------------------------------------
// 2. users — campos adicionais (nunca alterar migration existente)
// ---------------------------------------------------------------
Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'plan_id')) {
        $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
    }
    if (!Schema::hasColumn('users', 'trial_ends_at')) {
        $table->timestamp('trial_ends_at')->nullable();
    }
    if (!Schema::hasColumn('users', 'budgets_this_month')) {
        $table->integer('budgets_this_month')->default(0);
    }
    if (!Schema::hasColumn('users', 'budgets_reset_at')) {
        $table->timestamp('budgets_reset_at')->nullable();
    }
});

// ---------------------------------------------------------------
// 3. subscriptions
// ---------------------------------------------------------------
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('plan_id')->constrained();
    $table->string('pagarme_subscription_id')->unique();
    $table->string('status');                      // active, canceled, past_due, trialing
    $table->timestamp('current_period_start')->nullable();
    $table->timestamp('current_period_end')->nullable();
    $table->timestamp('canceled_at')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
});

// ---------------------------------------------------------------
// 4. user_profiles
// ---------------------------------------------------------------
Schema::create('user_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    $table->string('company_name')->nullable();    // nome do escritório
    $table->string('crea_cau')->nullable();        // registro profissional
    $table->string('phone')->nullable();
    $table->string('logo_path')->nullable();       // storage/app/private/logos/
    $table->string('accent_color')->default('#C4622D'); // cor do PDF
    $table->string('city')->nullable();
    $table->string('state', 2)->nullable();
    $table->text('about')->nullable();
    $table->boolean('terms_accepted')->default(false);
    $table->timestamp('terms_accepted_at')->nullable();
    $table->timestamps();
});

// ---------------------------------------------------------------
// 5. budgets
// ---------------------------------------------------------------
Schema::create('budgets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('work_type');                   // residential, commercial, renovation, industrial
    $table->decimal('area_m2', 10, 2);
    $table->string('standard');                    // simple, medium, high
    $table->string('state', 2);                    // UF para SINAPI regional
    $table->text('description')->nullable();       // descrição livre do usuário
    $table->decimal('bdi_percent', 5, 2)->default(25.00);
    $table->decimal('subtotal', 15, 2)->default(0);
    $table->decimal('bdi_value', 15, 2)->default(0);
    $table->decimal('total', 15, 2)->default(0);
    $table->string('pdf_path')->nullable();        // storage/app/private/pdfs/
    $table->string('status')->default('draft');    // draft, generated, exported
    $table->string('ai_model')->nullable();        // versão do modelo usado
    $table->timestamps();
    $table->softDeletes();
});

// ---------------------------------------------------------------
// 6. budget_items
// ---------------------------------------------------------------
Schema::create('budget_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
    $table->string('sinapi_code')->nullable();
    $table->text('description');
    $table->string('unit', 20);                    // m², un, m, kg, vb...
    $table->decimal('quantity', 12, 3);
    $table->decimal('unit_price', 12, 2);
    $table->decimal('total_price', 15, 2);
    $table->string('source')->default('ai');       // ai, sinapi, manual
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

// ---------------------------------------------------------------
// 7. sinapi_items (tabela local — sync mensal)
// ---------------------------------------------------------------
Schema::create('sinapi_items', function (Blueprint $table) {
    $table->id();
    $table->string('code', 20)->index();
    $table->text('description');
    $table->string('unit', 20);
    $table->string('state', 2)->index();           // UF — preços variam por estado
    $table->decimal('price', 12, 2);
    $table->string('reference_month', 7);          // 2026-05
    $table->boolean('deonerado')->default(false);  // com ou sem desoneração
    $table->timestamps();

    $table->unique(['code', 'state', 'reference_month']);
});
