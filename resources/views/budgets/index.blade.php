@extends('layouts.app')

@section('title', 'Histórico de Orçamentos')
@section('breadcrumb', 'Histórico de Orçamentos')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Histórico de Orçamentos</h3>
    <a href="{{ route('orcamentos.create') }}" class="btn-atlas d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Novo Orçamento
    </a>
</div>

{{-- Filtros --}}
<div class="atlas-card mb-4">
    <div class="atlas-card-body">
        <form method="GET" action="{{ route('orcamentos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Buscar por nome</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Nome do projeto..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de obra</label>
                <select name="work_type" class="form-select">
                    <option value="">Todos</option>
                    <option value="residential" {{ request('work_type') == 'residential' ? 'selected' : '' }}>Residencial</option>
                    <option value="commercial"  {{ request('work_type') == 'commercial'  ? 'selected' : '' }}>Comercial</option>
                    <option value="renovation"  {{ request('work_type') == 'renovation'  ? 'selected' : '' }}>Reforma</option>
                    <option value="industrial"  {{ request('work_type') == 'industrial'  ? 'selected' : '' }}>Industrial</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-atlas w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
            @if(request('search') || request('work_type'))
            <div class="col-md-2">
                <a href="{{ route('orcamentos.index') }}" class="btn-atlas-outline w-100 text-center d-block">
                    Limpar
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Lista --}}
<div class="atlas-card">
    @if($budgets->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-folder2-open" style="font-size:2.5rem;color:var(--atlas-border);"></i>
            <h5 class="mt-3" style="color:var(--atlas-muted);">
                {{ request('search') || request('work_type') ? 'Nenhum orçamento encontrado com esses filtros.' : 'Nenhum orçamento ainda.' }}
            </h5>
            @if(!request('search') && !request('work_type'))
                <p style="font-size:13px;color:var(--atlas-muted);">Crie seu primeiro orçamento e veja o resultado em minutos.</p>
                <a href="{{ route('orcamentos.create') }}" class="btn-atlas">
                    <i class="bi bi-plus-lg"></i> Criar orçamento
                </a>
            @endif
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="atlas-table w-100">
                <thead>
                    <tr>
                        <th>PROJETO</th>
                        <th>TIPO</th>
                        <th>ÁREA</th>
                        <th>PADRÃO</th>
                        <th>UF</th>
                        <th>TOTAL</th>
                        <th>DATA</th>
                        <th>STATUS</th>
                        <th style="width:130px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgets as $budget)
                    <tr>
                        <td>
                            <a href="{{ route('orcamentos.show', $budget) }}"
                               style="font-weight:500;color:var(--atlas-dark);text-decoration:none;">
                                {{ $budget->title }}
                            </a>
                            <div style="font-size:11px;color:var(--atlas-muted);">
                                {{ $budget->items_count ?? '—' }} itens
                            </div>
                        </td>
                        <td>{{ $budget->workTypeLabel() }}</td>
                        <td>{{ number_format($budget->area_m2, 0, ',', '.') }} m²</td>
                        <td>{{ $budget->standardLabel() }}</td>
                        <td>{{ $budget->state }}</td>
                        <td style="font-weight:600;color:var(--atlas-primary);">
                            R$ {{ number_format($budget->total, 0, ',', '.') }}
                        </td>
                        <td style="color:var(--atlas-muted);">
                            {{ $budget->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            @if($budget->status === 'exported')
                                <span style="background:#E8F5E9;color:#2A7D4F;font-size:11px;padding:2px 8px;border-radius:4px;font-weight:600;">PDF</span>
                            @elseif($budget->status === 'generated')
                                <span style="background:#E3F2FD;color:#1565C0;font-size:11px;padding:2px 8px;border-radius:4px;font-weight:600;">Gerado</span>
                            @else
                                <span style="background:#F3F3F3;color:#666;font-size:11px;padding:2px 8px;border-radius:4px;font-weight:600;">Rascunho</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('orcamentos.show', $budget) }}"
                                   class="btn-atlas-outline" style="font-size:12px;padding:0.3rem 0.75rem;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('orcamentos.duplicate', $budget) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-atlas-outline" style="font-size:12px;padding:0.3rem 0.75rem;" title="Duplicar">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('orcamentos.destroy', $budget) }}"
                                      style="display:inline;"
                                      onsubmit="return confirm('Remover este orçamento?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-atlas-outline"
                                            style="font-size:12px;padding:0.3rem 0.75rem;color:var(--atlas-danger);border-color:var(--atlas-danger);"
                                            title="Remover">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($budgets->hasPages())
        <div class="atlas-card-body border-top d-flex align-items-center justify-content-between">
            <div style="font-size:12px;color:var(--atlas-muted);">
                Mostrando {{ $budgets->firstItem() }}–{{ $budgets->lastItem() }} de {{ $budgets->total() }} orçamentos
            </div>
            {{ $budgets->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    @endif
</div>

@endsection
