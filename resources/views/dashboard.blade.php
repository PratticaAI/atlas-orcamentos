@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

{{-- Saudação --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-0">Bom dia, {{ explode(' ', auth()->user()->name)[0] }} 👋</h3>
        <div style="font-size:13px;color:var(--atlas-muted);">{{ now()->translatedFormat('l, d \de F \de Y') }}</div>
    </div>
    <a href="{{ route('orcamentos.create') }}" class="btn-atlas d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Novo Orçamento
    </a>
</div>

{{-- Trial banner --}}
@if(auth()->user()->isOnTrial())
<div class="alert-atlas-warning d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-clock-history"></i>
        <span>Trial gratuito — expira em <strong>{{ auth()->user()->trial_ends_at->diffForHumans() }}</strong></span>
    </div>
    <a href="{{ route('assinatura') }}" class="btn-atlas-outline" style="font-size:12px;padding:0.3rem 0.85rem;">Assinar agora</a>
</div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="kpi-label">ORÇAMENTOS NO MÊS</div>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:36px;height:36px;background:rgba(196,98,45,0.1);">
                    <i class="bi bi-file-earmark-text" style="color:var(--atlas-primary);font-size:16px;"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $stats['budgets_month'] }}</div>
            @if($stats['budgets_limit'])
                <div style="font-size:11px;color:var(--atlas-muted);">de {{ $stats['budgets_limit'] }} disponíveis</div>
                <div class="progress mt-2" style="height:4px;border-radius:2px;background:#ece9e4;">
                    <div class="progress-bar" style="width:{{ min(100, ($stats['budgets_month'] / $stats['budgets_limit']) * 100) }}%;background:var(--atlas-primary);border-radius:2px;"></div>
                </div>
            @else
                <div style="font-size:11px;color:var(--atlas-muted);">ilimitados</div>
            @endif
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="kpi-label">TOTAL ORÇADOS (MÊS)</div>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:36px;height:36px;background:rgba(232,168,56,0.1);">
                    <i class="bi bi-currency-dollar" style="color:var(--atlas-gold);font-size:16px;"></i>
                </div>
            </div>
            <div class="kpi-value" style="font-size:1.4rem;">
                R$ {{ number_format($stats['total_value'], 0, ',', '.') }}
            </div>
            <div style="font-size:11px;color:var(--atlas-muted);">valor total acumulado</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="kpi-label">TOTAL DE PROJETOS</div>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:36px;height:36px;background:rgba(42,125,79,0.1);">
                    <i class="bi bi-folder2-open" style="color:var(--atlas-success);font-size:16px;"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $stats['total_budgets'] }}</div>
            <div style="font-size:11px;color:var(--atlas-muted);">orçamentos criados</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="kpi-label">PLANO ATIVO</div>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:36px;height:36px;background:rgba(28,28,30,0.08);">
                    <i class="bi bi-award" style="color:var(--atlas-dark);font-size:16px;"></i>
                </div>
            </div>
            <div class="kpi-value" style="font-size:1.3rem;">
                {{ auth()->user()->plan?->name ?? 'Trial' }}
            </div>
            <div style="font-size:11px;color:var(--atlas-muted);">
                {{ auth()->user()->plan?->priceFormatted() ?? '14 dias grátis' }}
            </div>
        </div>
    </div>
</div>

{{-- Recentes --}}
<div class="atlas-card">
    <div class="atlas-card-header">
        <h5 class="mb-0" style="font-size:15px;">Orçamentos recentes</h5>
        <a href="{{ route('orcamentos.index') }}" style="font-size:13px;color:var(--atlas-primary);text-decoration:none;">
            Ver todos <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="atlas-card-body p-0">
        @if($budgets->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-plus" style="font-size:2.5rem;color:var(--atlas-border);"></i>
                <h5 class="mt-3" style="color:var(--atlas-muted);">Nenhum orçamento ainda</h5>
                <p style="font-size:13px;color:var(--atlas-muted);">Crie seu primeiro orçamento e veja o resultado em minutos.</p>
                <a href="{{ route('orcamentos.create') }}" class="btn-atlas">
                    <i class="bi bi-plus-lg"></i> Criar primeiro orçamento
                </a>
            </div>
        @else
            <table class="atlas-table w-100">
                <thead>
                    <tr>
                        <th>PROJETO</th>
                        <th>TIPO</th>
                        <th>ÁREA</th>
                        <th>TOTAL</th>
                        <th>DATA</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgets as $budget)
                    <tr>
                        <td>
                            <div style="font-weight:500;">{{ $budget->title }}</div>
                            <div style="font-size:11px;color:var(--atlas-muted);">{{ $budget->standardLabel() }}</div>
                        </td>
                        <td>{{ $budget->workTypeLabel() }}</td>
                        <td>{{ number_format($budget->area_m2, 0, ',', '.') }} m²</td>
                        <td style="font-weight:600;color:var(--atlas-primary);">
                            R$ {{ number_format($budget->total, 0, ',', '.') }}
                        </td>
                        <td style="color:var(--atlas-muted);">{{ $budget->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('orcamentos.show', $budget) }}"
                               class="btn-atlas-outline" style="font-size:12px;padding:0.3rem 0.85rem;">
                                Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@endsection
