(() => {
    'use strict';
    const host = document.querySelector('[data-notification-state]');
    if (!host) return;
    let initial;
    try { initial = JSON.parse(host.textContent); } catch (_) { return; }
    const account = Number(initial.account_id), lifecycle = new AbortController();
    let cursor = Number(initial.latest_id) || 0, timer = 0, request = null, stopped = false, delay = 10000, latestSnapshot = 0, mutating = false;
    const channel = typeof BroadcastChannel === 'function' ? new BroadcastChannel(`yza-notifications-${account}`) : null;
    const compact = count => count > 99 ? '99+' : String(count);
    const center = document.querySelector('[data-admin-notification-list], [data-member-notification-center]');
    const unreadOnly = new URL(location.href).searchParams.get('filter') === 'unread';
    const firstPage = Number(new URL(location.href).searchParams.get('page') || 1) === 1;
    function apply(data) {
        if (stopped) return;
        if (data.account_id != null && Number(data.account_id) !== account) { stop(); clearAccount(); return; }
        if (data.snapshot_started_at && Number(data.snapshot_started_at) < latestSnapshot) return;
        latestSnapshot = Math.max(latestSnapshot, Number(data.snapshot_started_at) || 0);
        const unread = Math.max(0, Number(data.unread_count) || 0);
        document.querySelectorAll('[data-member-notification-count], [data-admin-notification-count], [data-mc-notification-count], [data-notification-global-count]').forEach(badge => {
            badge.textContent = compact(unread); badge.hidden = unread < 1;
            badge.setAttribute('aria-label', `${unread} notifikasi belum dibaca`);
        });
        document.querySelectorAll('[data-notification-category], [data-notification-menu]').forEach(dot => {
            const value = Number(dot.dataset.notificationCategory ? data.category_unread?.[dot.dataset.notificationCategory] : data.menu_unread?.[dot.dataset.notificationMenu]) || 0;
            dot.hidden = value < 1; dot.setAttribute('aria-label', `${value} notifikasi belum dibaca`); dot.title = `${value} notifikasi belum dibaca`;
        });
        if (data.pending_counts) document.querySelectorAll('[data-notification-pending]').forEach(badge => {
            const value = Number(data.pending_counts[badge.dataset.notificationPending]) || 0;
            badge.textContent = compact(value); badge.hidden = value < 1;
            badge.title = `${value} ${badge.dataset.countLabel || 'pekerjaan tertunda'}`; badge.setAttribute('aria-label', badge.title);
        });
        document.querySelectorAll('[data-notification-unread-total]').forEach(label => { label.textContent = String(unread); });
        document.querySelectorAll('[data-notification-unread-badge]').forEach(label => { label.textContent = compact(unread); label.hidden = unread < 1; });
        const markAll = document.querySelector('.notification-mark-all-form');
        if (markAll) { markAll.hidden = unread < 1; const button = markAll.querySelector('button'); if (button) button.disabled = false; }
        if (data.total_count != null) document.querySelectorAll('[data-notification-total]').forEach(label => { label.textContent = String(data.total_count); });
        if (data.recent_notifications || data.notifications) window.YzaNotificationPanel?.sync(data, true);
        for (const [id, read] of Object.entries(data.visible_read_state || {})) {
            center?.querySelectorAll(`[data-notification-id="${Number(id)}"]`).forEach(card => {
                card.classList.toggle('is-unread', !read); card.classList.toggle('is-read', !!read);
                if (read) { card.querySelector('[data-notification-read-form]')?.remove(); card.querySelector('.admin-notification-new')?.remove(); card.querySelector('.notification-unread-dot')?.remove(); }
                if (read && unreadOnly) card.remove();
            });
        }
        // If assignment was revoked, remove inaccessible cards without exposing their read state.
        if (data.visible_read_state) center?.querySelectorAll('[data-notification-id]').forEach(card => {
            if (!(card.dataset.notificationId in data.visible_read_state)) card.remove();
        });
        document.dispatchEvent(new CustomEvent('yza:notification-state', { detail: data }));
    }
    function addCard(item) {
        if (!center || !firstPage || (unreadOnly && item.is_read) || center.querySelector(`[data-notification-id="${Number(item.id)}"]`)) return;
        const admin = center.hasAttribute('data-admin-notification-list');
        const card = document.createElement('article'); card.dataset.notificationId = Number(item.id);
        card.className = `${admin ? 'admin-notification-card' : 'notification-item'}${item.is_read ? ' is-read' : ' is-unread'}`;
        const icon = document.createElement('span'); icon.className = admin ? 'admin-notification-card-icon' : 'notification-item-icon'; icon.textContent = '•'; icon.setAttribute('aria-hidden', 'true');
        const copy = document.createElement('div'); copy.className = admin ? 'admin-notification-card-content' : 'notification-item-copy';
        const title = document.createElement('strong'); title.textContent = item.title;
        const message = document.createElement('p'); message.textContent = item.message;
        const time = document.createElement('time'); const date = new Date(item.created_at); if (!Number.isNaN(date.getTime())) time.textContent = date.toLocaleString('id-ID');
        const link = document.createElement('a');
        try { const url = new URL(item.open_url, location.origin); if (url.origin !== location.origin) return; link.href = url.href; } catch (_) { return; }
        link.className = admin ? 'admin-notification-card-link' : 'notification-item-content'; link.textContent = 'Buka detail →';
        copy.append(title, message, time, link); card.append(icon, copy);
        center.querySelector('.admin-notification-empty, .notification-empty')?.remove(); center.prepend(card);
    }
    function claimToast(id) {
        try {
            const key = `yza-notification-toasts:${account}`;
            const ids = JSON.parse(localStorage.getItem(key) || '[]');
            if (ids.includes(Number(id))) return false;
            localStorage.setItem(key, JSON.stringify([...ids, Number(id)].slice(-100))); return true;
        } catch (_) { return true; }
    }
    function schedule(ms = delay) { clearTimeout(timer); if (!stopped && !mutating && !document.hidden) timer = setTimeout(sync, ms); }
    async function sync() {
        if (stopped || mutating || document.hidden || request) return;
        const controller = new AbortController(); request = controller;
        try {
            const url = new URL(initial.sync_url, location.origin); url.searchParams.set('after_id', cursor); url.searchParams.set('incremental', '1');
            center?.querySelectorAll('[data-notification-id]').forEach(card => url.searchParams.append('visible_ids[]', card.dataset.notificationId));
            const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if ([401, 403, 419].includes(response.status)) { stop(); clearAccount(); return; }
            if (!response.ok) throw new Error('sync');
            const data = await response.json();
            if (controller.signal.aborted || stopped) return;
            if (Number(data.account_id) !== account) { stop(); clearAccount(); return; }
            apply(data);
            for (const item of data.notifications || []) {
                if (Number(item.id) <= cursor) continue;
                addCard(item);
                if (!item.is_read && claimToast(item.id)) window.YzaToast?.show(item);
            }
            cursor = Math.max(cursor, Number(data.cursor) || 0);
            delay = 10000;
            if (cursor < Number(data.latest_id)) delay = 300;
        } catch (_) { delay = Math.min(delay + 5000, 30000); }
        finally { if (request === controller) request = null; schedule(); }
    }
    function stop() { stopped = true; clearTimeout(timer); request?.abort(); request = null; }
    function clearAccount() {
        window.YzaToast?.clear(); window.YzaNotificationPanel?.clear();
        document.querySelectorAll('[data-member-notification-count], [data-admin-notification-count], [data-mc-notification-count], [data-notification-global-count], [data-notification-category], [data-notification-menu]').forEach(item => { item.hidden = true; });
        center?.replaceChildren();
    }
    document.addEventListener('submit', async event => {
        const form = event.target;
        if (form.matches('form[action$="/logout"]')) { stop(); clearAccount(); channel?.postMessage('logout'); return; }
        if (!form.matches('[data-notification-read-form]')) return;
        event.preventDefault();
        if (mutating) return;
        mutating = true; request?.abort(); request = null; clearTimeout(timer);
        const button = form.querySelector('button[type=submit], button'); if (button) button.disabled = true;
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('read');
            apply(await response.json()); channel?.postMessage('changed');
        } catch (_) { if (button) button.disabled = false; }
        finally { mutating = false; schedule(0); }
    }, { signal: lifecycle.signal });
    document.addEventListener('visibilitychange', () => { if (document.hidden) clearTimeout(timer); else schedule(0); }, { signal: lifecycle.signal });
    channel?.addEventListener('message', event => { if (event.data === 'logout') { stop(); clearAccount(); } else schedule(0); });
    window.addEventListener('pagehide', () => { stop(); });
    window.addEventListener('pageshow', event => { if (event.persisted) { stopped = false; schedule(0); } });
    window.YzaNotificationState = Object.freeze({ apply, refresh: () => schedule(0) });
    apply(initial); schedule(0);
})();
