<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ATLAS') — Orçamentos com IA</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Google Fonts: Barlow Condensed + Inter --}}
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --atlas-primary:   #C4622D;
            --atlas-dark:      #1C1C1E;
            --atlas-gold:      #E8A838;
            --atlas-bg:        #F5F2EE;
            --atlas-surface:   #FFFFFF;
            --atlas-border:    #E0DDD8;
            --atlas-muted:     #6B6860;
            --atlas-success:   #2A7D4F;
            --atlas-danger:    #C0392B;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--atlas-bg);
            color: var(--atlas-dark);
            font-size: 14px;
        }
        h1, h2, h3, h4, h5, .display-heading {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        /* Sidebar */
        .atlas-sidebar {
            width: 240px;
            min-height: 100vh;
            background: var(--atlas-dark);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }
        .atlas-sidebar .brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .atlas-sidebar .brand-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.05em;
        }
        .atlas-sidebar .brand-sub {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
        }
        .atlas-sidebar .nav-section {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            padding: 1.25rem 1.25rem 0.4rem;
        }
        .atlas-sidebar .nav-link {
            color: rgba(255,255,255,0.65);
            padding: 0.6rem 1.25rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            transition: all 0.15s;
        }
        .atlas-sidebar .nav-link:hover,
        .atlas-sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.07);
        }
        .atlas-sidebar .nav-link.active {
            border-left: 3px solid var(--atlas-primary);
            padding-left: calc(1.25rem - 3px);
        }
        .atlas-sidebar .nav-link i { font-size: 16px; }
        .atlas-sidebar .plan-badge {
            margin: auto 1.25rem 1.25rem;
            background: rgba(255,255,255,0.06);
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .atlas-sidebar .plan-badge .plan-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--atlas-gold);
        }
        .atlas-sidebar .plan-badge .plan-info {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
        }
        /* Main content */
        .atlas-main {
            margin-left: 240px;
            min-height: 100vh;
        }
        .atlas-topbar {
            background: var(--atlas-surface);
            border-bottom: 1px solid var(--atlas-border);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .atlas-page {
            padding: 1.5rem;
        }
        /* Cards */
        .atlas-card {
            background: var(--atlas-surface);
            border: 1px solid var(--atlas-border);
            border-radius: 10px;
        }
        .atlas-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--atlas-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .atlas-card-body { padding: 1.25rem; }
        /* KPI cards */
        .kpi-card {
            background: var(--atlas-surface);
            border: 1px solid var(--atlas-border);
            border-radius: 10px;
            padding: 1.25rem;
        }
        .kpi-value {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--atlas-dark);
        }
        .kpi-label { font-size: 12px; color: var(--atlas-muted); }
        /* Botões */
        .btn-atlas {
            background: var(--atlas-primary);
            color: #fff;
            border: none;
            font-weight: 600;
            font-size: 13px;
            padding: 0.5rem 1.1rem;
            border-radius: 7px;
            transition: background 0.15s;
        }
        .btn-atlas:hover { background: #a8511f; color: #fff; }
        .btn-atlas-outline {
            background: transparent;
            color: var(--atlas-primary);
            border: 1.5px solid var(--atlas-primary);
            font-weight: 600;
            font-size: 13px;
            padding: 0.45rem 1.1rem;
            border-radius: 7px;
            transition: all 0.15s;
        }
        .btn-atlas-outline:hover {
            background: var(--atlas-primary);
            color: #fff;
        }
        /* Badges de fonte */
        .source-sinapi { background: #E8F5E9; color: #2A7D4F; font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 600; }
        .source-ai      { background: #E3F2FD; color: #1565C0; font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 600; }
        .source-manual  { background: #FFF3E0; color: #E65100; font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 600; }
        .source-estimated { background: #F3E5F5; color: #6A1B9A; font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 600; }
        /* Alerts */
        .alert-atlas-success { background: #E8F5E9; border: 1px solid #A5D6A7; color: #1B5E20; border-radius: 8px; padding: 0.75rem 1rem; }
        .alert-atlas-error   { background: #FFEBEE; border: 1px solid #FFCDD2; color: #B71C1C; border-radius: 8px; padding: 0.75rem 1rem; }
        .alert-atlas-warning { background: #FFF8E1; border: 1px solid #FFE082; color: #7B4F00; border-radius: 8px; padding: 0.75rem 1rem; }
        /* Form controls */
        .form-control, .form-select {
            border: 1.5px solid var(--atlas-border);
            border-radius: 7px;
            font-size: 13.5px;
            padding: 0.5rem 0.85rem;
            background: #fff;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--atlas-primary);
            box-shadow: 0 0 0 3px rgba(196,98,45,0.12);
        }
        .form-label { font-weight: 500; font-size: 13px; color: var(--atlas-dark); margin-bottom: 5px; }
        /* Tabela */
        .atlas-table { font-size: 13px; }
        .atlas-table thead th {
            background: var(--atlas-bg);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--atlas-muted);
            border-bottom: 1px solid var(--atlas-border);
            padding: 0.6rem 0.85rem;
        }
        .atlas-table tbody td {
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid var(--atlas-border);
            vertical-align: middle;
        }
        .atlas-table tbody tr:last-child td { border-bottom: none; }
        .atlas-table tbody tr:hover { background: #fafaf9; }
    </style>

    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<div class="atlas-sidebar">
    <div class="brand">
        <div class="brand-name">⬡ ATLAS</div>
        <div class="brand-sub">Orçamentos com IA · Prattica AI</div>
    </div>

    <div class="nav-section">Menu</div>
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Dashboard
    </a>
    <a href="{{ route('orcamentos.create') }}" class="nav-link {{ request()->routeIs('orcamentos.create') ? 'active' : '' }}">
        <i class="bi bi-plus-circle"></i> Novo Orçamento
    </a>
    <a href="{{ route('orcamentos.index') }}" class="nav-link {{ request()->routeIs('orcamentos.index') ? 'active' : '' }}">
        <i class="bi bi-folder2-open"></i> Histórico
    </a>

    <div class="nav-section">Conta</div>
    <a href="{{ route('perfil.edit') }}" class="nav-link {{ request()->routeIs('perfil.*') ? 'active' : '' }}">
        <i class="bi bi-person-badge"></i> Perfil e Marca
    </a>
    <a href="{{ route('assinatura') }}" class="nav-link {{ request()->routeIs('assinatura*') ? 'active' : '' }}">
        <i class="bi bi-credit-card"></i> Assinatura
    </a>

    {{-- Plan badge --}}
    <div class="plan-badge mt-auto mb-3">
        @auth
            <div class="plan-name">
                {{ auth()->user()->plan?->name ?? 'Trial' }}
            </div>
            @if(auth()->user()->plan?->budget_limit)
                <div class="plan-info">
                    {{ auth()->user()->budgets_this_month ?? 0 }}/{{ auth()->user()->plan->budget_limit }} orçamentos este mês
                </div>
            @else
                <div class="plan-info">Orçamentos ilimitados</div>
            @endif
        @endauth
    </div>

    <form method="POST" action="{{ route('logout') }}" class="px-3 pb-3">
        @csrf
        <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" style="color:rgba(255,255,255,0.4)">
            <i class="bi bi-box-arrow-left"></i> Sair
        </button>
    </form>
</div>

{{-- Main --}}
<div class="atlas-main">
    {{-- Topbar --}}
    <div class="atlas-topbar">
        <div class="fw-600" style="font-size:14px; color: var(--atlas-muted);">
            @yield('breadcrumb', 'ATLAS')
        </div>
        @auth
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:32px;height:32px;background:var(--atlas-primary);color:#fff;font-size:13px;font-weight:600;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <span style="font-size:13px;">{{ auth()->user()->name }}</span>
        </div>
        @endauth
    </div>

    {{-- Alertas de sessão --}}
    <div class="atlas-page pb-0">
        @if(session('success'))
            <div class="alert-atlas-success mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-atlas-error mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="alert-atlas-warning mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}
            </div>
        @endif
    </div>

    {{-- Conteúdo --}}
    <div class="atlas-page">
        @yield('content')
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // CSRF global para AJAX
    window.CSRF = '{{ csrf_token() }}';
    const fmt = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
</script>
@stack('scripts')
</body>
</html>
