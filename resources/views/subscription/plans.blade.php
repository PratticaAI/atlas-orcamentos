<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos — ATLAS · Orçamentos com IA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --atlas-primary:#C4622D; --atlas-dark:#1C1C1E; --atlas-gold:#E8A838; --atlas-bg:#F5F2EE; --atlas-border:#E0DDD8; }
        body { font-family:'Inter',sans-serif; background:var(--atlas-dark); color:#fff; }
        h1,h2,h3 { font-family:'Barlow Condensed',sans-serif; font-weight:700; }
        .plan-card { background:#fff; color:var(--atlas-dark); border-radius:14px; padding:2rem; position:relative; }
        .plan-card.featured { border:2px solid var(--atlas-primary); }
        .plan-price { font-family:'Barlow Condensed',sans-serif; font-size:2.5rem; font-weight:700; color:var(--atlas-primary); }
        .btn-plan { background:var(--atlas-primary); color:#fff; border:none; border-radius:8px; padding:0.65rem 1.5rem; font-weight:600; font-size:14px; width:100%; transition:background 0.15s; text-decoration:none; display:block; text-align:center; }
        .btn-plan:hover { background:#a8511f; color:#fff; }
        .btn-plan-outline { background:transparent; color:var(--atlas-primary); border:1.5px solid var(--atlas-primary); border-radius:8px; padding:0.6rem 1.5rem; font-weight:600; font-size:14px; width:100%; text-decoration:none; display:block; text-align:center; }
        .btn-plan-outline:hover { background:var(--atlas-primary); color:#fff; }
        .feature-item { font-size:13px; color:#555; padding:5px 0; display:flex; gap:8px; }
        .feature-item i { color:var(--atlas-primary); flex-shrink:0; margin-top:1px; }
    </style>
</head>
<body>
    <div class="container py-5">

        {{-- Header --}}
        <div class="text-center mb-5">
            <div style="font-family:'Barlow Condensed',sans-serif;font-size:1.4rem;font-weight:700;color:rgba(255,255,255,0.4);letter-spacing:0.1em;margin-bottom:0.5rem;">⬡ ATLAS · Prattica AI</div>
            <h1 style="font-size:3rem;margin-bottom:1rem;">Orçamentos com IA em minutos</h1>
            <p style="color:rgba(255,255,255,0.6);font-size:16px;max-width:480px;margin:0 auto;">
                SINAPI atualizada · PDF profissional com sua marca · Sem planilha, sem retrabalho
            </p>
            @if(session('warning'))
                <div style="background:#FFF8E1;color:#7B4F00;border-radius:8px;padding:0.75rem 1rem;margin-top:1.5rem;font-size:13px;">
                    <i class="bi bi-clock-history me-1"></i> {{ session('warning') }}
                </div>
            @endif
        </div>

        {{-- Cards de planos --}}
        <div class="row justify-content-center g-4 mb-5">
            @foreach($plans as $plan)
            <div class="col-md-4">
                <div class="plan-card {{ $plan->slug === 'pro' ? 'featured' : '' }}">
                    @if($plan->slug === 'pro')
                        <div style="position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:var(--atlas-primary);color:#fff;font-size:11px;font-weight:700;padding:3px 16px;border-radius:20px;white-space:nowrap;">
                            ⭐ MAIS POPULAR
                        </div>
                    @endif
                    <div style="font-family:'Barlow Condensed',sans-serif;font-size:1.3rem;font-weight:700;margin-bottom:0.5rem;">
                        {{ $plan->name }}
                    </div>
                    <div class="plan-price">
                        {{ $plan->priceFormatted() }}
                        <span style="font-size:14px;color:#888;font-weight:400;">/mês</span>
                    </div>
                    <div style="font-size:12px;color:#888;margin-bottom:1.5rem;">
                        {{ $plan->budget_limit ? "Até {$plan->budget_limit} orçamentos/mês" : 'Orçamentos ilimitados' }}
                        · até {{ $plan->user_limit }} usuário{{ $plan->user_limit > 1 ? 's' : '' }}
                    </div>

                    <div class="mb-4">
                        @foreach($plan->features ?? [] as $feature)
                            <div class="feature-item">
                                <i class="bi bi-check-circle-fill"></i>
                                {{ $feature }}
                            </div>
                        @endforeach
                    </div>

                    @auth
                        <form method="POST" action="{{ route('assinatura.checkout') }}">
                            @csrf
                            <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                            <button type="submit" class="{{ $plan->slug === 'pro' ? 'btn-plan' : 'btn-plan-outline' }}">
                                Assinar agora
                            </button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" class="{{ $plan->slug === 'pro' ? 'btn-plan' : 'btn-plan-outline' }}">
                            Começar trial grátis
                        </a>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>

        {{-- Garantias --}}
        <div class="text-center" style="color:rgba(255,255,255,0.4);font-size:13px;">
            <i class="bi bi-shield-check me-1"></i> Trial gratuito 14 dias · sem cartão ·
            <i class="bi bi-arrow-counterclockwise ms-2 me-1"></i> Cancele quando quiser ·
            <i class="bi bi-lock ms-2 me-1"></i> Pagamento seguro via Pagar.me
        </div>

        <div class="text-center mt-4">
            @auth
                <a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,0.4);font-size:13px;">← Voltar ao dashboard</a>
            @else
                <a href="{{ route('login') }}" style="color:rgba(255,255,255,0.4);font-size:13px;">Já tenho conta — fazer login</a>
            @endauth
        </div>

    </div>
</body>
</html>
