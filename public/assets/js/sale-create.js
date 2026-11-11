(() => {
    const form = document.getElementById('sale-create-form');
    if (!form) return;
    const car = form.querySelector('#sale-car'), customer = form.querySelector('#sale-customer');
    const booking = form.querySelector('#sale-booking'), discount = form.querySelector('#sale-discount');
    const dp = form.querySelector('#sale-down-payment'), box = form.querySelector('[data-sale-promo]');
    const radios = [...box.querySelectorAll('[name=promo_id]')];
    const status = box.querySelector('.sale-promo-status'), summary = box.querySelector('.sale-promo-summary');
    const remove = box.querySelector('[data-sale-promo-remove]');
    const money = value => 'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 2}).format(Number(value) || 0);
    const text = (selector, value) => { const el = document.querySelector(selector); if (el) el.textContent = value; };
    let preview = null, request = null, version = 0;
    const chosen = () => radios.find(input => input.checked);
    const validation = (key, valid, label) => {
        const row = form.querySelector(`[data-validation="${key}"]`);
        row.classList.toggle('is-valid', valid);
        row.querySelector('i').textContent = valid ? '✓' : '•';
        row.querySelector('b').textContent = label;
    };
    function render() {
        const option = car.selectedOptions[0], selected = !!car.value;
        const price = Number(option?.dataset.price || 0), manual = Number(discount.value || 0), down = Number(dp.value || 0);
        const total = preview ? Number(preview.total_final) : Math.max(0, price - manual);
        const display = value => selected ? money(value) : '—';
        text('#sale-estimate-total', display(total)); text('#sale-price', display(price));
        text('#sale-discount-total', selected ? '- ' + money(manual) : '—');
        text('#sale-total', display(total)); text('#sale-dp-total', display(down));
        text('#sale-remaining-total', display(Math.max(0, total - down)));
        text('#sale-promo-total', preview ? '- ' + money(preview.discount) : chosen() ? 'Belum tervalidasi' : 'Tidak dipilih');
        form.querySelector('#sale-summary-empty').hidden = selected;
        form.querySelector('#sale-summary-car').hidden = !selected;
        if (selected) {
            form.querySelector('#sale-car-image').src = option.dataset.image;
            text('#sale-car-name', `${option.dataset.brand} ${option.dataset.model}`);
            text('#sale-car-meta', `${option.dataset.code} · Tahun ${option.dataset.year}`);
            text('#sale-car-status', option.dataset.status === 'tersedia' ? 'Periksa stok saat disimpan' : 'Perlu verifikasi booking');
        }
        discount.setCustomValidity(selected && manual > price ? 'Diskon melebihi harga unit.' : '');
        dp.setCustomValidity(selected && down > total ? 'DP melebihi total estimasi.' : '');
        const b = booking.selectedOptions[0];
        const matches = !booking.value || (b.dataset.customer === customer.value && b.dataset.car === car.value);
        booking.setCustomValidity(matches ? '' : 'Booking harus sesuai pelanggan dan kendaraan.');
        validation('customer', !!customer.value, customer.value ? 'Terpilih' : 'Belum dipilih');
        validation('car', false, selected ? 'Dicek saat disimpan' : 'Belum dipilih');
        validation('booking', matches, !booking.value ? 'Opsional' : matches ? 'Cocok' : 'Tidak sesuai');
        validation('promo', !!preview, preview ? 'Estimasi valid' : chosen() ? 'Perlu validasi' : 'Opsional');
        const note = form.querySelector('textarea[name=catatan]');
        text('.sale-note-count', `${note.value.length}/2000`);
    }
    async function refreshPromo() {
        const current = ++version;
        request?.abort(); preview = null; summary.hidden = true;
        radios.forEach(input => input.setCustomValidity(''));
        const radio = chosen(); remove.hidden = !radio;
        if (!radio) { status.textContent = 'Belum ada promo dipilih.'; render(); return; }
        radio.setCustomValidity('Tunggu hingga kelayakan promo selesai diperiksa, atau hapus promo.');
        if (!customer.value || !car.value || Number(discount.value) > 0) {
            status.textContent = Number(discount.value) > 0 ? 'Diskon manual dan promo tidak dapat digabungkan.' : 'Pilih pelanggan dan unit untuk memeriksa promo.';
            render(); return;
        }
        status.textContent = 'Memeriksa kelayakan promo…'; render();
        request = new AbortController();
        const query = new URLSearchParams({id_pelanggan: customer.value, id_mobil: car.value, promo_id: radio.value});
        try {
            const response = await fetch(box.dataset.url + '?' + query, {signal: request.signal, cache: 'no-store', headers: {Accept: 'application/json'}});
            const data = await response.json();
            if (current !== version) return;
            if (!response.ok || !data.eligible) throw new Error(data.message || 'Promo tidak memenuhi syarat.');
            preview = data; radio.setCustomValidity(''); summary.hidden = false;
            text('[data-sale-promo-subtotal]', money(data.subtotal));
            text('[data-sale-promo-discount]', '- ' + money(data.discount));
            text('[data-sale-promo-total]', money(data.total_final));
            status.textContent = 'Estimasi promo valid untuk pelanggan ini. Kuota dan harga final diperiksa kembali saat disimpan.';
        } catch (error) {
            if (current !== version || error.name === 'AbortError') return;
            status.textContent = error.message === 'Failed to fetch' ? 'Koneksi terputus. Pilih ulang atau hapus promo.' : error.message;
            radio.setCustomValidity('Promo belum valid. Pilih promo lain atau hapus promo.');
        }
        render();
    }
    [car, customer, discount].forEach(input => input.addEventListener('input', refreshPromo));
    [car, customer].forEach(input => input.addEventListener('change', refreshPromo));
    radios.forEach(input => input.addEventListener('change', refreshPromo));
    [booking, dp, form.querySelector('textarea[name=catatan]')].forEach(input => input.addEventListener('input', render));
    remove.addEventListener('click', () => { radios.forEach(input => input.checked = false); refreshPromo(); });
    form.addEventListener('reset', () => setTimeout(refreshPromo, 0));
    form.addEventListener('submit', () => { const button = form.querySelector('#sale-submit'); button.disabled = true; button.textContent = 'Memproses…'; });
    window.addEventListener('pageshow', () => { const button = form.querySelector('#sale-submit'); button.disabled = false; button.textContent = 'Buat Penjualan'; });
    refreshPromo();
})();
