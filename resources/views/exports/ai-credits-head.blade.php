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
        .page-group.active { display: contents; }
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
<div class="footnote">
    Estimates use: 1 language → 0.0711 + 0.002098&times;words; 5 languages → 0.1265 + 0.003725&times;words.
    Strapi Growth plan includes 1,000 credits/month ($45/mo); overages at $1.50 per 100 credits.
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
