const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('public/assets/js/notification-sync.js', 'utf8');

function harness() {
    const badge = { dataset: {}, hidden: true, setAttribute(k, v) { this[k] = v; } };
    const dot = { dataset: { notificationCategory: 'orders' }, hidden: true, setAttribute(k, v) { this[k] = v; } };
    const unrelated = { dataset: { notificationCategory: 'benefits' }, hidden: true, setAttribute(k, v) { this[k] = v; } };
    const pending = { dataset: { notificationPending: 'orders', countLabel: 'pesanan menunggu verifikasi' }, setAttribute(k, v) { this[k] = v; } };
    const timers = new Map(), listeners = new Map(), windowListeners = new Map(), storage = new Map();
    const requests = [], responses = [], toasts = [], messages = [];
    let timerId = 0, cleared = 0;
    const initial = { account_id: 7, latest_id: 100, snapshot_started_at: 1, sync_url: 'http://localhost/notifikasi/sync', unread_count: 2, category_unread: { orders: 2 }, pending_counts: { orders: 5 } };
    const document = {
        hidden: false,
        querySelector(selector) { return selector === '[data-notification-state]' ? { textContent: JSON.stringify(initial) } : null; },
        querySelectorAll(selector) {
            if (selector.includes('[data-notification-pending]')) return [pending];
            if (selector.includes('[data-notification-category]') && !selector.includes('[data-member-notification-count]')) return [dot, unrelated];
            if (selector.includes('[data-member-notification-count]')) return selector.includes('[data-notification-category]') ? [badge, dot, unrelated] : [badge];
            return [];
        },
        addEventListener(type, fn) { listeners.set(type, fn); },
        dispatchEvent() {},
    };
    const window = { addEventListener(type, fn) { windowListeners.set(type, fn); },
        YzaToast: { show(item) { toasts.push(item); }, clear() { cleared++; } },
        YzaNotificationPanel: { sync() {}, clear() { cleared++; } },
    };
    const context = {
        document, window, location: { href: 'http://localhost/pelanggan/member', origin: 'http://localhost' }, URL, AbortController,
        CustomEvent: class { constructor(type, options) { this.type = type; this.detail = options.detail; } },
        FormData: class {},
        BroadcastChannel: class { constructor() {} addEventListener() {} postMessage(value) { messages.push(value); } },
        localStorage: { getItem(k) { return storage.get(k) ?? null; }, setItem(k, v) { storage.set(k, v); } },
        setTimeout(fn, delay) { timers.set(++timerId, { fn, delay }); return timerId; }, clearTimeout(id) { timers.delete(id); },
        fetch: async (url, options) => {
            requests.push({ url: String(url), options });
            const next = responses.shift() || { status: 200, data: { ...initial, notifications: [], cursor: 100, latest_id: 100 } };
            return { status: next.status, ok: next.status === 200, async json() { return next.data; } };
        },
    };
    vm.runInNewContext(source, context);
    return { badge, dot, unrelated, pending, timers, requests, responses, toasts, messages, document, window, initial,
        get cleared() { return cleared; },
        async tick() { const first = timers.entries().next().value; if (first) { timers.delete(first[0]); await first[1].fn(); } },
        async submit(form) { await listeners.get('submit')({ target: form, preventDefault() {} }); },
        visibility(hidden) { document.hidden = hidden; listeners.get('visibilitychange')(); },
    };
}

test('unread, category dots and pending tasks use separate authoritative values', () => {
    const h = harness();
    assert.equal(h.badge.textContent, '2'); assert.equal(h.pending.textContent, '5');
    assert.equal(h.dot.hidden, false); assert.equal(h.unrelated.hidden, true);
    h.window.YzaNotificationState.apply({ ...h.initial, snapshot_started_at: 2, unread_count: 0, category_unread: { orders: 0 }, pending_counts: { orders: 5 } });
    assert.equal(h.badge.hidden, true); assert.equal(h.dot.hidden, true); assert.equal(h.pending.textContent, '5');
});

test('reading one of several messages retains the dot, and stale snapshots cannot restore old counts', () => {
    const h = harness();
    h.window.YzaNotificationState.apply({ ...h.initial, snapshot_started_at: 3, unread_count: 1, category_unread: { orders: 1 } });
    h.window.YzaNotificationState.apply({ ...h.initial, snapshot_started_at: 2 });
    assert.equal(h.badge.textContent, '1'); assert.equal(h.dot.hidden, false);
});

test('polling has one timer, drains the server cursor and never repeats a toast', async () => {
    const h = harness();
    const state = { ...h.initial, snapshot_started_at: 2, notifications: [{ id: 101, title: 'Pesanan diperbarui', is_read: false }], cursor: 101, latest_id: 102 };
    h.responses.push({ status: 200, data: state }); await h.tick();
    assert.equal(h.toasts.length, 1); assert.equal(h.timers.size, 1);
    assert.equal([...h.timers.values()][0].delay, 300);
    h.responses.push({ status: 200, data: { ...state, snapshot_started_at: 3, cursor: 102 } }); await h.tick();
    assert.equal(h.toasts.length, 1);
    assert.match(h.requests[1].url, /after_id=101/);
    assert.equal(h.requests[0].options.cache, 'no-store');
});

test('visibility pauses polling and authentication loss stops further requests', async () => {
    const h = harness(); h.visibility(true); assert.equal(h.timers.size, 0);
    h.visibility(false); assert.equal(h.timers.size, 1);
    h.responses.push({ status: 401 }); await h.tick();
    assert.equal(h.timers.size, 0); assert.equal(h.badge.hidden, true); assert.equal(h.cleared, 2);
});

test('account switch clears old account presentation and does not display the other account payload', async () => {
    const h = harness(); h.responses.push({ status: 200, data: { ...h.initial, account_id: 8, unread_count: 99, notifications: [{ id: 102, title: 'Other account' }] } });
    await h.tick(); assert.equal(h.badge.hidden, true); assert.equal(h.toasts.length, 0); assert.equal(h.timers.size, 0);
});

test('mark-as-read applies server response without a page reload and broadcasts only invalidation', async () => {
    const h = harness();
    const button = { disabled: false };
    const form = { action: 'http://localhost/notifikasi/100/read', matches(selector) { return selector === '[data-notification-read-form]'; }, querySelector() { return button; } };
    h.responses.push({ status: 200, data: { ...h.initial, snapshot_started_at: 2, unread_count: 1, category_unread: { orders: 1 } } });
    await h.submit(form);
    assert.equal(h.badge.textContent, '1'); assert.equal(h.dot.hidden, false); assert.equal(h.pending.textContent, '5');
    assert.deepEqual(h.messages, ['changed']); assert.equal(h.requests[0].options.method, 'POST'); assert.equal(h.timers.size, 1);
});

test('logout stops polling, clears notification presentation and rejects late state updates', async () => {
    const h = harness(); await h.submit({ matches(selector) { return selector.includes('/logout'); } });
    h.window.YzaNotificationState.apply({ ...h.initial, snapshot_started_at: 3, unread_count: 99 });
    assert.equal(h.badge.hidden, true); assert.equal(h.dot.hidden, true); assert.equal(h.timers.size, 0);
    assert.deepEqual(h.messages, ['logout']);
});
