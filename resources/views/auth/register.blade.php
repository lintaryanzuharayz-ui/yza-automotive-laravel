<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar YZA Member · YZA Automotive</title>
    <link rel="icon" href="{{ asset('assets/images/branding/logo-yza.png') }}">
    @foreach (['variables.css','base.css','components.css','pages/login.css','pages/pelanggan.css','pages/register.css'] as $style)
        <link rel="stylesheet" href="{{ asset('assets/css/'.$style) }}">
    @endforeach
    <link rel="stylesheet" href="{{ asset('assets/css/pages/membership-flow.css?v=membership-flow-20261004') }}">
</head>
<body>
<main class="register-page">
    <section class="register-showcase">
        <div class="register-showcase-brand"><img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive"><strong><span>YZA</span> AUTOMOTIVE</strong></div>
        <div class="register-showcase-copy">
            <span class="register-kicker">YZA AUTOMOTIVE MEMBERSHIP</span>
            <h1>BERGABUNG MENJADI<br><em>YZA MEMBER</em></h1>
            <p>Aktifkan membership YZA Automotive dan nikmati pengalaman otomotif yang lebih lengkap melalui satu akun.</p>
            <ul>
                <li><span class="register-feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 9h16v11H4zM7 9V6a5 5 0 0 1 10 0v3M8 13h8M12 11v5"/></svg></span><div><b>Benefit Eksklusif</b><small>Promo dan keuntungan khusus member.</small></div></li>
                <li><span class="register-feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 11h18M5 11V7l3-3h8l3 3v4M5 11v8h14v-8M8 19v2M16 19v2M7 15h10"/></svg></span><div><b>Kelola Kendaraan</b><small>Akses kendaraan dan layanan dalam satu akun.</small></div></li>
                <li><span class="register-feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v18M7 7h7a3 3 0 1 1 0 6H9a3 3 0 1 0 0 6h8"/></svg></span><div><b>YZA Points &amp; Reminder</b><small>Kumpulkan points dan dapatkan pengingat servis.</small></div></li>
            </ul>
        </div>
    </section>
    <form class="card register-card" method="post" action="{{ route('register.store') }}" autocomplete="off" novalidate id="register-form">
        @csrf
        <input type="hidden" name="current_step" id="current_step" value="{{ old('current_step', request()->query('step', 1)) }}">
        <div class="register-card-heading"><span class="eyebrow">YZA AUTOMOTIVE</span><span class="register-heading-line"></span></div>
        <h2>Daftar YZA Member</h2>
        <p class="intro">Buat akun dan aktifkan membership YZA Automotive untuk mengakses layanan dan benefit eksklusif.</p>
        <div class="register-membership-price"><div><span>AKTIVASI MEMBERSHIP</span><strong>Rp {{ number_format((float) app(\App\Services\PengaturanService::class)->membershipFee(), 0, ',', '.') }}</strong></div><small>Sekali aktivasi<br>Tanpa biaya bulanan</small></div>
        <div class="register-steps" aria-label="Progress pendaftaran"><span class="active" data-step-label="1"><b>01</b> Data Diri</span><i></i><span data-step-label="2"><b>02</b> Alamat</span><i></i><span data-step-label="3"><b>03</b> Membership</span><i></i><span data-step-label="4"><b>04</b> Pembayaran</span><i></i><span data-step-label="5"><b>05</b> Selesai</span></div>
        @if($errors->any())<div class="alert alert-error" role="alert">{{ $errors->first() }}</div>@endif
        <div class="register-flow-step is-active" data-step="1">
            <div class="form-grid">
                @foreach ([['nama_lengkap','Nama Lengkap','text','Masukkan nama lengkap Anda'],['no_hp','Nomor HP','tel','Contoh: 08123456789'],['email','Email','email','nama@email.com'],['username','Username','text','Buat username Anda']] as $field)
                    <div class="form-group"><label class="form-label" for="{{ $field[0] }}">{{ $field[1] }}</label><div class="register-input"><input class="form-control" id="{{ $field[0] }}" name="{{ $field[0] }}" type="{{ $field[2] }}" placeholder="{{ $field[3] }}" value="{{ old($field[0]) }}" required></div>@error($field[0])<p class="form-error">{{ $message }}</p>@enderror</div>
                @endforeach
                <div class="form-group"><label class="form-label" for="password">Password</label><div class="register-input"><input class="form-control" id="password" name="password" type="password" minlength="8" required><button class="field-action password-toggle" type="button" data-target="password" aria-label="Tampilkan password"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-5 9.5-5 9.5 5 9.5 5-3.4 5-9.5 5-9.5-5-9.5-5Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div></div>
                <div class="form-group"><label class="form-label" for="password_confirmation">Konfirmasi Password</label><div class="register-input"><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" required><button class="field-action password-toggle" type="button" data-target="password_confirmation" aria-label="Tampilkan password"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-5 9.5-5 9.5 5 9.5 5-3.4 5-9.5 5-9.5-5-9.5-5Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div></div>
                <div class="form-group"><label class="form-label" for="jenis_kelamin">Jenis Kelamin</label><div class="register-input"><select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required><option value="">Pilih jenis kelamin</option><option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option><option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option></select></div></div>
                <div class="form-group"><label class="form-label" for="tanggal_lahir">Tanggal Lahir</label><div class="register-input"><input class="form-control" id="tanggal_lahir" name="tanggal_lahir" type="date" value="{{ old('tanggal_lahir') }}" required></div></div>
            </div>
            <div class="form-actions"><a class="button button-secondary" href="{{ route('login') }}">Kembali ke Login</a><button class="button button-primary" type="button" data-next-step="1">LANJUT KE ALAMAT&nbsp; →</button></div>
        </div>
        <div class="register-flow-step" data-step="2" hidden>
            <div class="register-address-heading"><span class="eyebrow">ALAMAT TEMPAT TINGGAL</span><h3>Lengkapi wilayah tempat tinggal</h3><p>Lengkapi wilayah tempat tinggal Anda untuk melanjutkan pendaftaran YZA Member.</p></div>
            <div class="region-grid register-region-grid">
                @foreach ([['province_code','province_name','Provinsi','Pilih Provinsi'],['city_code','city_name','Kota / Kabupaten','Pilih Kota / Kabupaten'],['district_code','district_name','Kecamatan','Pilih Kecamatan'],['village_code','village_name','Kelurahan / Desa','Pilih Kelurahan / Desa']] as $region)
                    <div class="form-group register-region-field"><label class="form-label" for="{{ $region[0] }}">{{ $region[2] }} <small>*</small></label><div class="register-input"><select class="form-control region-select" id="{{ $region[0] }}" name="{{ $region[0] }}" data-region-level="{{ $region[0] === 'province_code' ? 'provinces' : ($region[0] === 'city_code' ? 'cities' : ($region[0] === 'district_code' ? 'districts' : 'villages')) }}" required><option value="">{{ $region[3] }}</option></select></div><input type="hidden" id="{{ $region[1] }}" name="{{ $region[1] }}"><p class="form-error" data-region-error="{{ $region[0] }}" hidden></p></div>
                @endforeach
            </div>
            <div class="register-address-summary"><span class="eyebrow">RINGKASAN ALAMAT</span><dl><div><dt>Provinsi</dt><dd data-address-summary="province">—</dd></div><div><dt>Kota/Kabupaten</dt><dd data-address-summary="city">—</dd></div><div><dt>Kecamatan</dt><dd data-address-summary="district">—</dd></div><div><dt>Kelurahan/Desa</dt><dd data-address-summary="village">—</dd></div></dl></div>
            <div class="form-group full register-address-group"><label class="form-label" for="address_detail">Detail Alamat Tambahan <small>(opsional)</small></label><div class="register-input register-textarea"><textarea class="form-control" id="address_detail" name="address_detail" rows="3" maxlength="500" placeholder="Contoh: nama jalan, nomor rumah, RT/RW, blok, kompleks, patokan, atau informasi tambahan lainnya.">{{ old('address_detail') }}</textarea></div><small class="register-field-help">Maksimal 500 karakter.</small></div>
            <div class="form-actions"><button class="button button-secondary" type="button" data-prev-step="2">← Kembali ke Data Diri</button><button class="button button-primary" type="button" data-next-step="2">LANJUT KE MEMBERSHIP&nbsp; →</button></div>
        </div>
        <div class="register-flow-step" data-step="3" hidden>
            <div class="register-membership-summary"><span class="eyebrow">RINGKASAN MEMBERSHIP</span><h3>YZA Member</h3><div class="register-summary-price">Rp {{ number_format((float) app(\App\Services\PengaturanService::class)->membershipFee(), 0, ',', '.') }} <small>sekali aktivasi</small></div><p>Tanpa biaya bulanan. Membership aktif setelah pembayaran berhasil diverifikasi.</p><ul><li>Promo eksklusif</li><li>YZA Points</li><li>Service reminder</li><li>Test drive &amp; kendaraan</li><li>Trade-in terintegrasi</li><li>Akses layanan member</li></ul></div>
            <div class="register-data-summary"><span class="eyebrow">DATA DIRI &amp; ALAMAT</span><div><span>Nama</span><strong data-summary="nama_lengkap">—</strong></div><div><span>Email</span><strong data-summary="email">—</strong></div><div><span>Nomor HP</span><strong data-summary="no_hp">—</strong></div><div><span>Username</span><strong data-summary="username">—</strong></div><div class="register-summary-address"><span>Alamat</span><strong data-summary="address">—</strong></div><div class="register-summary-address"><span>Detail tambahan</span><strong data-summary="address_detail">—</strong></div></div>
            <label class="register-confirm"><input type="checkbox" name="membership_confirmed" value="1" required> Saya memahami bahwa aktivasi YZA Member dikenakan biaya sekali aktivasi sesuai nominal di atas.</label>
            <div class="form-actions"><button class="button button-secondary" type="button" data-prev-step="3">← Kembali ke Alamat</button><button class="button button-primary" type="submit">LANJUT KE PEMBAYARAN&nbsp; →</button></div>
        </div>
    </form>
