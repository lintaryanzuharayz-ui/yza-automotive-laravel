(() => {
    'use strict';
    const panel = document.querySelector('[data-yza-notification-panel]');
    if (!panel) return;
    const triggers = [...document.querySelectorAll('[data-member-notification-toggle], [data-admin-notification-link], [data-mc-notification-toggle]')];
    if (!triggers.length) return;
    const list = panel.querySelector('[data-panel-list]'), count = panel.querySelector('[data-panel-count]');
    const lifecycle = new AbortController();
    let rows = [], unread = 0, total = 0, filter = 'all', open = false, activeTrigger = null, closeTimer = 0, request = null;
    function category(item) {
        const title = String(item.title).toLowerCase();
        if (/ditolak|gagal|dibatalkan|kritis|habis/.test(title)) return ['alert', '!'];
        if (/diterima|disetujui|terverifikasi|berhasil|selesai/.test(title)) return ['success', '✓'];
        if (/diperlukan|persetujuan/.test(title)) return ['tools', '⚒'];
        if (/dikerjakan|pemeriksaan|stok|verifikasi/.test(title)) return ['progress', '⚙'];
        return ['info', '♧'];
    }
    function render() {
        count.textContent = `${unread} belum dibaca`;
        panel.querySelector('[data-panel-total]').textContent = `(${total})`;
        const fragment = document.createDocumentFragment();
        const visible = rows.filter(item => filter !== 'unread' || !item.is_read).slice(0, 4);
        for (const item of visible) {
            let url;
            try { url = new URL(item.open_url, location.origin); } catch (_) { continue; }
            if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)) continue;
            const [type, symbol] = category(item);
            const card = document.createElement('a'); card.className = `ynp-item${item.is_read ? '' : ' is-unread'}`; card.href = url.href; card.dataset.notificationId = item.id;
            const icon = document.createElement('span'); icon.className = `ynp-icon ynp-icon--${type}`; icon.textContent = symbol; icon.setAttribute('aria-hidden', 'true');
            const copy = document.createElement('div'); copy.className = 'ynp-copy';
            const title = document.createElement('strong'); title.textContent = item.title;
            const message = document.createElement('p'); message.textContent = item.message; message.title = item.message;
            const time = document.createElement('time');
            const date = new Date(item.created_at);
            if (!Number.isNaN(date.getTime())) { time.dateTime = item.created_at; time.textContent = date.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }
            copy.append(title, message, time);
            const arrow = document.createElement('span'); arrow.className = 'ynp-chevron'; arrow.textContent = '›'; arrow.setAttribute('aria-hidden', 'true');
            card.append(icon, copy, arrow); fragment.append(card);
        }
        if (!fragment.childNodes.length) {
            const empty = document.createElement('p'); empty.className = 'ynp-empty'; empty.textContent = filter === 'unread' ? 'Semua notifikasi sudah dibaca.' : 'Belum ada notifikasi.'; fragment.append(empty);
        }
        // Do not replace an item while someone is navigating its link with the keyboard.
        if (!list.contains(document.activeElement)) list.replaceChildren(fragment);
    }
    function updateBadge(value) {
        unread = Math.max(0, Number(value) || 0);
    }
    function sync(data, replace = false) {
        updateBadge(data.unread_count);
        if (data.total_count != null) total = Math.max(0, Number(data.total_count) || 0);
        const items = filter === 'unread' ? (data.unread_recent_notifications || data.notifications) : (data.recent_notifications || data.notifications);
        const incoming = Array.isArray(items) ? items : [];
        rows = replace ? incoming : [...new Map([...rows, ...incoming].map(item => [Number(item.id), item])).values()];
        rows.sort((a, b) => Number(b.id) - Number(a.id)); rows = rows.slice(0, 20);
        render();
    }
    async function refresh() {
        request?.abort(); request = new AbortController();
        const current = request;
        try {
            const url = new URL(panel.dataset.syncUrl, location.origin);
            url.searchParams.set('panel', '1'); url.searchParams.set('filter', filter);
            const response = await fetch(url, { signal: current.signal, credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if ([401, 403, 419].includes(response.status)) { close(); return; }
            if (!response.ok) return;
            const data = await response.json();
            if (!current.signal.aborted) { if (window.YzaNotificationState) window.YzaNotificationState.apply(data); else sync(data, true); }
        } catch (_) { /* Keep the last real data on temporary connection failure. */ }
    }
    function position() {
        if (!open) return;
        const navbar = activeTrigger?.closest('header, .mc-topbar, .yza-navbar');
        const edge = (navbar || activeTrigger)?.getBoundingClientRect().bottom || 68;
        panel.style.setProperty('--ynp-top', `${Math.max(8, Math.min(edge + 8, window.innerHeight - 180))}px`);
    }
    function close(restoreFocus = false) {
        if (!open) return;
        open = false; request?.abort(); activeTrigger?.setAttribute('aria-expanded', 'false');
        if (restoreFocus || panel.contains(document.activeElement)) activeTrigger?.focus();
        panel.inert = true; panel.classList.add('is-closing');
        closeTimer = setTimeout(() => {
            panel.hidden = true; panel.classList.remove('is-closing');
            document.documentElement.removeAttribute('data-notification-panel-open');
            document.dispatchEvent(new Event('yza:notification-panel'));
        }, matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 160);
    }
    function show(trigger) {
        clearTimeout(closeTimer); activeTrigger = trigger;
        document.querySelectorAll('[data-member-profile-menu], [data-admin-dropdown], [data-mc-notification-popover]').forEach(menu => { menu.hidden = true; });
        document.querySelectorAll('[data-member-profile-toggle], [data-admin-user-toggle]').forEach(button => button.setAttribute('aria-expanded', 'false'));
        open = true; panel.hidden = false; panel.inert = false; panel.classList.remove('is-closing');
        trigger.setAttribute('aria-expanded', 'true'); document.documentElement.setAttribute('data-notification-panel-open', '');
        position(); refresh();
    }
    triggers.forEach((trigger, index) => {
        trigger.setAttribute('aria-controls', panel.id); trigger.setAttribute('aria-expanded', 'false'); trigger.setAttribute('aria-haspopup', 'true');
        if (!trigger.id) trigger.id = `yza-notification-trigger-${index}`;
        trigger.addEventListener('click', event => {
            event.preventDefault(); event.stopImmediatePropagation();
            if (open) close(); else show(trigger);
        }, { capture: true, signal: lifecycle.signal });
        trigger.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown' || (trigger.tagName === 'A' && event.key === ' ')) {
                event.preventDefault(); if (!open) show(trigger); panel.querySelector('[data-panel-filter]')?.focus();
            }
        }, { signal: lifecycle.signal });
    });
    panel.querySelectorAll('[data-panel-filter]').forEach(button => button.addEventListener('click', () => {
        filter = button.dataset.panelFilter;
        panel.querySelectorAll('[data-panel-filter]').forEach(item => { const active = item === button; item.classList.toggle('is-active', active); item.setAttribute('aria-pressed', String(active)); });
        render(); refresh();
    }, { signal: lifecycle.signal }));
    document.addEventListener('click', event => { if (open && !panel.contains(event.target)) close(); }, { signal: lifecycle.signal });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(true); }, { signal: lifecycle.signal });
    window.addEventListener('resize', position, { signal: lifecycle.signal });
    window.addEventListener('scroll', position, { passive: true, signal: lifecycle.signal });
    window.addEventListener('pagehide', () => { request?.abort(); clearTimeout(closeTimer); panel.hidden = true; open = false; document.documentElement.removeAttribute('data-notification-panel-open'); });
    function clear() { request?.abort(); close(); rows = []; unread = 0; total = 0; render(); }
    window.YzaNotificationPanel = Object.freeze({ sync, close, clear });
    try { sync(JSON.parse(panel.querySelector('[data-panel-initial]').textContent), true); } catch (_) { render(); }
})();
