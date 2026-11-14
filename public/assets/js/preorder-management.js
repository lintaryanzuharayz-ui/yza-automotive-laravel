(() => {
    'use strict';
    const modal = document.querySelector('[data-preorder-modal]');
    if (!modal) return;
    document.body.append(modal);
    const get = name => modal.querySelector(`[data-pom-${name}]`);
    const tabs = [...modal.querySelectorAll('[data-pom-tab]')];
    const form = get('form'), selection = form.elements.action, confirmation = get('confirmation');
    let trigger, urls, data, detailRequest, stockRequest, stockResult, activeTab = 'detail', saving = false, dirty = false, previousOverflow = '';
    const label = value => ({ menunggu_stok: 'Menunggu Stok', stok_tersedia: 'Stok Tersedia', notifikasi_terkirim: 'Notifikasi Terkirim', dilanjutkan_ke_checkout: 'Lanjut ke Checkout', dibatalkan: 'Dibatalkan' }[value] || value);
    const date = value => value ? new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : 'Belum tercatat';
    const error = (message, retry = false) => {
        get('error-text').textContent = message;
        get('error').hidden = false;
        get('retry').hidden = !retry;
    };
    const editable = () => Boolean(data?.can_manage && data?.can_act);
    function history(items = []) {
        const fragment = document.createDocumentFragment();
        for (const item of items) {
            const article = document.createElement('article'), title = document.createElement('strong'), line = document.createElement('p');
            title.textContent = item.message;
            line.textContent = `${date(item.at)} · ${item.actor || 'Sistem'}${item.notification_status ? ' · ' + item.notification_status : ''}`;
            article.append(title, line); fragment.append(article);
        }
        if (!items.length) { const empty = document.createElement('p'); empty.textContent = 'Belum ada riwayat aktivitas yang tersimpan. Data legacy tidak direka ulang.'; fragment.append(empty); }
        get('history-list').replaceChildren(fragment);
    }
    function updateActions() {
        form.hidden = !editable() || !get('confirm').hidden;
        get('readonly').hidden = editable();
        get('review').disabled = saving || !editable();
        get('save').disabled = saving || !editable() || confirmation.value !== data?.code;
        const available = (stockResult || data?.stock)?.available === true;
        const notify = selection.querySelector('[value="notify"]'), resend = selection.querySelector('[value="resend"]');
        notify.disabled = !available || !data?.member_eligible || Boolean(data?.notified_at);
        resend.disabled = !available || !data?.member_eligible || !data?.notified_at || !data?.automatic_delivery_enabled;
        get('eligibility-note').textContent = available ? 'Stok cocok ditemukan. Stok tidak dijamin dan akan diperiksa kembali saat checkout.' : 'Pemberitahuan dinonaktifkan: belum ada stok cocok yang eligible. Buka tab Ketersediaan Stok untuk memeriksa ulang.';
        get('reason').required = selection.value === 'resend';
        get('reason-group').hidden = selection.value !== 'resend';
        if (data && !data.member_eligible) get('eligibility-note').textContent = 'Pemberitahuan dinonaktifkan: akun atau membership penerima belum eligible.';
    }
    function populate(value) {
        data = value;
        modal.querySelectorAll('[data-pom-field]').forEach(node => {
            const key = node.dataset.pomField;
            let text = key.split('.').reduce((obj, part) => obj?.[part], data);
            if (key.endsWith('_at')) text = date(text);
            if (key === 'status') text = label(text);
            node.textContent = text ?? 'Belum tercatat';
        });
        get('image').src = data.image;
        get('image').alt = [data.vehicle, data.variant, data.year, data.color].filter(Boolean).join(' · ');
        get('footer-status').textContent = `${data.code} · ${label(data.status)}`;
        modal.querySelectorAll('[data-pom-manage]').forEach(link => { link.hidden = !data.stock_management_url; if (data.stock_management_url) link.href = data.stock_management_url; });
        history(data.history);
        get('automation-status').hidden = Boolean(data.automatic_delivery_enabled);
        updateActions();
    }
    async function fetchJson(url, options = {}) {
        const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', ...options,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...options.headers } });
        if ([401, 419].includes(response.status)) throw new Error('Sesi berakhir. Muat ulang halaman dan masuk kembali.');
        if (response.status === 403) throw new Error('Akun Anda tidak diizinkan mengakses tindakan ini.');
        let result;
        try { result = await response.json(); } catch (_) { throw new Error('Respons belum dapat dibaca. Silakan coba kembali.'); }
        if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : result.message || 'Permintaan gagal. Silakan coba kembali.');
        return result;
    }
    async function checkStock() {
        if (!data || saving) return;
        stockRequest?.abort();
        const request = new AbortController(); stockRequest = request;
        get('check').disabled = true;
        get('stock-status').textContent = 'Memeriksa inventori dan reservasi aktif…';
        try {
            const result = await fetchJson(urls.stock, { signal: request.signal, ...(data.can_manage ? {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''}} : {}) });
            if (request.signal.aborted) return;
            stockResult = result;
            if (result.history) history(result.history);
            updateActions();
            get('stock-status').textContent = `${data.vehicle} · ${data.variant} · ${data.year} · ${data.color}. ${result.message} Diperiksa ${date(result.checked_at)}. Total ${result.total} unit eligible; ${result.reserved_total || 0} reservasi aktif.`;
            const fragment = document.createDocumentFragment();
            for (const unit of result.units) {
                const article = document.createElement('article'), title = document.createElement('h4');
                title.textContent = `${unit.code} · ${unit.kind} · ${unit.stock} unit`; article.append(title);
                for (const text of [
                    `${unit.vehicle} · ${unit.variant || 'Varian belum tercatat'} · ${unit.year} · ${unit.color}`,
                    `${unit.transmission || 'Transmisi belum tercatat'} · ${unit.fuel || 'Bahan bakar belum tercatat'} · ${unit.condition || 'Kondisi belum tercatat'}${unit.limited ? ' · Limited edition' : ''}`,
                    `Harga katalog saat ini: ${unit.price} · Reservasi aktif: ${unit.reserved || 0}`,
                    unit.vin ? `Nomor rangka unit: ${unit.vin}` : 'Nomor rangka tidak ditetapkan dari stok agregat.',
                ]) { const line = document.createElement('p'); line.textContent = text; article.append(line); }
                fragment.append(article);
            }
            get('stock-list').replaceChildren(fragment);
        } catch (failure) { if (failure.name !== 'AbortError') { get('stock-status').textContent = 'Pemeriksaan belum berhasil.'; error(failure.message); } }
        finally { if (stockRequest === request) { stockRequest = null; get('check').disabled = saving; } }
    }
    function selectTab(name, focus = false) {
        activeTab = name;
        modal.querySelector('.pom-scroll').scrollTop = 0;
        tabs.forEach(tab => { const selected = tab.dataset.pomTab === name; tab.setAttribute('aria-selected', String(selected)); tab.tabIndex = selected ? 0 : -1; if (focus && selected) tab.focus(); });
        modal.querySelectorAll('[data-pom-panel]').forEach(panel => { panel.hidden = !data || panel.dataset.pomPanel !== name; });
        get('discard').hidden = true;
        if (name === 'stock' && data && !stockResult && !stockRequest) checkStock();
    }
    async function loadDetail() {
        detailRequest?.abort();
        const request = new AbortController(); detailRequest = request;
        get('loading').hidden = false; get('error').hidden = true;
        try {
            const value = await fetchJson(urls.detail, { signal: request.signal });
            if (request.signal.aborted) return;
            populate(value); selectTab(activeTab);
        } catch (failure) { if (failure.name !== 'AbortError') error(failure.message, true); }
        finally { if (detailRequest === request) { detailRequest = null; get('loading').hidden = true; } }
    }
    function close() {
        if (saving) return;
        detailRequest?.abort(); stockRequest?.abort();
        modal.hidden = true;
        document.body.style.overflow = previousOverflow;
        trigger?.focus();
        data = null; dirty = false;
    }
    function requestClose() {
        if (saving) return;
        if (dirty) { get('discard').hidden = false; get('keep').focus(); }
        else close();
    }
    document.querySelectorAll('[data-preorder-open]').forEach(button => button.addEventListener('click', () => {
        trigger = button;
        urls = { detail: button.dataset.detailUrl, stock: button.dataset.stockUrl, action: button.dataset.actionUrl };
        activeTab = button.dataset.preorderOpen;
        data = null; stockResult = null; dirty = false; saving = false;
        form.reset(); confirmation.value = '';
        get('confirm').hidden = true; get('discard').hidden = true; get('success').hidden = true;
        get('stock-list').replaceChildren(); get('stock-status').textContent = 'Belum diperiksa.';
        modal.querySelectorAll('[data-pom-field]').forEach(node => { node.textContent = 'Memuat…'; });
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden'; modal.hidden = false;
        modal.querySelector('.pom-scroll').scrollTop = 0;
        selectTab(activeTab); get('close').focus(); loadDetail();
    }));
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab.dataset.pomTab));
        tab.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (next != null) { event.preventDefault(); selectTab(tabs[next].dataset.pomTab, true); }
        });
    });
    get('check').addEventListener('click', checkStock);
    get('retry').addEventListener('click', loadDetail);
    modal.querySelectorAll('[data-pom-close]').forEach(button => button.addEventListener('click', requestClose));
    get('backdrop').addEventListener('click', requestClose);
    get('keep').addEventListener('click', () => { get('discard').hidden = true; tabs.find(tab => tab.dataset.pomTab === activeTab)?.focus(); });
    get('discard-confirm').addEventListener('click', close);
    selection.addEventListener('change', () => { dirty = selection.value !== ''; get('error').hidden = true; });
    selection.addEventListener('change', updateActions);
    get('reason').addEventListener('input', () => { dirty = true; });
    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!editable() || saving || !selection.value || selection.selectedOptions[0]?.disabled) return;
        if (selection.value === 'resend' && get('reason').value.trim().length < 10) { error('Isi alasan kirim ulang minimal 10 karakter.'); return; }
        get('confirm-text').textContent = selection.value === 'cancel'
            ? `Batalkan ${data.code} untuk ${data.vehicle} / ${data.color}? Member akan menerima notifikasi. Tidak ada transaksi penjualan yang dibuat.`
            : `Beri tahu member ${data.customer.name || ''} tentang stok ${data.vehicle} / ${data.color}? Backend akan memeriksa ulang kecocokan stok sebelum mengirim notifikasi.`;
        get('confirm-code').textContent = data.code;
        get('confirm').hidden = false; updateActions(); confirmation.focus();
    });
    confirmation.addEventListener('input', () => { dirty = true; updateActions(); });
    get('back').addEventListener('click', () => { get('confirm').hidden = true; confirmation.value = ''; updateActions(); selection.focus(); });
    get('save').addEventListener('click', async () => {
        if (saving || !editable() || confirmation.value !== data.code) return;
        saving = true; get('error').hidden = true; get('save').textContent = 'Menyimpan…';
        modal.setAttribute('aria-busy', 'true');
        modal.querySelectorAll('button,select,input').forEach(node => { node.disabled = true; });
        try {
            const result = await fetchJson(urls.action, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ action: selection.value, expected_status: data.status, confirmation: confirmation.value, reason: get('reason').value }) });
            dirty = false; get('confirm').hidden = true; form.reset(); confirmation.value = '';
            populate(result.preorder);
            get('success').textContent = result.message; get('success').hidden = false;
            const row = document.querySelector(`[data-preorder-row="${Number(data.id)}"]`);
            const badge = row?.querySelector('.badge-status');
            if (badge) { badge.textContent = label(data.status); badge.className = `badge-status ${data.status === 'dibatalkan' ? 'is-cancelled' : 'is-confirmed'}`; }
            ['active', 'waiting', 'follow_up', 'checkout'].forEach((key, index) => {
                const value = document.querySelector(`[data-preorder-stat="${index}"]`);
                if (value && result.stats?.[key] != null) value.textContent = Number(result.stats[key]).toLocaleString('id-ID');
            });
            window.YzaNotificationState?.refresh();
        } catch (failure) { error(failure.message); }
        finally {
            saving = false; modal.removeAttribute('aria-busy');
            modal.querySelectorAll('button,select,input').forEach(node => { node.disabled = false; });
            get('save').textContent = 'Konfirmasi & simpan'; updateActions();
        }
    });
    document.addEventListener('keydown', event => {
        if (modal.hidden) return;
        if (event.key === 'Escape') { event.preventDefault(); event.stopImmediatePropagation(); requestClose(); }
        if (event.key === 'Tab') {
            const items = [...modal.querySelectorAll('button,input,select,textarea,a[href],[tabindex="0"]')].filter(node => !node.disabled && node.getClientRects().length);
            const first = items[0], last = items.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); event.stopImmediatePropagation(); last?.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); event.stopImmediatePropagation(); first?.focus(); }
        }
    }, true);
    const review = Number(new URLSearchParams(window.location.search).get('review'));
    if (Number.isInteger(review) && review > 0) document.querySelector(`[data-preorder-row="${review}"] [data-preorder-open]`)?.click();
})();