</main>
@php
    $oldRegionValues = [
        'province_code' => old('province_code'),
        'city_code' => old('city_code'),
        'district_code' => old('district_code'),
        'village_code' => old('village_code'),
    ];
    $oldRegionNames = [
        'province_name' => old('province_name'),
        'city_name' => old('city_name'),
        'district_name' => old('district_name'),
        'village_name' => old('village_name'),
    ];
@endphp
<script>
(() => {
    const form = document.getElementById('register-form');
    const steps = [...document.querySelectorAll('[data-step]')];
    const labels = [...document.querySelectorAll('[data-step-label]')];
    const currentStepInput = document.getElementById('current_step');
    const initialStep = Math.min(3, Math.max(1, Number(currentStepInput?.value || 1)));
    const showStep = (step) => { step = Math.min(3, Math.max(1, Number(step) || 1)); steps.forEach((panel) => { panel.hidden = Number(panel.dataset.step) !== step; panel.classList.toggle('is-active', Number(panel.dataset.step) === step); }); labels.forEach((label) => { const value = Number(label.dataset.stepLabel); label.classList.toggle('active', value === step); label.classList.toggle('complete', value < step); }); if (currentStepInput) currentStepInput.value = String(step); const url = new URL(window.location.href); if (step === 1) url.searchParams.delete('step'); else url.searchParams.set('step', String(step)); window.history.replaceState({}, '', url); };
    const regionSelects = [...document.querySelectorAll('[data-region-level]')];
    const regionNames = { province_code: 'province_name', city_code: 'city_name', district_code: 'district_name', village_code: 'village_name' };
    const summaryNames = { province_code: 'province', city_code: 'city', district_code: 'district', village_code: 'village' };
    const resetChildren = (index) => regionSelects.slice(index + 1).forEach((select) => { select.innerHTML = `<option value="">${select.options[0]?.textContent || 'Pilih wilayah'}</option>`; select.disabled = true; document.getElementById(regionNames[select.id]).value = ''; });
    const loadRegions = async (select, parent) => { select.disabled = true; select.innerHTML = '<option value="">Memuat...</option>'; try { const response = await fetch(`{{ url('/register/regions') }}/${select.dataset.regionLevel}/${parent || ''}`); const payload = await response.json(); select.innerHTML = `<option value="">Pilih ${select.id === 'province_code' ? 'Provinsi' : select.id === 'city_code' ? 'Kota / Kabupaten' : select.id === 'district_code' ? 'Kecamatan' : 'Kelurahan / Desa'}</option>`; payload.data.forEach((item) => { const option = new Option(item.name, item.code); select.add(option); }); select.disabled = payload.data.length === 0; if (!payload.data.length) select.innerHTML = '<option value="">Data wilayah tidak tersedia</option>'; } catch { select.innerHTML = '<option value="">Data wilayah tidak tersedia</option>'; } };
    const updateAddressSummary = () => { regionSelects.forEach((select) => { const target = document.querySelector(`[data-address-summary="${summaryNames[select.id]}"]`); if (target) target.textContent = select.selectedOptions[0]?.value ? select.selectedOptions[0].textContent : '—'; }); };
    regionSelects.forEach((select, index) => select.addEventListener('change', async () => { document.getElementById(regionNames[select.id]).value = select.selectedOptions[0]?.textContent || ''; resetChildren(index); updateAddressSummary(); if (select.value && regionSelects[index + 1]) await loadRegions(regionSelects[index + 1], select.value); }));
    const oldRegionValues = {!! json_encode($oldRegionValues) !!};
    const oldRegionNames = {!! json_encode($oldRegionNames) !!};
    const draftStorageKey = 'yza-registration-draft-v1';
    let storedDraft = {};
    try { storedDraft = initialStep > 1 ? JSON.parse(window.sessionStorage.getItem(draftStorageKey) || '{}') : {}; if (initialStep === 1) window.sessionStorage.removeItem(draftStorageKey); } catch { storedDraft = {}; }
    const draftFields = ['nama_lengkap','no_hp','email','username','jenis_kelamin','tanggal_lahir','province_code','city_code','district_code','village_code','province_name','city_name','district_name','village_name','address_detail'];
    if (initialStep > 1) draftFields.forEach((name) => { const input = document.querySelector(`[name="${name}"]`); if (input && !input.value && storedDraft[name]) input.value = storedDraft[name]; });
    const restoredRegionValues = { ...oldRegionValues, ...Object.fromEntries(['province_code','city_code','district_code','village_code'].filter((name) => storedDraft[name]).map((name) => [name, storedDraft[name]])) };
    const restoredRegionNames = { ...oldRegionNames, ...Object.fromEntries(['province_name','city_name','district_name','village_name'].filter((name) => storedDraft[name]).map((name) => [name, storedDraft[name]])) };
    const persistDraft = () => { const draft = {}; draftFields.forEach((name) => { const input = document.querySelector(`[name="${name}"]`); if (input?.value) draft[name] = input.value; }); try { window.sessionStorage.setItem(draftStorageKey, JSON.stringify(draft)); } catch {} };
    form.addEventListener('input', persistDraft);
    form.addEventListener('change', persistDraft);
    const restoreRegions = async () => { let parent = ''; for (const select of regionSelects) { await loadRegions(select, parent); const value = restoredRegionValues[select.id]; if (!value) break; select.value = value; document.getElementById(regionNames[select.id]).value = restoredRegionNames[regionNames[select.id]] || select.selectedOptions[0]?.textContent || ''; updateAddressSummary(); parent = value; } updatePersonalSummary(); };
    restoreRegions();
    const validAddress = () => { let valid = true; regionSelects.forEach((select) => { const error = document.querySelector(`[data-region-error="${select.id}"]`); if (!select.value) { valid = false; if (error) { error.textContent = `${select.previousElementSibling?.textContent?.replace('*', '').trim() || 'Wilayah'} wajib dipilih.`; error.hidden = false; } } else if (error) error.hidden = true; }); return valid; };
    const validateStep = (step) => {
        const panel = steps.find((candidate) => Number(candidate.dataset.step) === step);
        if (!panel) return true;
        const controls = [...panel.querySelectorAll('input, select, textarea')].filter((control) => !control.disabled);
        const firstInvalid = controls.find((control) => !control.checkValidity());
        controls.forEach((control) => control.toggleAttribute('aria-invalid', !control.checkValidity()));
        if (firstInvalid) {
            firstInvalid.focus({ preventScroll: true });
            firstInvalid.reportValidity();
            return false;
        }
        return true;
    };
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    const syncPasswordValidity = () => {
        if (!password || !passwordConfirmation) return;
        passwordConfirmation.setCustomValidity(passwordConfirmation.value && passwordConfirmation.value !== password.value ? 'Konfirmasi password tidak sama.' : '');
    };
    password?.addEventListener('input', syncPasswordValidity);
    passwordConfirmation?.addEventListener('input', syncPasswordValidity);
    document.querySelector('[data-next-step="1"]')?.addEventListener('click', () => { syncPasswordValidity(); if (!validateStep(1)) return; showStep(2); });
    const updatePersonalSummary = () => { ['nama_lengkap','email','no_hp','username'].forEach((name) => { const target = document.querySelector(`[data-summary="${name}"]`); const input = document.getElementById(name); if (target) target.textContent = input?.value || '—'; }); const addressTarget = document.querySelector('[data-summary="address"]'); const detailTarget = document.querySelector('[data-summary="address_detail"]'); if (addressTarget) addressTarget.textContent = regionSelects.map((select) => select.selectedOptions[0]?.textContent).filter(Boolean).reverse().join(', ') || '—'; if (detailTarget) detailTarget.textContent = document.getElementById('address_detail')?.value || 'Tidak ada'; };
    document.querySelector('[data-next-step="2"]')?.addEventListener('click', () => { if (!validAddress()) return; updatePersonalSummary(); showStep(3); });
    document.querySelector('[data-prev-step="2"]')?.addEventListener('click', () => showStep(1));
    document.querySelector('[data-prev-step="3"]')?.addEventListener('click', () => showStep(2));
    form.addEventListener('submit', (event) => { if (!document.querySelector('[data-step="3"]')?.classList.contains('is-active') || !form.reportValidity() || !validAddress()) event.preventDefault(); });
    document.querySelectorAll('.password-toggle').forEach((button) => button.addEventListener('click', () => { const input = document.getElementById(button.dataset.target); input.type = input.type === 'password' ? 'text' : 'password'; button.setAttribute('aria-label', input.type === 'password' ? 'Tampilkan password' : 'Sembunyikan password'); }));
    updatePersonalSummary();
    showStep(initialStep);
})();
</script>
</body>
</html>
