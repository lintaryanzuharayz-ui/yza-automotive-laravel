(() => {
    const form = document.querySelector('[data-spk-form]');
    if (!form) return;
    const catalog = JSON.parse(form.querySelector('[data-spk-catalog]').textContent);
    const model = form.querySelector('[data-spk-model]'), unit = form.querySelector('[data-spk-unit]');
    const variant = form.querySelector('[data-spk-variant]');
    const colorData = form.querySelector('[data-spk-color-catalog]');
    const colorKey = value => String(value || '').trim().toLocaleLowerCase('id-ID');
    const variants = colorData?.textContent ? JSON.parse(colorData.textContent) : [...new Set(catalog.map(car=>car.group))].map(group=>{
        const cars=catalog.filter(car=>car.group===group);
        return {group,model_key:cars[0].model_key || group,name:cars[0].name,variant:cars[0].variant,
            colors:[...new Set(cars.map(car=>car.color))].map(name=>{const rows=cars.filter(car=>car.color===name);return {key:colorKey(name),name,
                swatch:rows[0].swatch,image:rows[0].image,available:true,stock:rows.reduce((sum,car)=>sum+car.stock,0),
                aggregate_stock:rows.filter(car=>car.aggregate).reduce((sum,car)=>sum+car.stock,0),unit_ids:rows.map(car=>car.id)};})};
    });
    const swatches = form.querySelector('[data-spk-swatches]'), status = form.querySelector('[data-spk-status]');
    const type = form.elements.manual_discount_type, value = form.elements.manual_discount_value, reason = form.elements.manual_discount_reason;
    const steps = [...form.querySelectorAll('[data-spk-step]')], labels = [...form.querySelectorAll('[data-spk-step-label]')];
    const back = form.querySelector('[data-spk-back]'), next = form.querySelector('[data-spk-next]'), save = form.querySelector('[data-spk-save]');
    let step = 0, quote = null, controller = null, version = 0, activeColor = null;
    const selected = () => catalog.find(car => String(car.id) === unit.value);
    const money = amount => 'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits:2}).format(Number(amount));
    const text = (selector, content) => { form.querySelector(selector).textContent = content; };
    const positive = () => Number(value.value) > 0;
    function confirmation() {
        const car = selected();
        text('[data-spk-confirm-member]', form.querySelector('[data-member-search]').value || '—');
        text('[data-spk-confirm-buyer]', [form.elements.nama_pembeli.value, form.elements.telepon_pembeli.value, form.elements.email_pembeli.value, form.elements.alamat_pembeli.value].filter(Boolean).join(' · ') || '—');
        text('[data-spk-confirm-vehicle]', car ? `${car.name} · ${car.variant} · ${car.color} · ${car.code}` : '—');
        text('[data-spk-confirm-vin]', car?.vin || 'VIN fisik belum dialokasikan pada inventori agregat');
        for (const [field, key] of [['base','base_price'],['discount','discount_amount'],['total','estimated_total']]) {
            text(`[data-spk-${field}]`, quote ? money(quote[key]) : '—');
            text(`[data-spk-confirm-${field}]`, quote ? money(quote[key]) : '—');
        }
        const approval = !quote ? 'Belum dihitung' : quote.approval_required ? 'Menunggu persetujuan independen; kebijakan harga minimum/margin belum tersedia' : 'Tanpa diskon; verifikasi pesanan tetap wajib';
        text('[data-spk-approval]', approval); text('[data-spk-confirm-approval]', approval);
        text('[data-spk-discount-label]', type.value === 'percentage' ? `(${value.value || 0}%)` : type.value === 'nominal' ? '(Rp)' : '');
        save.textContent = positive() ? 'Simpan Draft SPK' : 'Simpan Pesanan';
        save.disabled = !quote || (quote.approval_required && form.dataset.storageReady !== '1');
    }
    function showStep(index, focus = true) {
        step = index;
        steps.forEach((panel, i) => panel.hidden = i !== index);
        labels.forEach((label, i) => { if (i === index) label.setAttribute('aria-current','step'); else label.removeAttribute('aria-current'); });
        back.hidden = index === 0; next.hidden = index === 2; save.hidden = index !== 2;
        confirmation();
        if (focus) { const legend = steps[index].querySelector('legend'); legend.tabIndex = -1; legend.focus(); }
        form.querySelector('.order-create-body').scrollTop = 0;
    }
    async function refreshQuote() {
        const requestVersion = ++version; controller?.abort(); quote = null; form.elements.expected_base_price.value = '';
        reason.required = positive();
        value.max = type.value === 'percentage' ? '100' : '';
        value.setCustomValidity(type.value === 'none' && positive() ? 'Pilih jenis diskon, atau ubah nilai menjadi nol.' : '');
        const car = selected(); confirmation();
        if (!car) { status.textContent = activeColor ? 'Warna ini tidak tersedia untuk reservasi langsung. Pilih warna dengan stok eligible.' : 'Pilih unit inventori yang tersedia.'; return false; }
        if (!value.checkValidity()) { status.textContent = 'Periksa nilai diskon.'; return false; }
        status.textContent = 'Memeriksa harga, warna dan stok dari server…';
        controller = new AbortController();
        try {
            const query = new URLSearchParams({id_mobil:car.id, warna_pilihan:car.color, manual_discount_type:type.value, manual_discount_value:value.value || '0'});
            const response = await fetch(form.dataset.quoteUrl+'?'+query, {signal:controller.signal,cache:'no-store',headers:{Accept:'application/json'}});
            const data = await response.json();
            if (requestVersion !== version) return false;
            if (!response.ok) throw new Error(data.message || 'Estimasi tidak tersedia.');
            quote = data; form.elements.expected_base_price.value = data.base_price;
            status.textContent = data.approval_required ? (form.dataset.storageReady === '1'
                ? 'Dapat disimpan sebagai draft pengajuan. Potongan belum disahkan dan tidak dipakai untuk invoice.'
                : 'Simulasi tersedia. Penyimpanan draft diskon menunggu pembaruan schema SPK.')
                : 'Harga diperiksa server. Stok dan harga diperiksa ulang saat disimpan.';
            confirmation(); return true;
        } catch (error) {
            if (requestVersion !== version || error.name === 'AbortError') return false;
            status.textContent = error instanceof SyntaxError ? 'Respons tidak tersedia. Muat ulang halaman dan coba kembali.' : error.message;
            confirmation(); return false;
        }
    }
    function preview() {
        const car = selected(), group=variants.find(row=>row.group===variant.value);
        const panel = form.querySelector('[data-spk-preview]'); panel.hidden = !car && !activeColor;
        if (car) {
            form.elements.warna_pilihan.value = car.color;
            const image = form.querySelector('[data-spk-image]'); image.src = car.image; image.alt = `${car.name} · ${car.color}`;
            text('[data-spk-name]', car.name); text('[data-spk-unit-info]', `${car.variant} · ${car.color} · ${car.code}`);
            text('[data-spk-vin]', car.vin ? 'VIN: '+car.vin : 'Inventori agregat — VIN fisik belum dialokasikan');
            text('[data-spk-stock]', car.aggregate
                ? `Stok agregat warna ini: ${activeColor?.aggregate_stock ?? car.stock}. Ini jumlah stok, bukan daftar VIN individual; alokasi fisik wajib diverifikasi pada PDI.`
                : `Unit fisik dipilih: ${car.code}. ${car.identity_complete === false ? 'Identitas belum lengkap; tidak dapat diluluskan ke serah terima.' : 'VIN/nomor mesin diverifikasi lagi saat PDI.'}`);
        } else {
            form.elements.warna_pilihan.value = '';
            if(activeColor){const image=form.querySelector('[data-spk-image]');image.src=activeColor.image;image.alt=`${group?.name || ''} · ${activeColor.name}`;
                text('[data-spk-name]',group?.name || 'Warna katalog');text('[data-spk-unit-info]',`${group?.variant || ''} · ${activeColor.name}`);
                text('[data-spk-vin]','Tidak ada unit eligible dipilih');text('[data-spk-stock]','Tidak tersedia — katalog/foto bukan bukti stok. Tidak ada reservasi atau pre-order dibuat.');}
        }
        refreshQuote();
    }
    function chooseColor(key = null, preferred = null) {
        const group=variants.find(row=>row.group===variant.value && row.model_key===model.value);
        const colors=group?.colors || [];
        activeColor=colors.find(color=>color.key===key) || colors.find(color=>color.available) || colors[0] || null;
        const cars = catalog.filter(car => car.group === variant.value && activeColor && colorKey(car.color) === activeColor.key);
        [...unit.options].forEach(option => {
            const car = catalog.find(car => String(car.id) === option.value);
            option.hidden = !!car && !cars.some(row=>row.id===car.id);
            option.disabled = !!car && option.hidden;
        });
        const car = cars.find(car => String(car.id) === String(preferred)) || cars[0];
        unit.value = car ? String(car.id) : '';
        swatches.replaceChildren();
        colors.forEach(row => {
            const button = document.createElement('button'), dot = document.createElement('i');
            button.type = 'button'; button.setAttribute('aria-pressed',String(row === activeColor));
            button.setAttribute('data-stock-state',row.available ? 'available' : 'unavailable');
            button.setAttribute('aria-label',`${row.name} — ${row.available ? `${row.stock} tersedia` : 'Tidak tersedia, lihat foto katalog'}`);
            dot.style.backgroundColor = row.swatch; dot.setAttribute('aria-hidden','true');
            button.append(dot,document.createTextNode(`${row.name} · ${row.available ? `${row.stock} tersedia` : 'Tidak tersedia'}`));
            button.addEventListener('click', () => chooseColor(row.key, unit.value)); swatches.append(button);
        });
        form.querySelector('[data-spk-no-color]').hidden = colors.length > 0;
        const colorStatus=form.querySelector('[data-spk-color-status]');
        if(colorStatus)colorStatus.textContent=activeColor ? `${activeColor.name}${activeColor.code ? ' · '+activeColor.code : ''} — ${activeColor.available ? `${activeColor.stock} stok eligible saat dimuat` : 'Tidak tersedia. Pengajuan pre-order warna tertentu belum didukung dari akun staf.'}` : '';
        preview();
    }
    function chooseModel(preferredGroup=null, preferredColor=null, preferredUnit=null){
        const groups=variants.filter(row=>row.model_key===model.value);
        [...(variant.options || [])].forEach(option=>{option.hidden=!!option.value&&!groups.some(row=>row.group===option.value);option.disabled=option.hidden;});
        variant.value=groups.find(row=>row.group===preferredGroup)?.group || groups[0]?.group || '';
        chooseColor(preferredColor,preferredUnit);
    }
    model.addEventListener('change', () => chooseModel());
    variant.addEventListener('change', () => chooseColor());
    unit.addEventListener('change', preview);
    type.addEventListener('change', refreshQuote); value.addEventListener('input', refreshQuote);
    form.addEventListener('input', confirmation);
    back.addEventListener('click', () => showStep(Math.max(0,step-1)));
    next.addEventListener('click', async () => {
        const invalid = [...steps[step].querySelectorAll('input,select,textarea')].find(field => !field.checkValidity());
        if (invalid) { invalid.reportValidity(); return; }
        if (!await refreshQuote()) return;
        showStep(Math.min(2,step+1));
    });
    form.addEventListener('invalid', event => {
        const panel = event.target.closest('[data-spk-step]');
        if (panel) showStep(Number(panel.dataset.spkStep),false);
    }, true);
    form.addEventListener('keydown', event => {
        if (event.key === 'Enter' && step < 2 && !event.defaultPrevented
            && event.target.tagName === 'INPUT' && event.target.type !== 'checkbox') {
            event.preventDefault(); next.click();
        }
    });
    form.addEventListener('submit', event => {
        if (step !== 2 || !quote || save.disabled) { event.preventDefault(); if (step < 2) next.click(); return; }
        save.disabled = true; save.textContent = 'Menyimpan…';
    });
    document.querySelectorAll('[data-order-create-open]').forEach(button => button.addEventListener('click', () => { showStep(0,false); refreshQuote(); }));
    const initial = selected();
    if (initial) { model.value = initial.model_key || initial.group; chooseModel(initial.group,colorKey(initial.color),initial.id); }
    else chooseModel();
    showStep(0,false);
})();
