(() => {
    const dialog = document.getElementById('order-create-dialog');
    if (!dialog) return;
    let opener;
    document.querySelectorAll('[data-order-create-open]').forEach(button => button.addEventListener('click', () => {
        opener = button;
        dialog.showModal();
    }));
    dialog.querySelectorAll('[data-order-create-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('close', () => (opener || document.querySelector('[data-order-create-open]'))?.focus());
    // Native dialog handles Escape, focus trapping, and background inertness.
    const form = dialog.querySelector('form');
    const picker = form.querySelector('[data-member-picker]');
    if (picker) {
        const search = picker.querySelector('[data-member-search]');
        const list = picker.querySelector('[role=listbox]');
        const options = [...picker.querySelectorAll('[data-member-option]')];
        const empty = picker.querySelector('[data-member-empty]');
        let active = -1;
        const visible = () => options.filter(option => !option.hidden);
        const expand = open => { list.hidden = !open; search.setAttribute('aria-expanded', String(open)); };
        const validate = () => search.setCustomValidity(form.elements.id_pelanggan.value ? '' : 'Pilih member aktif dari daftar.');
        const select = option => {
            form.elements.id_pelanggan.value = option.dataset.id;
            search.value = `${option.dataset.name} — ${option.dataset.member}`;
            form.elements.nama_pembeli.value = option.dataset.name;
            form.elements.telepon_pembeli.value = option.dataset.phone || '';
            form.elements.alamat_pembeli.value = option.dataset.address || '';
            if (form.elements.email_pembeli) form.elements.email_pembeli.value = option.dataset.email || '';
            options.forEach(item => item.setAttribute('aria-selected', String(item === option)));
            validate(); expand(false); search.focus(); expand(false);
        };
        const filter = () => {
            const query = search.value.trim().toLocaleLowerCase('id-ID');
            options.forEach(option => {
                option.hidden = !`${option.dataset.name} ${option.dataset.member} ${option.dataset.code}`.toLocaleLowerCase('id-ID').includes(query);
                option.classList.remove('is-active');
            });
            active = -1; search.removeAttribute('aria-activedescendant');
            empty.hidden = visible().length > 0;
        };
        options.forEach((option, index) => {
            option.id = `order-member-option-${index}`; option.tabIndex = -1;
            option.addEventListener('click', () => select(option));
        });
        const openOptions = () => {
            options.forEach(option => { option.hidden = false; option.classList.remove('is-active'); });
            active = -1; search.removeAttribute('aria-activedescendant');
            empty.hidden = options.length > 0; expand(true);
        };
        search.addEventListener('focus', openOptions);
        search.addEventListener('click', openOptions);
        search.addEventListener('input', () => {
            form.elements.id_pelanggan.value = '';
            options.forEach(option => option.setAttribute('aria-selected', 'false'));
            validate(); filter(); expand(true);
        });
        search.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !list.hidden) { event.preventDefault(); event.stopPropagation(); expand(false); return; }
            if (event.key === 'Tab') { expand(false); return; }
            const items = visible();
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault(); expand(true);
                if (!items.length) return;
                active = active < 0 ? (event.key === 'ArrowDown' ? 0 : items.length - 1)
                    : (active + (event.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
                options.forEach(option => option.classList.remove('is-active'));
                items[active].classList.add('is-active'); search.setAttribute('aria-activedescendant', items[active].id);
                items[active].scrollIntoView({block:'nearest'});
            } else if (event.key === 'Enter' && !list.hidden) {
                event.preventDefault(); if (items[active] || items.length === 1) select(items[active] || items[0]);
            }
        });
        picker.addEventListener('focusout', event => { if (!picker.contains(event.relatedTarget)) expand(false); });
        search.addEventListener('invalid', () => expand(true));
        validate();
    }
    form.addEventListener('submit', () => {
        if (form.dataset.spkForm === '1') return;
        const submit = form.querySelector('[type="submit"]');
        submit.disabled = true;
        submit.textContent = 'Menyimpan…';
    });
    if (dialog.dataset.reopen === '1') dialog.showModal();
})();
