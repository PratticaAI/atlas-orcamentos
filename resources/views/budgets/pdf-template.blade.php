<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4; margin: 2cm 1.8cm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #1C1C1E; }

        /* Variável de cor do usuário */
        :root { --accent: {{ $budget->user->profile?->accent_color ?? '#C4622D' }}; }

        /* Cabeçalho */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid var(--accent); padding-bottom: 12px; margin-bottom: 16px; }
        .header .logo-area img { max-height: 55px; max-width: 160px; object-fit: contain; }
        .header .logo-area .company-name { font-size: 14pt; font-weight: 700; color: var(--accent); letter-spacing: 0.03em; }
        .header .logo-area .crea { font-size: 8pt; color: #666; margin-top: 3px; }
        .header .doc-info { text-align: right; }
        .header .doc-title { font-size: 18pt; font-weight: 700; color: var(--accent); letter-spacing: 0.05em; }
        .header .doc-num { font-size: 9pt; color: #666; margin-top: 2px; }
        .header .doc-date { font-size: 8pt; color: #888; }

        /* Dados da obra */
        .obra-info { background: #F5F2EE; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; display: flex; gap: 24px; flex-wrap: wrap; }
        .obra-field { }
        .obra-field .label { font-size: 7pt; text-transform: uppercase; letter-spacing: 0.06em; color: #888; font-weight: 700; }
        .obra-field .value { font-size: 10pt; font-weight: 600; color: #1C1C1E; margin-top: 2px; }

        /* Tabela */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead tr { background: #1C1C1E; }
        thead th { color: #fff; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.07em; padding: 6px 7px; text-align: left; font-weight: 700; }
        thead th.text-right { text-align: right; }
        tbody tr:nth-child(even) { background: #FAFAF8; }
        tbody tr { border-bottom: 1px solid #E8E5E0; }
        tbody td { padding: 5px 7px; font-size: 8.5pt; vertical-align: top; }
        tbody td.text-right { text-align: right; }
        tbody td.text-center { text-align: center; }
        .item-desc { font-weight: 500; }
        .item-sinapi { font-size: 7pt; color: #888; margin-top: 1px; }
        .source-badge { font-size: 6.5pt; font-weight: 700; border-radius: 3px; padding: 1px 4px; }
        .source-sinapi { background: #E8F5E9; color: #2A7D4F; }
        .source-ai      { background: #E3F2FD; color: #1565C0; }
        .source-manual  { background: #FFF3E0; color: #E65100; }
        .source-estimated { background: #F3E5F5; color: #6A1B9A; }

        /* Totais */
        .totals { width: 280px; margin-left: auto; margin-bottom: 20px; }
        .totals table { font-size: 9pt; }
        .totals table td { padding: 5px 10px; border-bottom: 1px solid #E8E5E0; }
        .totals table td:last-child { text-align: right; font-weight: 600; }
        .totals .total-row td { background: var(--accent); color: #fff; font-size: 11pt; font-weight: 700; border-radius: 0; }

        /* Rodapé */
        .footer { border-top: 1px solid #E8E5E0; padding-top: 10px; font-size: 7.5pt; color: #888; display: flex; justify-content: space-between; }
        .footer .about { max-width: 60%; }
        .footer .contact { text-align: right; }

        /* Aviso */
        .disclaimer { font-size: 7pt; color: #aaa; border-top: 1px solid #eee; padding-top: 8px; margin-top: 8px; }
    </style>
</head>
<body>

{{-- CABEÇALHO --}}
<div class="header">
    <div class="logo-area">
        @if($budget->user->profile?->hasLogo())
            <img src="{{ storage_path('app/private/' . $budget->user->profile->logo_path) }}" alt="Logo">
        @else
            <div class="company-name">
                {{ $budget->user->profile?->company_name ?? $budget->user->name }}
            </div>
        @endif
        @if($budget->user->profile?->crea_cau)
            <div class="crea">{{ $budget->user->profile->crea_cau }}</div>
        @endif
    </div>
    <div class="doc-info">
        <div class="doc-title">ORÇAMENTO</div>
        <div class="doc-num">#{{ str_pad($budget->id, 4, '0', STR_PAD_LEFT) }}</div>
        <div class="doc-date">Emitido em {{ $budget->created_at->format('d/m/Y') }}</div>
    </div>
</div>

{{-- DADOS DA OBRA --}}
<div class="obra-info">
    <div class="obra-field">
        <div class="label">Projeto</div>
        <div class="value">{{ $budget->title }}</div>
    </div>
    <div class="obra-field">
        <div class="label">Tipo</div>
        <div class="value">{{ $budget->workTypeLabel() }}</div>
    </div>
    <div class="obra-field">
        <div class="label">Área</div>
        <div class="value">{{ number_format($budget->area_m2, 0, ',', '.') }} m²</div>
    </div>
    <div class="obra-field">
        <div class="label">Padrão</div>
        <div class="value">{{ $budget->standardLabel() }}</div>
    </div>
    <div class="obra-field">
        <div class="label">Estado</div>
        <div class="value">{{ $budget->state }}</div>
    </div>
    <div class="obra-field">
        <div class="label">BDI</div>
        <div class="value">{{ number_format($budget->bdi_percent, 1) }}%</div>
    </div>
</div>

{{-- TABELA DE ITENS --}}
<table>
    <thead>
        <tr>
            <th style="width:28px;">#</th>
            <th>Descrição</th>
            <th style="width:55px;">Cód. SINAPI</th>
            <th style="width:38px;" class="text-center">Unid.</th>
            <th style="width:55px;" class="text-right">Qtd.</th>
            <th style="width:75px;" class="text-right">Preço Unit.</th>
            <th style="width:80px;" class="text-right">Total</th>
            <th style="width:38px;" class="text-center">Fonte</th>
        </tr>
    </thead>
    <tbody>
        @foreach($budget->items as $index => $item)
        <tr>
            <td style="color:#888;font-size:7.5pt;">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
            <td>
                <div class="item-desc">{{ $item->description }}</div>
            </td>
            <td style="font-size:7.5pt;color:#888;">{{ $item->sinapi_code ?? '—' }}</td>
            <td class="text-center">{{ $item->unit }}</td>
            <td class="text-right">{{ number_format($item->quantity, 3, ',', '.') }}</td>
            <td class="text-right">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
            <td class="text-right" style="font-weight:600;">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
            <td class="text-center">
                <span class="source-badge source-{{ $item->source }}">
                    {{ strtoupper(substr($item->source, 0, 3)) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- TOTAIS --}}
<div class="totals">
    <table>
        <tr>
            <td>Subtotal (sem BDI)</td>
            <td>R$ {{ number_format($budget->subtotal, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>BDI ({{ number_format($budget->bdi_percent, 1) }}%)</td>
            <td>R$ {{ number_format($budget->bdi_value, 2, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL GERAL</td>
            <td>R$ {{ number_format($budget->total, 2, ',', '.') }}</td>
        </tr>
    </table>
</div>

{{-- RODAPÉ --}}
<div class="footer">
    <div class="about">
        @if($budget->user->profile?->about)
            <div>{{ $budget->user->profile->about }}</div>
        @endif
        @if($budget->user->profile?->city)
            <div>{{ $budget->user->profile->city }}{{ $budget->user->profile->state ? ', ' . $budget->user->profile->state : '' }}</div>
        @endif
    </div>
    <div class="contact">
        @if($budget->user->profile?->phone)
            <div>{{ $budget->user->profile->phone }}</div>
        @endif
        <div>{{ $budget->user->email }}</div>
    </div>
</div>

<div class="disclaimer">
    Este orçamento é uma estimativa paramétrica com base na tabela SINAPI · Mês de referência: {{ now()->format('m/Y') }} ·
    Valores sujeitos a alteração conforme projeto executivo e condições de obra ·
    Gerado pelo ATLAS — Prattica AI Solutions
</div>

</body>
</html>
