@extends('layouts.app')

@section('title', 'Minha Assinatura')
@section('breadcrumb', 'Assinatura')

@section('content')

<h3 class="mb-4">Assinatura</h3>

<div class="row g-4">
    <div class="col-lg-5">

        {{-- Status atual --}}
        <div class="atlas-card mb-4">
            <div class="atlas-card-header">
                <h5 class="mb-0" style="font-size:14px;">
                    <i class="bi bi-award me-2" style="color:var(--atlas-primary);"></i>
                    Plano atual
                </h5>
            </div>
            <div class="atlas-card-body">
                @if($user->isOnTrial())
                    <div style="background:#FFF8E1;border:1px solid #FFE082;border-radius:8px;padding:1rem;margin-bottom:1rem;">
                        <div style="font-size:13px;font-weight:600;color:#7B4F00;">
                            <i class="bi bi-clock-history me-1"></i> Trial gratuito
                        </div>
                        <div style="font-size:12px;color:#7B4F00;margin-top:3px;">
                            Expira {{ $user->trial_ends_at->format('d/m/Y') }}
                            ({{ $user->trial_ends_at->diffForHumans() }})
                        </div>
                    </div>
                @elseif($subscription)
                    <div style="background:#E8F5E9;border:1px solid #A5D6A7;border-radius:8px;padding:1rem;margin-bottom:1rem;">
                        <div style="font-size:13px;font-weight:600;color:#1B5E20;">
                            <i class="bi bi-check-circle-fill me-1"></i> Assinatura ativa
                        </div>
                        <div style="font-size:12px;color:#1B5E20;margin-top:3px;">
                            Próxima cobrança: {{ $subscription->current_period_end?->format('d/m/Y') }}
                        </div>
                    </div>
                @else
                    <div class="alert-atlas-error mb-3">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                        Sem assinatura ativa. Escolha um plano abaixo.
                    </div>
                @endif

                <div class="mb-2">
                    <div style="font-size:11px;color:var(--atlas-muted);">PLANO</div>
                    <div style="font-size:15px;font-weight:700;color:var(--atlas-primary);">
                        {{ $user->plan?->name ?? 'Trial' }}
                        @if($user->plan) — {{ $user->plan->priceFormatted() }}/mês @endif
                    </div>
                </div>
                <div class="mb-3">
                    <div style="font-size:11px;color:var(--atlas-muted);">USO NO MÊS</div>
                    <div style="font-size:13px;font-weight:500;">
                        {{ $user->budgets_this_month ?? 0 }}
                        @if($user->plan?->budget_limit)
                            / {{ $user->plan->budget_limit }} orçamentos
                        @else
                            orçamentos (ilimitado)
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-lg-7">
        {{-- Planos disponíveis --}}
        <h5 class="mb-3" style="font-size:15px;">Alterar plano</h5>
        <div class="d-flex flex-column gap-3">
            @foreach($plans as $plan)
            <div class="atlas-card {{ $user->plan?->id === $plan->id ? 'border-2' : '' }}"
                 style="{{ $user->plan?->id === $plan->id ? 'border-color:var(--atlas-primary);' : '' }}">
                <div class="atlas-card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            @if($plan->slug === 'pro')
                                <span style="background:var(--atlas-primary);color:#fff;font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;margin-bottom:6px;display:inline-block;">MAIS POPULAR</span><br>
                            @endif
                            <div style="font-family:'Barlow Condensed',sans-serif;font-size:1.2rem;font-weight:700;">
                                {{ $plan->name }}
                            </div>
                            <div style="font-size:1.5rem;font-weight:700;color:var(--atlas-primary);">
                                {{ $plan->priceFormatted() }}<span style="font-size:13px;color:var(--atlas-muted);font-weight:400;">/mês</span>
                            </div>
                            <ul class="mt-2 mb-0 ps-3" style="font-size:12px;color:var(--atlas-muted);">
                                @foreach($plan->features ?? [] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            @if($user->plan?->id === $plan->id)
                                <span style="background:#E8F5E9;color:#2A7D4F;font-size:11px;font-weight:600;padding:4px 12px;border-radius:6px;">
                                    <i class="bi bi-check2"></i> Atual
                                </span>
                            @else
                                <form method="POST" action="{{ route('assinatura.checkout') }}">
                                    @csrf
                                    <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                                    <button type="submit" class="btn-atlas-outline" style="font-size:12px;">
                                        Assinar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-3" style="font-size:12px;color:var(--atlas-muted);">
            <i class="bi bi-shield-check me-1"></i>
            Pagamento seguro via Pagar.me · Cancele a qualquer momento · Sem taxa de cancelamento
        </div>
    </div>
</div>

@endsection
