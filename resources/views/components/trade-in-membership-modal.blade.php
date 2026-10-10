@props(['entryUrl', 'membershipUrl', 'isActiveMember' => false])

@if(!$isActiveMember)
    <div class="trade-in-gate" data-trade-in-gate hidden>
        <div class="trade-in-gate-backdrop" data-trade-in-close></div>
        <section class="trade-in-gate-dialog" role="dialog" aria-modal="true" aria-labelledby="trade-in-gate-title" aria-describedby="trade-in-gate-description" tabindex="-1">
            <button class="trade-in-gate-close" type="button" aria-label="Tutup" data-trade-in-close>×</button>
            <div class="trade-in-gate-logo"><img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive"></div>
            <span class="trade-in-gate-eyebrow">YZA MEMBER EXCLUSIVE</span>
            <h2 id="trade-in-gate-title">Trade-In Eksklusif untuk <em>YZA Member</em></h2>
            <p id="trade-in-gate-description">Tukarkan kendaraan lama Anda dan dapatkan penawaran terbaik untuk mobil pilihan di YZA Automotive. Aktifkan YZA Member untuk mengakses layanan Trade-In / Tukar Tambah dan keuntungan eksklusif lainnya.</p>
            <ul class="trade-in-gate-benefits">
                <li>Akses pengajuan Trade-In</li>
                <li>Penilaian kendaraan lebih terstruktur</li>
                <li>Penawaran khusus YZA Member</li>
            </ul>
            <div class="trade-in-gate-actions">
                <a class="trade-in-gate-primary" href="{{ $membershipUrl }}">Jadi YZA Member <span aria-hidden="true">→</span></a>
                <a class="trade-in-gate-secondary" href="{{ yza_whatsapp_url('Halo YZA Automotive, saya ingin konsultasi mengenai program Trade-In YZA.') }}" target="_blank" rel="noopener">Konsultasi via WhatsApp</a>
            </div>
        </section>
    </div>
    <style>
        .trade-in-gate{position:fixed;inset:0;z-index:50;display:grid;place-items:center;padding:24px}.trade-in-gate[hidden]{display:none}.trade-in-gate-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.72);backdrop-filter:blur(12px)}.trade-in-gate-dialog{position:relative;width:min(100%,570px);max-height:calc(100vh - 48px);overflow:auto;padding:48px 46px 40px;border:1px solid rgba(242,30,42,.42);border-radius:20px;background:radial-gradient(circle at 100% 0,rgba(114,13,24,.38),transparent 45%),linear-gradient(145deg,#1c1719,#0b0d10 78%);box-shadow:0 30px 100px rgba(0,0,0,.6),0 0 42px rgba(242,30,42,.1);color:#f7f7f4;animation:tradeGateIn .28s cubic-bezier(.2,.8,.2,1)}.trade-in-gate-close{position:absolute;top:16px;right:18px;width:36px;height:36px;border:1px solid rgba(255,255,255,.2);border-radius:10px;background:rgba(255,255,255,.04);color:#fff;font-size:25px;line-height:1;cursor:pointer}.trade-in-gate-close:hover{border-color:#f21e2a;background:rgba(242,30,42,.16)}.trade-in-gate-icon{display:grid;place-items:center;width:58px;height:58px;margin-bottom:20px;border:1px solid rgba(242,30,42,.55);border-radius:16px;background:linear-gradient(145deg,rgba(242,30,42,.28),rgba(63,8,14,.6));color:#ff4a55;font-size:35px;box-shadow:0 10px 28px rgba(242,30,42,.18)}.trade-in-gate-eyebrow{color:#ff4a55;font-size:.67rem;font-weight:800;letter-spacing:.22em}.trade-in-gate-dialog h2{margin:13px 0 14px;font-size:clamp(1.8rem,4vw,2.55rem);line-height:1.08;letter-spacing:-.035em}.trade-in-gate-dialog h2 em{color:#ff3a47;font-style:normal}.trade-in-gate-dialog p{margin:0;color:#bab9b7;font-size:.94rem;line-height:1.7}.trade-in-gate-benefits{display:grid;gap:11px;margin:25px 0 31px;padding:0;list-style:none;color:#e5e4e1;font-size:.86rem}.trade-in-gate-benefits li{display:flex;align-items:flex-start;gap:10px}.trade-in-gate-benefits li::before{content:'✓';display:grid;place-items:center;width:19px;height:19px;flex:none;border-radius:50%;background:rgba(242,30,42,.18);color:#ff4a55;font-weight:800}.trade-in-gate-actions{display:grid;gap:11px}.trade-in-gate-primary,.trade-in-gate-secondary{display:flex;align-items:center;justify-content:center;min-height:52px;border-radius:10px;font-size:.9rem;font-weight:800;cursor:pointer;transition:transform .2s,background .2s,border-color .2s,box-shadow .2s}.trade-in-gate-primary{border:1px solid #f21e2a;background:#f21e2a;color:#fff;box-shadow:0 12px 28px rgba(242,30,42,.22)}.trade-in-gate-primary span{margin-left:auto;margin-right:17px;font-size:1.2rem}.trade-in-gate-primary:hover{background:#ff303b;transform:translateY(-1px);box-shadow:0 15px 32px rgba(242,30,42,.32)}.trade-in-gate-secondary{border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.03);color:#d5d4d1}.trade-in-gate-secondary:hover{border-color:rgba(255,255,255,.45);background:rgba(255,255,255,.08)}@keyframes tradeGateIn{from{opacity:0;transform:translateY(16px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}@media (max-width:600px){.trade-in-gate{padding:16px}.trade-in-gate-dialog{max-height:calc(100vh - 32px);padding:42px 24px 25px;border-radius:16px}.trade-in-gate-dialog h2{font-size:1.85rem}.trade-in-gate-dialog p{font-size:.86rem}.trade-in-gate-icon{width:50px;height:50px;font-size:30px}}@media (prefers-reduced-motion:reduce){.trade-in-gate-dialog{animation:none}.trade-in-gate-primary,.trade-in-gate-secondary,.trade-in-gate-close{transition:none}}
    </style>
    <style>
        .trade-in-gate-dialog{width:min(100%,650px);padding:56px 58px 48px}
        .trade-in-gate-icon{width:64px;height:64px;margin-bottom:24px;font-size:39px}
        .trade-in-gate-eyebrow{font-size:.75rem;letter-spacing:.25em}
        .trade-in-gate-dialog h2{max-width:520px;margin:16px 0 20px;font-size:clamp(2.35rem,4.5vw,3.25rem);line-height:1.04;letter-spacing:-.045em}
        .trade-in-gate-dialog p{max-width:530px;font-size:1.04rem;line-height:1.75}
        .trade-in-gate-benefits{gap:14px;margin:30px 0 36px;font-size:.98rem}
        .trade-in-gate-benefits li{gap:12px;line-height:1.4}
        .trade-in-gate-benefits li::before{width:21px;height:21px}
        .trade-in-gate-primary,.trade-in-gate-secondary{min-height:56px;font-size:1rem}
        .trade-in-gate-primary{position:relative;justify-content:center;text-align:center;padding:0 58px 0 24px}
        .trade-in-gate-primary span{position:absolute;right:18px;margin:0}
        .trade-in-gate-secondary{min-height:52px}
        .trade-in-gate-logo{display:flex;align-items:center;justify-content:center;width:108px;height:92px;margin:-8px 0 18px -10px;background:transparent;border:0;border-radius:0;box-shadow:none}
        .trade-in-gate-logo img{display:block;width:100%;height:100%;object-fit:contain;background:transparent;border:0;border-radius:0;box-shadow:none}
        @media (max-width:600px){.trade-in-gate-dialog{padding:44px 26px 28px}.trade-in-gate-logo{width:92px;height:78px;margin:-6px 0 16px -8px}.trade-in-gate-dialog h2{font-size:2.15rem}.trade-in-gate-dialog p{font-size:.95rem;line-height:1.65}.trade-in-gate-benefits{font-size:.91rem}}
    </style>
    <script>
        (() => {
            const gate = document.querySelector('[data-trade-in-gate]');
            const triggers = [...document.querySelectorAll('[data-trade-in-trigger]')];
            if (!gate || !triggers.length) return;
            const dialog = gate.querySelector('.trade-in-gate-dialog');
            let returnFocus = triggers[0];
            const close = () => { gate.hidden = true; document.body.style.overflow = ''; returnFocus?.focus(); };
            const open = (event) => { event.preventDefault(); gate.hidden = false; document.body.style.overflow = 'hidden'; dialog.focus(); };
            triggers.forEach((trigger) => trigger.addEventListener('click', (event) => { returnFocus = trigger; open(event); }));
            gate.querySelectorAll('[data-trade-in-close]').forEach((button) => button.addEventListener('click', close));
            document.addEventListener('keydown', (event) => { if (!gate.hidden && event.key === 'Escape') close(); });
        })();
    </script>
@endif
