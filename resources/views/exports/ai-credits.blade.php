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
        .meta a { color: #3b82f6; text-decoration: none; }

        /* Summary cards */
        .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 8px; }
        @media (max-width: 700px) { .cards { grid-template-columns: 1fr 1fr; } }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .card-label { font-size: 11px; color: #6b7280; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
        .card-value { font-size: 22px; font-weight: 700; color: #111; }
        .card-note { font-size: 11px; margin-top: 4px; color: #9ca3af; }
        .card-note.warn { color: #d97706; }
        .card-calc { font-size: 11px; margin-top: 6px; color: #6b7280; line-height: 1.6; }

        /* Footnote */
        .footnote { font-size: 11px; color: #9ca3af; margin-bottom: 20px; }

        /* Table */
        .table-wrap {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .table-header {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
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
        tbody tr.main-row { cursor: pointer; }
        tbody tr.main-row:hover td { background: #f9fafb; }
        tbody tr.main-row td {
            padding: 10px 14px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: middle;
        }
        td.r { text-align: right; font-variant-numeric: tabular-nums; }
        td.url { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #9ca3af;
            font-size: 16px;
            line-height: 1;
            transition: color .15s;
        }
        .toggle-btn:hover { color: #374151; }
        .toggle-btn.open { transform: rotate(180deg); }

        /* Expanded content row */
        tr.content-row { display: none; }
        tr.content-row.visible { display: table-row; }
        tr.content-row td {
            padding: 12px 20px 16px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .segments { display: flex; flex-direction: column; gap: 8px; }
        .segment { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; }
        .segment-cols {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            flex: 1;
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .badge-title      { background: #dbeafe; color: #1d4ed8; }
        .badge-description{ background: #f3e8ff; color: #7e22ce; }
        .badge-heading    { background: #fef3c7; color: #92400e; }
        .badge-paragraph  { background: #d1fae5; color: #065f46; }
        .badge-list-item  { background: #ccfbf1; color: #0f766e; }
        .badge-cell       { background: #ffedd5; color: #9a3412; }
        .badge-alt-text   { background: #fce7f3; color: #9d174d; }
        .badge-default    { background: #f3f4f6; color: #374151; }
        .seg-text { color: #374151; line-height: 1.5; flex: 1; }
        .seg-stats { display: flex; gap: 16px; flex-shrink: 0; font-size: 11px; color: #9ca3af; white-space: nowrap; }
        .seg-stats span { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; }
        .seg-stats strong { color: #374151; font-size: 12px; }

        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }
        .page-btns { display: flex; gap: 4px; }
        .page-btn {
            padding: 4px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            font-size: 13px;
            color: #374151;
        }
        .page-btn:hover { background: #f3f4f6; }
        .page-btn.active { background: #2563eb; border-color: #2563eb; color: #fff; }
        .page-btn:disabled { opacity: .4; cursor: default; }

        /* Hide all page groups by default (JS controls visibility) */
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

{{-- Summary cards --}}
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
                {{ number_format($billable1, 1) }} ÷ 100 × $1.50 = <strong>${{ number_format($overage1, 2) }}</strong>
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
                {{ number_format($billable5, 1) }} ÷ 100 × $1.50 = <strong>${{ number_format($overage5, 2) }}</strong>
            </div>
        @else
            <div class="card-note">within 1,000 credit plan</div>
        @endif
    </div>
</div>
<div class="footnote">
    Estimates: ~0.01 credits/word (1 language), ~0.028 credits/word (5 languages).
    Strapi Growth plan includes 1,000 credits/month ($45/mo); overages at $1.50 per 100 credits.
</div>

{{-- Per-page table --}}
<div class="table-wrap">
    <div class="table-header">
        <h2>Per-page estimates</h2>
        <span style="font-size:12px;color:#9ca3af;">{{ $allCredits->count() }} pages</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>URL</th>
                <th class="r" style="width:80px">Words</th>
                <th class="r" style="width:110px">Credits × 1</th>
                <th class="r" style="width:110px">Credits × 5</th>
                <th style="width:40px"></th>
            </tr>
        </thead>
        <tbody id="table-body">
            @foreach ($allCredits as $i => $row)
                @php $pageNum = intdiv($i, 25) + 1; @endphp
                <tr class="page-group main-row" data-page="{{ $pageNum }}" onclick="toggleRow({{ $i }})">
                    <td class="url" title="{{ $row->url }}">{{ $row->url }}</td>
                    <td class="r">{{ number_format($row->word_count) }}</td>
                    <td class="r">{{ number_format($row->credits_one_language, 2) }}</td>
                    <td class="r">{{ number_format($row->credits_five_languages, 2) }}</td>
                    <td style="text-align:center">
                        <button class="toggle-btn" id="btn-{{ $i }}" type="button">&#8964;</button>
                    </td>
                </tr>
                <tr class="page-group content-row" id="content-{{ $i }}" data-page="{{ $pageNum }}">
                    <td colspan="5">
                        @if (count($row->translatable_content ?? []) > 0)
                            <div class="segments">
                                @foreach ($row->translatable_content as $seg)
                                    @php
                                        $segWc = $seg['word_count'] ?? null;
                                        $segC1 = isset($seg['credits_one']) ? $seg['credits_one'] : ($segWc !== null ? round($segWc * 0.01, 4) : null);
                                        $segC5 = isset($seg['credits_five']) ? $seg['credits_five'] : ($segWc !== null ? round($segWc * 0.028, 4) : null);
                                        $badgeClass = match($seg['type']) {
                                            'title' => 'badge-title',
                                            'description' => 'badge-description',
                                            'heading' => 'badge-heading',
                                            'paragraph' => 'badge-paragraph',
                                            'list-item' => 'badge-list-item',
                                            'cell' => 'badge-cell',
                                            'alt-text' => 'badge-alt-text',
                                            default => 'badge-default',
                                        };
                                    @endphp
                                    <div class="segment">
                                        <div class="segment-cols">
                                            <span class="badge {{ $badgeClass }}">{{ $seg['type'] }}</span>
                                            <span class="seg-text">{{ $seg['text'] }}</span>
                                        </div>
                                        @if ($segWc !== null)
                                            <div class="seg-stats">
                                                <span><strong>{{ number_format($segWc) }}</strong>words</span>
                                                <span><strong>{{ number_format($segC1, 2) }}</strong>cr×1</span>
                                                <span><strong>{{ number_format($segC5, 2) }}</strong>cr×5</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span style="color:#9ca3af;font-size:13px">No translatable content extracted.</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination" id="pagination">
        <span id="page-info"></span>
        <div class="page-btns" id="page-btns"></div>
    </div>
</div>

<script>
(function () {
    const ROWS_PER_PAGE = 25;
    const totalRows = {{ $allCredits->count() }};
    const totalPages = Math.ceil(totalRows / ROWS_PER_PAGE) || 1;
    let currentPage = 1;

    function renderPage(page) {
        currentPage = page;

        document.querySelectorAll('.page-group').forEach(function (el) {
            const elPage = parseInt(el.getAttribute('data-page'), 10);
            el.classList.toggle('active', elPage === page);
        });

        document.getElementById('page-info').textContent =
            'Page ' + page + ' of ' + totalPages +
            ' (' + totalRows + ' pages total)';

        const btns = document.getElementById('page-btns');
        btns.innerHTML = '';

        const prev = document.createElement('button');
        prev.className = 'page-btn';
        prev.textContent = '← Prev';
        prev.disabled = page === 1;
        prev.onclick = function () { renderPage(page - 1); };
        btns.appendChild(prev);

        const range = buildRange(page, totalPages);
        range.forEach(function (p) {
            if (p === '…') {
                const el = document.createElement('span');
                el.style.padding = '4px 6px';
                el.textContent = '…';
                btns.appendChild(el);
            } else {
                const btn = document.createElement('button');
                btn.className = 'page-btn' + (p === page ? ' active' : '');
                btn.textContent = p;
                btn.onclick = (function (n) { return function () { renderPage(n); }; })(p);
                btns.appendChild(btn);
            }
        });

        const next = document.createElement('button');
        next.className = 'page-btn';
        next.textContent = 'Next →';
        next.disabled = page === totalPages;
        next.onclick = function () { renderPage(page + 1); };
        btns.appendChild(next);
    }

    function buildRange(current, total) {
        if (total <= 7) {
            const r = [];
            for (let i = 1; i <= total; i++) r.push(i);
            return r;
        }
        const pages = [1];
        if (current > 3) pages.push('…');
        for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i);
        if (current < total - 2) pages.push('…');
        pages.push(total);
        return pages;
    }

    function toggleRow(i) {
        const content = document.getElementById('content-' + i);
        const btn = document.getElementById('btn-' + i);
        const visible = content.classList.toggle('visible');
        btn.classList.toggle('open', visible);
    }

    window.toggleRow = toggleRow;
    renderPage(1);
})();
</script>
</body>
</html>
