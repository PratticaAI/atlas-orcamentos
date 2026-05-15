@extends('layouts.app')

@section('title', $budget->title)
@section('breadcrumb', 'Orçamentos / ' . $budget->title)

@section('content')

<div
    x-data="orcamentoEditor()"
    x-init="init()"
>

{{-- Header --}}
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h3 class="mb-1">{{ $budget->title }}</h3>
        <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size:12px;color:var(--atlas-muted);">
            <span><i class="bi bi-building me-1"></i>{{ $budget->workTypeLabel() }}</span>
            <span>·</span>
            <span><i class="bi bi-rulers me-1"></i>{{ number_format($budget->area_m2, 0, ',', '.') }} m²</span>
            <span>·</span>
            <span><i class="bi bi-award me-1"></i>{{ $budget->standardLabel() }}</span>
            <span>·</span>
            <span><i class="bi bi-geo-alt me-1"></i>{{ $budget->state }}</span>
            <span>·</span>
            <span><i class="bi bi-calendar me-1"></i>{{ $budget->created_at->format('d/m/Y') }}</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button
            @click="exportarPdf()"
            class="btn-atlas d-flex align-items-center gap-2"
            :disabled="pdfLoading"
        >
            <span x-show="!pdfLoading"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</span>
            <span x-show="pdfLoading" class="d-flex align-items-center gap-2">
                <span class="spinner-border spinner-border-sm"></span> Gerando PDF...
            </span>
        </button>
        <form method="POST" action="{{ route('orcamentos.duplicate', $budget) }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn-atlas-outline d-flex align-items-center gap-2">
                <i class="bi bi-copy"></i> Duplicar
            </button>
        </form>
    </div>
</div>

{{-- Resumo de totais --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-label mb-1">ITENS GERADOS</div>
            <div class="kpi-value">{{ $budget->items->count() }}</div>
            <div style="font-size:11px;color:var(--atlas-muted);">itens de orçamento</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-label mb-1">SUBTOTAL (SEM BDI)</div>
            <div class="kpi-value" style="font-size:1.35rem;" id="kpi-subtotal">
                R$ {{ number_format($budget->subtotal, 2, ',', '.') }}
            </div>
            <div style="font-size:11px;color:var(--atlas-muted);">antes do BDI</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-label mb-1">BDI ({{ number_format($budget->bdi_percent, 1) }}%)</div>
            <div class="kpi-value" style="font-size:1.35rem;" id="kpi-bdi">
                R$ {{ number_format($budget->bdi_value, 2, ',', '.') }}
            </div>
            <div style="font-size:11px;color:var(--atlas-muted);">benefícios e despesas indiretas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-color:var(--atlas-primary);border-width:2px;">
            <div class="kpi-label mb-1" style="color:var(--atlas-primary);">TOTAL FINAL</div>
            <div class="kpi-value" style="color:var(--atlas-primary);" id="kpi-total">
                R$ {{ number_format($budget->total, 2, ',', '.') }}
            </div>
            <div style="font-size:11px;color:var(--atlas-muted);">
                R$ {{ number_format($budget->total / max($budget->area_m2, 1), 2, ',', '.') }}/m²
            </div>
        </div>
    </div>
</div>

{{-- Aviso de edição --}}
<div class="alert-atlas-warning d-flex align-items-center gap-2 mb-3" style="font-size:13px;">
    <i class="bi bi-pencil-square"></i>
    <span>Clique em qualquer valor de <strong>quantidade</strong> ou <strong>preço unitário</strong> para editar. Os totais são atualizados automaticamente.</span>
</div>

{{-- Tabela de itens --}}
<div class="atlas-card">
    <div class="atlas-card-header">
        <h5 class="mb-0" style="font-size:14px;">
            <i class="bi bi-table me-2" style="color:var(--atlas-primary);"></i>
            Itens do orçamento
        </h5>
        <div style="font-size:12px;color:var(--atlas-muted);">
            <span class="source-sinapi me-1">SINAPI</span> preço da tabela ·
            <span class="source-ai ms-1 me-1">IA</span> estimado pela IA ·
            <span class="source-manual ms-1">MANUAL</span> editado por você
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="atlas-table w-100">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>DESCRIÇÃO</th>
                    <th style="width:70px;">UNID.</th>
                    <th style="width:110px;">QUANTIDADE</th>
                    <th style="width:130px;">PREÇO UNIT.</th>
                    <th style="width:130px;">TOTAL</th>
                    <th style="width:80px;">FONTE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budget->items as $item)
                <tr id="row-{{ $item->id }}" x-data="itemEditor({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }}, {{ $item->total_price }})">
                    <td style="color:var(--atlas-muted);font-size:11px;">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div style="font-weight:500;">{{ $item->description }}</div>
                        @if($item->sinapi_code)
                            <div style="font-size:11px;color:var(--atlas-muted);">SINAPI: {{ $item->sinapi_code }}</div>
                        @endif
                    </td>
                    <td style="color:var(--atlas-muted);">{{ $item->unit }}</td>

                    {{-- Quantidade editável --}}
                    <td>
                        <input
                            type="number"
                            x-model.number="qty"
                            @change="salvar({{ $item->id }})"
                            step="0.001" min="0.001"
                            class="form-control form-control-sm text-end"
                            style="width:90px;font-size:13px;"
                        >
                    </td>

                    {{-- Preço unitário editável --}}
                    <td>
                        <input
                            type="number"
                            x-model.number="price"
                            @change="salvar({{ $item->id }})"
                            step="0.01" min="0"
                            class="form-control form-control-sm text-end"
                            style="width:110px;font-size:13px;"
                        >
                    </td>

                    {{-- Total calculado --}}
                    <td style="font-weight:600;" x-text="'R$ ' + totalFormatado()"></td>

                    <td>
                        <span x-show="source === 'sinapi'" class="source-sinapi">SINAPI</span>
                        <span x-show="source === 'ai'" class="source-ai">IA</span>
                        <span x-show="source === 'manual'" class="source-manual">MANUAL</span>
                        <span x-show="source === 'estimated'" class="source-estimated">ESTIM.</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Ações finais --}}
