(() => {
    'use strict';
    const shell = document.querySelector('[data-owner-shell]');
    if (!shell) return;
    const sidebar = shell.querySelector('.oe-sidebar');
    const opener = shell.querySelector('[data-owner-open]');
    const closer = shell.querySelector('[data-owner-close]');
    const overlay = shell.querySelector('[data-owner-overlay]');
    const collapse = shell.querySelector('[data-owner-collapse]');
    const mobile = window.matchMedia('(max-width: 1024px)');
    const workspace = shell.querySelector('.oe-workspace');
    const storageKey = 'yza_owner_sidebar_collapsed';
    let collapsed = false;
    try { collapsed = localStorage.getItem(storageKey) === '1'; } catch (_) {}
    const setCollapsed = () => {
        shell.classList.toggle('oe-collapsed', !mobile.matches && collapsed);
        collapse?.setAttribute('aria-expanded', String(!collapsed));
        if (collapse) collapse.title = collapse.ariaLabel = collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar';
    };
    const setDrawer = (open, restoreFocus = false) => {
        shell.classList.toggle('oe-drawer-open', open);
        overlay.hidden = !open;
        opener?.setAttribute('aria-expanded', String(open));
        workspace.inert = open;
        sidebar.inert = mobile.matches && !open;
        if (open) closer?.focus();
        else if (restoreFocus) opener?.focus();
    };
    opener?.addEventListener('click', () => setDrawer(true));
    closer?.addEventListener('click', () => setDrawer(false, true));
    overlay?.addEventListener('click', () => setDrawer(false, true));
    collapse?.addEventListener('click', () => {
        collapsed = !collapsed;
        try { localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (_) {}
        setCollapsed();
    });
    mobile.addEventListener('change', () => { setDrawer(false); setCollapsed(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && shell.classList.contains('oe-drawer-open')) setDrawer(false, true);
        if (event.key === 'Tab' && shell.classList.contains('oe-drawer-open')) {
            const items = [...sidebar.querySelectorAll('a, button')].filter(item => item.getClientRects().length);
            const first = items[0], last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    setCollapsed();
    setDrawer(false);
    const fromInput = shell.querySelector('input[name="from"]');
    const toInput = shell.querySelector('input[name="to"]');
    fromInput?.addEventListener('change', () => { toInput.min = fromInput.value; });
    if (fromInput && toInput) toInput.min = fromInput.value;

    const ns = 'http://www.w3.org/2000/svg';
    const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
    const compact = value => new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
    const element = (tag, attrs, text) => {
        const node = document.createElementNS(ns, tag);
        for (const [key, value] of Object.entries(attrs)) node.setAttribute(key, String(value));
        if (text !== undefined) node.textContent = text;
        return node;
    };
    shell.querySelectorAll('[data-owner-chart]').forEach(container => {
        const data = JSON.parse(container.querySelector('script').textContent);
        const key = container.dataset.ownerChart;
        const byMonth = new Map(data.map(row => [row.period, row]));
        const start = fromInput?.value.slice(0, 7), end = toInput?.value.slice(0, 7);
        const months = [];
        if (start && end) {
            let [year, month] = start.split('-').map(Number);
            while (`${year}-${String(month).padStart(2, '0')}` <= end && months.length < 1200) {
                const period = `${year}-${String(month).padStart(2, '0')}`;
                months.push(byMonth.get(period) || { period, units: 0, amount: 0 });
                if (++month === 13) { month = 1; year++; }
            }
        }
        const rows = months.length ? months : data;
        const draw = () => {
            container.querySelector('svg')?.remove();
            container.querySelector('.oe-chart-tooltip')?.remove();
            const width = Math.max(240, container.clientWidth), height = container.clientHeight;
            const left = key === 'amount' ? 63 : 28, right = 12, top = 15, bottom = 32;
            const plotWidth = width - left - right, plotHeight = height - top - bottom;
            const maximum = Math.max(...rows.map(row => Number(row[key])), 1);
            const step = key === 'units' ? Math.max(1, Math.ceil(maximum / 3)) : Math.max(1, maximum / 3);
            const max = step * 3;
            const svg = element('svg', { viewBox: `0 0 ${width} ${height}`, role: 'img', 'aria-label': key === 'units' ? 'Unit penjualan final per bulan. Data tersedia pada tabel di bawah grafik.' : 'Nilai penjualan final per bulan dalam rupiah. Data tersedia pada tabel di bawah grafik.' });
            for (let tick = 0; tick <= 3; tick++) {
                const y = top + plotHeight - (tick / 3) * plotHeight;
                svg.append(element('line', { x1: left, x2: width - right, y1: y, y2: y, stroke: '#e9eef4', 'stroke-dasharray': tick ? '3 4' : '0' }));
                svg.append(element('text', { x: left - 9, y: y + 3, 'text-anchor': 'end', fill: '#96a1b1', 'font-size': 9 }, key === 'amount' ? compact(step * tick) : step * tick));
            }
            const tooltip = document.createElement('div');
            tooltip.className = 'oe-chart-tooltip';
            tooltip.hidden = true;
            const band = plotWidth / rows.length;
            rows.forEach((row, index) => {
                const x = left + index * band, value = Number(row[key]);
                const barHeight = (value / max) * plotHeight;
                const group = element('g', { tabindex: 0, role: 'img', 'aria-label': `${row.period}: ${key === 'amount' ? money(value) : `${value} unit`}` });
                group.append(element('rect', { x: x + band * .22, y: top + plotHeight - barHeight, width: Math.max(1, band * .56), height: barHeight, rx: Math.min(3, band * .1), fill: key === 'units' ? '#dc2948' : '#2a4567' }));
                group.append(element('rect', { x, y: top, width: band, height: plotHeight, fill: 'transparent' }));
                const title = `${row.period} · ${key === 'amount' ? money(value) : `${value} unit`}`;
                group.append(element('title', {}, title));
                const show = () => {
                    tooltip.textContent = title;
                    tooltip.hidden = false;
                    tooltip.style.left = `${Math.max(0, Math.min(x, width - tooltip.offsetWidth))}px`;
                    tooltip.style.top = '0px';
                };
                group.addEventListener('pointerenter', show);
                group.addEventListener('focus', show);
                group.addEventListener('pointerleave', () => { tooltip.hidden = true; });
                group.addEventListener('blur', () => { tooltip.hidden = true; });
                svg.append(group);
                const labelEvery = Math.max(1, Math.ceil(rows.length / Math.max(3, Math.floor(plotWidth / 50))));
                if (index % labelEvery === 0) {
                    const date = new Date(`${row.period}-01T12:00:00`);
                    svg.append(element('text', { x: x + band / 2, y: height - 10, 'text-anchor': 'middle', fill: '#8795a8', 'font-size': 9 }, date.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' })));
                }
            });
            container.append(svg, tooltip);
        };
        new ResizeObserver(draw).observe(container);
        draw();
    });
})();
