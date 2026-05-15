@extends('layouts.app')

@section('title', 'Novo Orçamento')
@section('breadcrumb', 'Orçamentos / Novo')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Header --}}
        <div class="mb-4">
            <h3 class="mb-1">Novo Orçamento</h3>
            <p style="font-size:13px;color:var(--atlas-muted);">
                Preencha os dados da obra. A IA gera os itens com preços SINAPI atualizados em instantes.
            </p>
        </div>

        {{-- Erros de validação --}}
        @if($errors->any())
            <div class="alert-atlas-error mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <strong>Corrija os campos abaixo:</strong>
                </div>
                <ul class="mb-0 ps-3" style="font-size:13px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('orcamentos.store') }}" id="form-orcamento">
            @csrf

            {{-- Tipo e padrão --}}
            <div class="atlas-card mb-3">
                <div class="atlas-card-header">
                    <h5 class="mb-0" style="font-size:14px;">
                        <i class="bi bi-building me-2" style="color:var(--atlas-primary);"></i>
                        Tipo de obra
                    </h5>
                </div>
                <div class="atlas-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de obra *</label>
                            <select name="work_type" class="form-select @error('work_type') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                <option value="residential" {{ old('work_type') == 'residential' ? 'selected' : '' }}>Residencial Unifamiliar</option>
                                <option value="commercial"  {{ old('work_type') == 'commercial'  ? 'selected' : '' }}>Comercial</option>
                                <option value="renovation"  {{ old('work_type') == 'renovation'  ? 'selected' : '' }}>Reforma / Ampliação</option>
                                <option value="industrial"  {{ old('work_type') == 'industrial'  ? 'selected' : '' }}>Industrial / Galpão</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Padrão construtivo *</label>
                            <select name="standard" class="form-select @error('standard') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                <option value="simple" {{ old('standard') == 'simple' ? 'selected' : '' }}>Simples (padrão popular)</option>
                                <option value="medium" {{ old('standard') == 'medium' ? 'selected' : '' }}>Médio (padrão normal)</option>
                                <option value="high"   {{ old('standard') == 'high'   ? 'selected' : '' }}>Alto (padrão alto)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dimensões e localização --}}
            <div class="atlas-card mb-3">
                <div class="atlas-card-header">
                    <h5 class="mb-0" style="font-size:14px;">
                        <i class="bi bi-rulers me-2" style="color:var(--atlas-primary);"></i>
                        Dimensões e localização
                    </h5>
                </div>
                <div class="atlas-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Área total (m²) *</label>
                            <input type="number"
                                   name="area_m2"
                                   class="form-control @error('area_m2') is-invalid @enderror"
                                   value="{{ old('area_m2') }}"
                                   min="10" max="50000"
                                   step="0.5"
                                   placeholder="Ex: 120"
                                   required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado da obra *</label>
                            <select name="state" class="form-select @error('state') is-invalid @enderror" required>
                                <option value="">UF...</option>
                                @foreach(['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'] as $uf)
                                    <option value="{{ $uf }}" {{ old('state') == $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">BDI (%) <span style="color:var(--atlas-muted);font-weight:400;">padrão 25%</span></label>
                            <input type="number"
                                   name="bdi_percent"
                                   class="form-control"
                                   value="{{ old('bdi_percent', 25) }}"
                                   min="0" max="100" step="0.5"
                                   placeholder="25">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Identificação e descrição --}}
            <div class="atlas-card mb-4">
                <div class="atlas-card-header">
                    <h5 class="mb-0" style="font-size:14px;">
                        <i class="bi bi-card-text me-2" style="color:var(--atlas-primary);"></i>
                        Identificação e detalhes
                    </h5>
                </div>
                <div class="atlas-card-body">
                    <div class="mb-3">
                        <label class="form-label">
                            Nome do projeto
                            <span style="color:var(--atlas-muted);font-weight:400;">— opcional (gerado automaticamente se não informado)</span>
                        </label>
                        <input type="text"
                               name="title"
                               class="form-control"
                               value="{{ old('title') }}"
                               placeholder="Ex: Residência João Silva — Campo Grande MS">
                    </div>
                    <div>
                        <label class="form-label">
                            Descrição adicional
                            <span style="color:var(--atlas-muted);font-weight:400;">— quanto mais detalhar, melhor o orçamento</span>
                        </label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Ex: Casa térrea com 3 quartos, 2 banheiros, churrasqueira coberta. Fundação em radier. Cobertura em telha cerâmica. Não incluir piscina.">{{ old('description') }}</textarea>
                        <div style="font-size:11px;color:var(--atlas-muted);margin-top:4px;">
                            Especifique características especiais, itens que não devem ser incluídos, ou detalhes relevantes.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Aviso de tempo --}}
            <div class="d-flex align-items-start gap-2 mb-4" style="background:#F5F2EE;border:1px solid var(--atlas-border);border-radius:8px;padding:0.85rem 1rem;">
                <i class="bi bi-cpu" style="color:var(--atlas-primary);font-size:18px;margin-top:1px;"></i>
                <div>
                    <div style="font-size:13px;font-weight:500;">A IA leva entre 15 e 45 segundos para gerar o orçamento.</div>
                    <div style="font-size:12px;color:var(--atlas-muted);">Os itens são gerados com base na tabela SINAPI atualizada para o estado selecionado. Você poderá revisar e editar todos os valores antes de exportar o PDF.</div>
                </div>
            </div>

            {{-- Botões --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn-atlas d-flex align-items-center gap-2" id="btn-gerar">
                    <i class="bi bi-stars"></i> Gerar Orçamento com IA
                </button>
                <a href="{{ route('dashboard') }}" class="btn-atlas-outline">Cancelar</a>
            </div>
        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('form-orcamento').addEventListener('submit', function() {
    const btn = document.getElementById('btn-gerar');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Gerando com IA...';
    btn.disabled = true;
});
</script>
@endpush
