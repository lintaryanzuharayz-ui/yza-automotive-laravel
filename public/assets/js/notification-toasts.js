(() => {
    'use strict';
    const stack = document.querySelector('[data-yza-toast-stack]');
    if (!stack || window.YzaToast) return;
    const icons = {
        crown: '<path d="m3 6 5 4 4-7 4 7 5-4-2 14H5Z"/>',
        user: '<circle cx="12" cy="7" r="4"/><path d="M5 21v-3a7 7 0 0 1 14 0v3"/>',
        calendar: '<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v5m8-5v5M4 10h16m-12 5 3 3 5-5"/>',
        success: '<circle cx="12" cy="12" r="9"/><path d="m7 12 3 3 7-7"/>',
        order: '<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v5m8-5v5M4 10h16m-8 3v5m-3-2 3 2 3-2"/>',
        warning: '<path d="m12 3 10 18H2Z"/><path d="M12 9v5m0 3v1"/>',
    };
    const queue = [], active = new Set(), seen = new Set();
    const lifecycle = new AbortController();
    let frame = 0, disposed = false;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    function safeLink(value) {
        if (!value) return null;
        try { const url = new URL(value, location.origin); return url.origin === location.origin && /^https?:$/.test(url.protocol) ? url.href : null; }
        catch (_) { return null; }
    }
    function classify(item) {
        if (item.welcome) return { type: item.member ? 'member' : 'info', icon: item.member ? 'crown' : 'user' };
        const title = String(item.title || '').toLowerCase();
        // Only explicit successful states receive the green checkmark.
        if (/ditolak|gagal|kritis|dibatalkan/.test(title)) return { type: 'warning', icon: 'warning' };
        if (/terverifikasi|berhasil|selesai|disetujui/.test(title)) return { type: 'success', icon: 'success' };
        if (/pesanan.*(baru|masuk)/.test(title)) return { type: 'order', icon: 'order' };
        if (/booking|jadwal/.test(title)) return { type: 'booking', icon: 'calendar' };
        if (/verifikasi|menunggu|pending/.test(title)) return { type: 'warning', icon: 'warning' };
        return { type: 'info', icon: 'user' };
    }
    function svg(markup) {
        const element = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        element.setAttribute('viewBox', '0 0 24 24');
        element.setAttribute('aria-hidden', 'true');
        element.innerHTML = markup; // Only fixed icon markup above; payload text uses textContent.
        return element;
    }
    function pump() {
        while (!disposed && !document.documentElement.hasAttribute('data-notification-panel-open') && active.size < 2 && queue.length) mount(queue.shift());
    }
    function mount(item) {
        const appearance = classify(item);
        const toast = document.createElement('article');
        toast.className = `yza-toast yza-toast--${appearance.type}`;
        const icon = document.createElement('span'); icon.className = 'yza-toast-icon'; icon.append(svg(icons[appearance.icon]));
        const copy = document.createElement('div'); copy.className = 'yza-toast-copy';
        const title = document.createElement('strong'); title.className = 'yza-toast-title'; title.textContent = item.title;
        const body = document.createElement('div'); body.className = 'yza-toast-body';
        const message = document.createElement('span'); message.className = 'yza-toast-message'; message.textContent = item.message;
        body.append(message);
        const target = safeLink(item.open_url || item.url);
        if (target) {
            const link = document.createElement('a'); link.className = 'yza-toast-link'; link.href = target;
            link.append(document.createTextNode('Lihat detail '));
            const arrow = document.createElement('span'); arrow.textContent = '→'; arrow.setAttribute('aria-hidden', 'true'); link.append(arrow);
            link.setAttribute('aria-label', `Lihat detail: ${item.title}`); body.append(link);
        }
        copy.append(title, body);
        const close = document.createElement('button'); close.type = 'button'; close.className = 'yza-toast-close'; close.setAttribute('aria-label', 'Tutup notifikasi'); close.append(svg('<path d="m5 5 14 14M19 5 5 19"/>'));
        const track = document.createElement('span'); track.className = 'yza-toast-timer'; track.setAttribute('aria-hidden', 'true');
        const progress = document.createElement('span'); progress.className = 'yza-toast-progress'; track.append(progress);
        const seconds = document.createElement('span'); seconds.className = 'yza-toast-seconds'; seconds.setAttribute('aria-hidden', 'true');
        const duration = item.welcome ? 4000 : 7000;
        seconds.textContent = `${duration / 1000}s`;
        toast.append(icon, copy, close, track, seconds);
        const controller = new AbortController();
        const state = { toast, progress, seconds, remaining: duration, duration, last: performance.now(), hover: false, focus: false, closing: false, exit: 0, controller };
        active.add(state); stack.append(toast);
        close.addEventListener('click', () => dismiss(state), { signal: controller.signal });
        toast.addEventListener('mouseenter', () => { state.hover = true; }, { signal: controller.signal });
        toast.addEventListener('mouseleave', () => { state.hover = false; state.last = performance.now(); }, { signal: controller.signal });
        toast.addEventListener('focusin', () => { state.focus = true; }, { signal: controller.signal });
        toast.addEventListener('focusout', event => { state.focus = toast.contains(event.relatedTarget); state.last = performance.now(); }, { signal: controller.signal });
        if (!frame && !document.hidden) frame = requestAnimationFrame(tick);
    }
    function dismiss(state) {
        if (state.closing) return;
        state.closing = true; state.controller.abort(); state.toast.classList.add('is-closing');
        state.exit = setTimeout(() => { state.toast.remove(); active.delete(state); pump(); }, reducedMotion.matches ? 0 : 240);
    }
    function tick(now) {
        frame = 0;
        for (const state of active) {
            const elapsed = now - state.last; state.last = now;
            if (state.closing || state.hover || state.focus || document.documentElement.hasAttribute('data-notification-panel-open')) continue;
            state.remaining = Math.max(0, state.remaining - elapsed);
            state.progress.style.transform = `scaleX(${state.remaining / state.duration})`;
            state.seconds.textContent = `${Math.ceil(state.remaining / 1000)}s`;
            if (!state.remaining) dismiss(state);
        }
        if (!disposed && active.size && !document.hidden) frame = requestAnimationFrame(tick);
    }
    function show(item) {
        if (disposed || !item || !item.title) return;
        if (item.id != null) {
            const key = `${stack.dataset.account}:${item.id}`;
            if (seen.has(key)) return;
            seen.add(key);
        }
        queue.push(item); pump();
    }
    function clear() {
        cancelAnimationFrame(frame); frame = 0; queue.length = 0; seen.clear();
        for (const state of active) { clearTimeout(state.exit); state.controller.abort(); state.toast.remove(); }
        active.clear();
    }
    window.YzaToast = Object.freeze({ show, clear });
    document.addEventListener('yza:notification-panel', pump, { signal: lifecycle.signal });
    document.addEventListener('visibilitychange', () => {
        cancelAnimationFrame(frame); frame = 0;
        for (const state of active) state.last = performance.now();
        if (!document.hidden && active.size) frame = requestAnimationFrame(tick);
    }, { signal: lifecycle.signal });
    window.addEventListener('pagehide', event => {
        disposed = true; cancelAnimationFrame(frame); frame = 0; if (!event.persisted) lifecycle.abort(); queue.length = 0;
        for (const state of active) { clearTimeout(state.exit); state.controller.abort(); state.toast.remove(); }
        active.clear();
    });
    // A restored page can receive notifications again, without replaying its welcome.
    window.addEventListener('pageshow', event => { if (event.persisted) disposed = false; });
    if (stack.dataset.welcome === 'true') show({ welcome: true, member: stack.dataset.member === 'true', title: stack.dataset.title, message: stack.dataset.message });
    // Existing member flash messages use the same component, rather than a second visual stack.
    document.querySelectorAll('[data-member-toast]').forEach(toast => {
        show({ title: toast.querySelector('strong')?.textContent, message: toast.querySelector('.member-toast-copy span')?.textContent || '' });
        toast.remove();
    });
})();
