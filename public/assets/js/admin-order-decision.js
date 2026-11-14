document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-decision-modal]');
    if (!modal) return;
    // Keep the active confirmation last for the shared modal keyboard focus trap.
    document.body.appendChild(modal);
    const get = name => modal.querySelector(`[data-decision-${name}]`);
    const reviewed = get('reviewed'), code = get('code'), next = get('next'), submit = get('submit');
    const note = modal.querySelector('[data-reject-input]');
    let form = null, trigger = null, decision = null, step = 1, sending = false;
    const sync = () => {
        next.disabled = !reviewed.checked;
        submit.disabled = sending || !reviewed.checked || code.value !== form?.dataset.orderNumber;
    };
    const showStep = number => {
        step = number;
        get('step').textContent = number === 1 ? 'Langkah 1 dari 2 · Periksa pesanan' : 'Langkah 2 dari 2 · Konfirmasi akhir';
        get('review-label').hidden = number !== 1;
        get('final').hidden = number !== 2;
        get('back').hidden = number !== 2;
        modal.querySelector('[data-reject-note]').hidden = decision !== 'reject' || number !== 1;
        next.hidden = number !== 1;
        submit.hidden = number !== 2;
        sync();
        (number === 1 ? reviewed : code).focus();
    };
    const close = () => {
        if (sending) return;
        trigger?.focus();
        modal.hidden = true;
        form = null;
        code.value = '';
        reviewed.checked = false;
        note.value = '';
        if (!document.querySelector('.admin-order-detail-modal:not([hidden])')) document.body.style.overflow = '';
    };
    document.querySelectorAll('[data-decision]').forEach(button => button.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        form = document.getElementById(button.dataset.form);
        if (!form) return;
        trigger = button;
        decision = button.dataset.decision;
        sending = false;
        reviewed.checked = false;
        code.value = '';
        note.value = '';
        get('error').hidden = true;
        const reject = decision === 'reject';
        get('title').textContent = reject ? 'Periksa Penolakan Pesanan' : 'Periksa Persetujuan Pesanan';
        get('message').textContent = reject ? 'Pesanan akan ditolak dan reservasi mobil dilepas.' : 'Pesanan akan disetujui dan tagihan pembayaran dibuat untuk member.';
        modal.querySelector('[data-reject-note]').hidden = !reject;
        get('order-code').textContent = form.dataset.orderNumber;
        get('summary').replaceChildren();
        [['Pesanan', form.dataset.orderNumber], ['Pembeli', form.dataset.orderBuyer], ['Mobil', form.dataset.orderVehicle], ['Spesifikasi', form.dataset.orderSpec], ['Total', form.dataset.orderTotal]].forEach(([label, value]) => {
            const row = document.createElement('div'), term = document.createElement('dt'), detail = document.createElement('dd');
            term.textContent = label; detail.textContent = value || 'Belum tercatat';
            row.append(term, detail); get('summary').append(row);
        });
        submit.textContent = reject ? 'Konfirmasi Akhir: Tolak Pesanan' : 'Konfirmasi Akhir: Setujui Pesanan';
        submit.classList.toggle('is-approve', !reject);
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        showStep(1);
    }));
    reviewed.addEventListener('change', sync);
    code.addEventListener('input', () => { get('error').hidden = true; sync(); });
    next.addEventListener('click', () => { if (reviewed.checked) showStep(2); });
    get('back').addEventListener('click', () => { code.value = ''; showStep(1); });
    modal.querySelectorAll('[data-decision-cancel]').forEach(button => button.addEventListener('click', close));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.hidden) { event.preventDefault(); close(); }
    });
    // Enter inside the code input deliberately does not submit a financial decision.
    submit.addEventListener('click', () => {
        if (!form || sending || step !== 2 || !reviewed.checked) return;
        if (code.value !== form.dataset.orderNumber) { get('error').hidden = false; return; }
        form.elements.confirmation_reviewed.value = '1';
        form.elements.confirmation_order.value = code.value;
        form.elements.catatan_admin.value = note.value.trim() || (decision === 'reject' ? 'Pesanan ditolak oleh admin.' : 'Pesanan disetujui oleh admin.');
        sending = true;
        sync();
        modal.querySelectorAll('button').forEach(button => { button.disabled = true; });
        submit.textContent = 'Memproses keputusan…';
        form.requestSubmit();
    });
});