<div class="d-flex justify-content-between align-items-center mt-4">
    <a href="{{ route('orcamentos.index') }}" style="font-size:13px;color:var(--atlas-muted);text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Voltar ao histórico
    </a>
    <div class="d-flex gap-2">
        <form method="POST"
              action="{{ route('orcamentos.destroy', $budget) }}"
              onsubmit="return confirm('Remover este orçamento? Esta ação não pode ser desfeita.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-atlas-outline" style="color:var(--atlas-danger);border-color:var(--atlas-danger);">
                <i class="bi bi-trash"></i> Remover
            </button>
        </form>
        <button @click="exportarPdf()" class="btn-atlas" :disabled="pdfLoading">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </button>
    </div>
</div>

</div>{{-- fim x-data --}}

@endsection

@push('scripts')
<script>
function orcamentoEditor() {
    return {
        pdfLoading: false,

        init() {},

        exportarPdf() {
            this.pdfLoading = true;
            fetch('{{ route('orcamentos.pdf', $budget) }}', {
                headers: { 'X-CSRF-TOKEN': window.CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'ready') {
                    window.open(data.url, '_blank');
                    this.pdfLoading = false;
                } else {
                    // PDF ainda sendo gerado — polling a cada 3s
                    setTimeout(() => this.exportarPdf(), 3000);
                }
            })
            .catch(() => {
                this.pdfLoading = false;
                Swal.fire({ icon: 'error', title: 'Erro ao gerar PDF', text: 'Tente novamente em instantes.', confirmButtonColor: '#C4622D' });
            });
        }
    }
}

function itemEditor(id, qty, price, total) {
    return {
        id, qty, price, total,
        source: '{{ "' }}' + (document.getElementById('row-' + id)?.dataset?.source || 'ai') + '{{ "' }}',
        salvando: false,

        totalFormatado() {
            const t = (this.qty * this.price);
            return t.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        salvar(itemId) {
            this.source = 'manual';
            this.salvando = true;
            fetch(`/orcamentos/{{ $budget->id }}/item/${itemId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: this.qty, unit_price: this.price })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('kpi-subtotal').textContent = 'R$ ' + data.subtotal.toLocaleString('pt-BR', {minimumFractionDigits:2});
                    document.getElementById('kpi-bdi').textContent      = 'R$ ' + data.bdi_value.toLocaleString('pt-BR', {minimumFractionDigits:2});
                    document.getElementById('kpi-total').textContent    = 'R$ ' + data.total.toLocaleString('pt-BR', {minimumFractionDigits:2});
                }
                this.salvando = false;
            })
            .catch(() => { this.salvando = false; });
        }
    }
}
</script>
@endpush
