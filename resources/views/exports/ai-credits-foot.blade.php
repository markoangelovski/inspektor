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
    const totalRows = {{ $totalCount }};
    const totalPages = Math.ceil(totalRows / ROWS_PER_PAGE) || 1;
    let currentPage = 1;

    function renderPage(page) {
        currentPage = page;
        document.querySelectorAll('.page-group').forEach(function (el) {
            el.classList.toggle('active', parseInt(el.getAttribute('data-page'), 10) === page);
        });
        document.getElementById('page-info').textContent =
            'Page ' + page + ' of ' + totalPages + ' (' + totalRows + ' pages total)';

        const btns = document.getElementById('page-btns');
        btns.innerHTML = '';

        const prev = document.createElement('button');
        prev.className = 'page-btn';
        prev.textContent = '← Prev';
        prev.disabled = page === 1;
        prev.onclick = function () { renderPage(page - 1); };
        btns.appendChild(prev);

        buildRange(page, totalPages).forEach(function (p) {
            if (p === '…') {
                const el = document.createElement('span');
                el.style.cssText = 'padding:4px 6px;';
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
        if (total <= 7) { const r = []; for (let i = 1; i <= total; i++) r.push(i); return r; }
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
