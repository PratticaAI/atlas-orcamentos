@extends('layouts.app')

@section('title', 'Perfil e Marca')
@section('breadcrumb', 'Perfil e Marca')

@section('content')

<div class="row g-4">

    {{-- Coluna principal --}}
    <div class="col-lg-8">

        <h3 class="mb-4">Perfil Profissional</h3>

        {{-- Dados profissionais --}}
        <div class="atlas-card mb-4">
            <div class="atlas-card-header">
                <h5 class="mb-0" style="font-size:14px;">
                    <i class="bi bi-person-badge me-2" style="color:var(--atlas-primary);"></i>
                    Dados profissionais
                </h5>
            </div>
            <div class="atlas-card-body">
                <form method="POST" action="{{ route('perfil.update') }}">
                    @csrf @method('PUT')

                    @if($errors->any())
                        <div class="alert-atlas-error mb-3">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Nome do escritório / empresa</label>
                            <input type="text" name="company_name" class="form-control"
                                   value="{{ old('company_name', $profile->company_name) }}"
                                   placeholder="Ex: Silva Engenharia e Projetos">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CREA / CAU</label>
                            <input type="text" name="crea_cau" class="form-control"
                                   value="{{ old('crea_cau', $profile->crea_cau) }}"
                                   placeholder="Ex: CREA-MS 123456">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone / WhatsApp</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $profile->phone) }}"
                                   placeholder="Ex: (67) 99999-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="city" class="form-control"
                                   value="{{ old('city', $profile->city) }}"
                                   placeholder="Ex: Campo Grande">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select name="state" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach(['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'] as $uf)
                                    <option value="{{ $uf }}" {{ old('state', $profile->state) == $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Apresentação curta <span style="font-weight:400;color:var(--atlas-muted);">— aparece no rodapé do PDF</span></label>
                            <textarea name="about" class="form-control" rows="2" maxlength="500"
                                      placeholder="Ex: Especialista em projetos residenciais e comerciais. 15 anos de experiência em Campo Grande e região.">{{ old('about', $profile->about) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn-atlas">
                            <i class="bi bi-check-lg me-1"></i> Salvar dados
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Aparência do PDF --}}
        <div class="atlas-card">
            <div class="atlas-card-header">
                <h5 class="mb-0" style="font-size:14px;">
                    <i class="bi bi-palette me-2" style="color:var(--atlas-primary);"></i>
                    Aparência do PDF
                </h5>
            </div>
            <div class="atlas-card-body">
                <form method="POST" action="{{ route('perfil.update') }}" x-data="{ cor: '{{ $profile->accent_color ?? '#C4622D' }}' }">
                    @csrf @method('PUT')
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Cor de destaque</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" name="accent_color"
                                       x-model="cor"
                                       value="{{ old('accent_color', $profile->accent_color ?? '#C4622D') }}"
                                       style="width:48px;height:38px;border-radius:7px;border:1.5px solid var(--atlas-border);cursor:pointer;padding:2px;">
                                <input type="text" x-model="cor" readonly
                                       class="form-control" style="width:100px;font-family:monospace;font-size:13px;">
                                <div style="font-size:12px;color:var(--atlas-muted);">Aparece nos títulos e totais do PDF</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="background:var(--atlas-bg);border-radius:8px;padding:1rem;border:1px solid var(--atlas-border);">
                                <div style="font-size:11px;color:var(--atlas-muted);margin-bottom:6px;">PREVIEW</div>
                                <div style="font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;" :style="'color:' + cor">
                                    ORÇAMENTO #001
                                </div>
                                <div style="font-size:12px;color:#333;margin-top:4px;">Total: <strong :style="'color:' + cor">R$ 125.000,00</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn-atlas">
                            <i class="bi bi-check-lg me-1"></i> Salvar aparência
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Coluna lateral --}}
    <div class="col-lg-4">

        {{-- Upload de logo --}}
        <div class="atlas-card mb-4">
            <div class="atlas-card-header">
                <h5 class="mb-0" style="font-size:14px;">
                    <i class="bi bi-image me-2" style="color:var(--atlas-primary);"></i>
                    Logo do escritório
                </h5>
            </div>
            <div class="atlas-card-body text-center">
                @if($profile->hasLogo())
                    <div class="mb-3">
                        <img src="{{ Storage::url($profile->logo_path) }}"
                             alt="Logo" style="max-height:80px;max-width:180px;object-fit:contain;">
                    </div>
                @else
                    <div class="mb-3 py-3" style="background:var(--atlas-bg);border-radius:8px;">
                        <i class="bi bi-building" style="font-size:2rem;color:var(--atlas-border);"></i>
                        <div style="font-size:12px;color:var(--atlas-muted);margin-top:6px;">Sem logo cadastrado</div>
                    </div>
                @endif
                <form method="POST" action="{{ route('perfil.logo') }}" enctype="multipart/form-data">
                    @csrf
                    @error('logo')
                        <div class="alert-atlas-error mb-2" style="font-size:12px;">{{ $message }}</div>
                    @enderror
                    <input type="file" name="logo" accept="image/jpeg,image/png"
                           class="form-control mb-2" style="font-size:12px;">
                    <div style="font-size:11px;color:var(--atlas-muted);margin-bottom:10px;">
                        JPEG ou PNG · máximo 2MB
                    </div>
                    <button type="submit" class="btn-atlas-outline w-100">
                        <i class="bi bi-upload me-1"></i>
                        {{ $profile->hasLogo() ? 'Atualizar logo' : 'Enviar logo' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Info da conta --}}
        <div class="atlas-card">
            <div class="atlas-card-header">
                <h5 class="mb-0" style="font-size:14px;">
                    <i class="bi bi-person me-2" style="color:var(--atlas-primary);"></i>
                    Dados da conta
                </h5>
            </div>
            <div class="atlas-card-body">
                <div class="mb-2">
                    <div style="font-size:11px;color:var(--atlas-muted);">NOME</div>
                    <div style="font-size:13px;font-weight:500;">{{ $user->name }}</div>
                </div>
                <div class="mb-2">
                    <div style="font-size:11px;color:var(--atlas-muted);">E-MAIL</div>
                    <div style="font-size:13px;">{{ $user->email }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size:11px;color:var(--atlas-muted);">PLANO</div>
                    <div style="font-size:13px;font-weight:500;color:var(--atlas-primary);">
                        {{ $user->plan?->name ?? 'Trial' }}
                        @if($user->isOnTrial())
                            <span style="font-size:11px;color:var(--atlas-muted);font-weight:400;">
                                — expira {{ $user->trial_ends_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('assinatura') }}" class="btn-atlas-outline w-100 text-center d-block" style="font-size:12px;">
                    <i class="bi bi-credit-card me-1"></i> Gerenciar assinatura
                </a>
            </div>
        </div>

    </div>
</div>

@endsection
