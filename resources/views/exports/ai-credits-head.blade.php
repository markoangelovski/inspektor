<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $website->name }} — Strapi AI Credits</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 14px;
            color: #1a1a1a;
            background: #f8f8f8;
            margin: 0;
            padding: 24px;
        }
        h1 { font-size: 20px; font-weight: 700; margin: 0 0 4px; }
        h2 { font-size: 15px; font-weight: 600; margin: 0 0 12px; }
        .meta { font-size: 12px; color: #666; margin-bottom: 24px; }
        .meta a { color: #4945FF; text-decoration: none; }

        .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 8px; }
        @media (max-width: 700px) { .cards { grid-template-columns: 1fr 1fr; } }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px; }
        .card-label { font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
        .card-value { font-size: 22px; font-weight: 700; color: #111; }
        .card-note { font-size: 11px; margin-top: 4px; color: #9ca3af; }
        .card-note.warn { color: #d97706; font-weight: 600; }
        .card-calc { font-size: 11px; margin-top: 6px; color: #6b7280; line-height: 1.7; border-top: 1px solid #f3f4f6; padding-top: 6px; }
        .card-calc strong { color: #111; }

        .footnote { font-size: 11px; color: #9ca3af; margin-bottom: 20px; }

        .table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; margin-bottom: 20px; }
        .table-header { padding: 14px 16px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        thead th.r { text-align: right; }

        tbody tr.main-row { cursor: pointer; transition: background .1s; }
        tbody tr.main-row:hover td { background: #f9fafb; }
        tbody tr.main-row td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
        td.r { text-align: right; font-variant-numeric: tabular-nums; }
        td.url { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .chevron { display: inline-block; transition: transform .15s; font-size: 14px; color: #9ca3af; }
        .chevron.open { transform: rotate(180deg); }

        /* Sub-table (expanded row) */
        tr.content-row { display: none; }
        tr.content-row.visible { display: table-row; }
        tr.content-row > td { padding: 0; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        .sub-table { width: 100%; border-collapse: collapse; }
        .sub-table tr { border-bottom: 1px solid #f3f4f6; }
        .sub-table tr:last-child { border-bottom: none; }
        .sub-table td { padding: 7px 14px; color: #374151; font-size: 13px; vertical-align: top; }
        .sub-table td.r { text-align: right; font-variant-numeric: tabular-nums; color: #4b5563; }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
            margin-right: 6px;
            vertical-align: middle;
        }
        .badge-title      { background: #dbeafe; color: #1d4ed8; }
        .badge-description{ background: #f3e8ff; color: #7e22ce; }
        .badge-heading    { background: #fef3c7; color: #92400e; }
        .badge-paragraph  { background: #d1fae5; color: #065f46; }
        .badge-list-item  { background: #ccfbf1; color: #0f766e; }
        .badge-cell       { background: #ffedd5; color: #9a3412; }
        .badge-alt-text   { background: #fce7f3; color: #9d174d; }
        .badge-default    { background: #f3f4f6; color: #374151; }

        /* Pagination */
        .pagination { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-top: 1px solid #e5e7eb; font-size: 13px; color: #6b7280; }
        .page-btns { display: flex; gap: 4px; flex-wrap: wrap; }
        .page-btn { padding: 4px 10px; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; cursor: pointer; font-size: 13px; color: #374151; }
        .page-btn:hover { background: #f3f4f6; }
        .page-btn.active { background: #4945FF; border-color: #4945FF; color: #fff; }
        .page-btn:disabled { opacity: .4; cursor: default; }

        .page-group { display: none; }
        tr.main-row.active { display: table-row; }
        tr.content-row.active.visible { display: table-row; }

        /* Info modal */
        .modal-overlay { display: none; position: fixed; inset: 0; z-index: 50; background: rgba(0,0,0,.5); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 16px; }
        .modal-overlay.open { display: flex; }
        .modal { position: relative; background: #fff; border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,.25); max-width: 640px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; border: 1px solid #e5e7eb; overflow: hidden; }
        .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #f3f4f6; background: linear-gradient(to right, rgba(73,69,255,.05), transparent); flex-shrink: 0; }
        .modal-icon { width: 28px; height: 28px; border-radius: 8px; background: rgba(73,69,255,.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 12px; }
        .modal-title { font-size: 14px; font-weight: 600; color: #111; margin: 0; }
        .modal-subtitle { font-size: 12px; color: #9ca3af; margin: 2px 0 0; }
        .modal-close { background: none; border: none; cursor: pointer; color: #9ca3af; padding: 4px; border-radius: 6px; display: flex; align-items: center; justify-content: center; line-height: 1; }
        .modal-close:hover { background: #f3f4f6; color: #374151; }
        .modal-body { overflow-y: auto; padding: 20px 24px; flex: 1; }
        .modal-footer { padding: 12px 24px; border-top: 1px solid #f3f4f6; background: #f9fafb; font-size: 12px; color: #9ca3af; flex-shrink: 0; }
        .modal-footer a { color: #4945FF; text-decoration: none; }
        .modal-footer a:hover { text-decoration: underline; }
        /* Markdown content inside modal */
        .strapi-ref h2 { font-size: 14px; font-weight: 600; margin: 16px 0 8px; color: #111; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .strapi-ref h3 { font-size: 13px; font-weight: 600; margin: 12px 0 6px; color: #374151; }
        .strapi-ref p { margin: 0 0 10px; line-height: 1.6; color: #374151; font-size: 13px; }
        .strapi-ref ul, .strapi-ref ol { margin: 0 0 10px; padding-left: 20px; }
        .strapi-ref li { margin-bottom: 4px; line-height: 1.6; color: #374151; font-size: 13px; }
        .strapi-ref strong { font-weight: 600; color: #111; }
        .strapi-ref code { background: #f3f4f6; padding: 1px 5px; border-radius: 3px; font-size: 12px; font-family: monospace; }
        .strapi-ref table { width: 100%; border-collapse: collapse; margin: 0 0 10px; font-size: 12px; }
        .strapi-ref th { padding: 6px 10px; background: #f9fafb; border: 1px solid #e5e7eb; font-weight: 600; text-align: left; color: #374151; }
        .strapi-ref td { padding: 6px 10px; border: 1px solid #e5e7eb; color: #374151; }
        .strapi-ref a { color: #4945FF; }
        .strapi-ref hr { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }
        /* Info button */
        .info-btn { background: none; border: none; cursor: pointer; color: #9ca3af; padding: 0; display: inline-flex; align-items: center; vertical-align: middle; }
        .info-btn:hover { color: #4945FF; }
    </style>
</head>
<body>

<h1>{{ $website->name }} — Strapi AI Credits</h1>
<div class="meta">
    Generated on {{ now()->format('Y-m-d H:i') }} &nbsp;·&nbsp;
    Source: <a href="{{ $website->url }}" target="_blank">{{ $website->url }}</a>
</div>

@php
    $billable1 = max(0, $totals['total_credits_one'] - 1000);
    $overage1  = round($billable1 / 100 * 1.5, 2);
    $billable5 = max(0, $totals['total_credits_five'] - 1000);
    $overage5  = round($billable5 / 100 * 1.5, 2);
    $adjBillable1 = max(0, $adjustedTotals['total_credits_one'] - 1000);
    $adjOverage1  = round($adjBillable1 / 100 * 1.5, 2);
    $adjBillable5 = max(0, $adjustedTotals['total_credits_five'] - 1000);
    $adjOverage5  = round($adjBillable5 / 100 * 1.5, 2);
    $wordsSaved   = $totals['total_words'] - $adjustedTotals['total_words'];
@endphp

<div class="cards">
    <div class="card">
        <div class="card-label">Pages</div>
        <div class="card-value">{{ number_format($totals['page_count']) }}</div>
    </div>
    <div class="card">
        <div class="card-label">Total words</div>
        <div class="card-value">{{ number_format($totals['total_words']) }}</div>
    </div>
    <div class="card">
        <div class="card-label">Credits × 1 language</div>
        <div class="card-value">{{ number_format($totals['total_credits_one'], 1) }}</div>
        @if ($totals['total_credits_one'] > 1000)
            <div class="card-note warn">+${{ number_format($overage1, 2) }} overage</div>
            <div class="card-calc">
                {{ number_format($totals['total_credits_one'], 1) }} total credits<br>
                − 1,000 included in plan<br>
                = {{ number_format($billable1, 1) }} billable credits<br>
                <strong>{{ number_format($billable1, 1) }} ÷ 100 × $1.50 = ${{ number_format($overage1, 2) }}</strong>
            </div>
        @else
            <div class="card-note">within 1,000 credit plan</div>
        @endif
    </div>
    <div class="card">
        <div class="card-label">Credits × 5 languages</div>
        <div class="card-value">{{ number_format($totals['total_credits_five'], 1) }}</div>
        @if ($totals['total_credits_five'] > 1000)
            <div class="card-note warn">+${{ number_format($overage5, 2) }} overage</div>
            <div class="card-calc">
                {{ number_format($totals['total_credits_five'], 1) }} total credits<br>
                − 1,000 included in plan<br>
                = {{ number_format($billable5, 1) }} billable credits<br>
                <strong>{{ number_format($billable5, 1) }} ÷ 100 × $1.50 = ${{ number_format($overage5, 2) }}</strong>
            </div>
        @else
            <div class="card-note">within 1,000 credit plan</div>
        @endif
    </div>
</div>
<div style="margin-bottom:8px;">
    <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:8px;">
        Adjusted for repeated content
        <span style="font-weight:400;color:#9ca3af;">— identical segments across pages counted once</span>
    </div>
    <div class="cards" style="grid-template-columns:repeat(3,1fr);">
        <div class="card">
            <div class="card-label">Unique words</div>
            <div class="card-value">{{ number_format($adjustedTotals['total_words']) }}</div>
            @if ($wordsSaved > 0)
                <div class="card-note">{{ number_format($wordsSaved) }} words excluded (repeated)</div>
            @endif
        </div>
        <div class="card">
            <div class="card-label">Credits × 1 language</div>
            <div class="card-value">{{ number_format($adjustedTotals['total_credits_one'], 1) }}</div>
            @if ($adjustedTotals['total_credits_one'] > 1000)
                <div class="card-note warn">+${{ number_format($adjOverage1, 2) }} overage</div>
                <div class="card-calc">
                    {{ number_format($adjustedTotals['total_credits_one'], 1) }} total credits<br>
                    − 1,000 included in plan<br>
                    = {{ number_format($adjBillable1, 1) }} billable credits<br>
                    <strong>{{ number_format($adjBillable1, 1) }} ÷ 100 × $1.50 = ${{ number_format($adjOverage1, 2) }}</strong>
                </div>
            @else
                <div class="card-note">within 1,000 credit plan</div>
            @endif
        </div>
        <div class="card">
            <div class="card-label">Credits × 5 languages</div>
            <div class="card-value">{{ number_format($adjustedTotals['total_credits_five'], 1) }}</div>
            @if ($adjustedTotals['total_credits_five'] > 1000)
                <div class="card-note warn">+${{ number_format($adjOverage5, 2) }} overage</div>
                <div class="card-calc">
                    {{ number_format($adjustedTotals['total_credits_five'], 1) }} total credits<br>
                    − 1,000 included in plan<br>
                    = {{ number_format($adjBillable5, 1) }} billable credits<br>
                    <strong>{{ number_format($adjBillable5, 1) }} ÷ 100 × $1.50 = ${{ number_format($adjOverage5, 2) }}</strong>
                </div>
            @else
                <div class="card-note">within 1,000 credit plan</div>
            @endif
        </div>
    </div>
</div>

<div class="footnote" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
    <span>
        Estimates use: 1 language → 0.0711 + 0.002098&times;words; 5 languages → 0.1265 + 0.003725&times;words (per segment).
        Strapi Growth plan includes 1,000 credits/month ($45/mo); overages at $1.50 per 100 credits.
    </span>
    <button class="info-btn" onclick="openInfoModal()" title="View Strapi AI Credits reference">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 01.67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 11-.671-1.34l.041-.022zM12 9a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
        </svg>
    </button>
</div>

<!-- Strapi AI Credits reference modal -->
<div id="info-modal" class="modal-overlay" onclick="if(event.target===this)closeInfoModal()">
    <div class="modal">
        <div class="modal-header">
            <div style="display:flex;align-items:center;">
                <div class="modal-icon">
                    <svg style="width:16px;height:16px;color:#4945FF" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-.5 5a.5.5 0 0 1 1 0v5.5H17a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5V7z"/>
                    </svg>
                </div>
                <div>
                    <div class="modal-title">Strapi AI Credits Reference</div>
                    <div class="modal-subtitle">Growth plan · $45/mo · 1,000 credits included</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeInfoModal()" title="Close">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body strapi-ref">
            {!! $strapiMdHtml !!}
        </div>
        <div class="modal-footer">
            Source:
            <a href="https://strapi.io/pricing-cms" target="_blank">strapi.io/pricing-cms</a>
            &nbsp;·&nbsp;
            <a href="https://support.strapi.io/articles/2817318284-ai-translation-credit-usage" target="_blank">AI Translation Credit Usage</a>
        </div>
    </div>
</div>

<div class="table-wrap">
    <div class="table-header">
        <h2>Per-page estimates</h2>
        <span style="font-size:12px;color:#9ca3af;">{{ number_format($totalCount) }} pages</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>URL</th>
                <th class="r" style="width:96px">Words</th>
                <th class="r" style="width:128px">Credits × 1</th>
                <th class="r" style="width:128px">Credits × 5</th>
                <th style="width:40px"></th>
            </tr>
        </thead>
        <tbody id="table-body">
