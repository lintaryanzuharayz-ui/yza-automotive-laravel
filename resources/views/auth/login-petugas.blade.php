<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Portal Petugas & Admin · YZA Automotive</title>
    <link rel="icon" href="{{ asset('assets/images/branding/logo-yza.png') }}">
    @foreach (['variables.css','base.css','components.css','pages/login.css'] as $style)
        <link rel="stylesheet" href="{{ asset('assets/css/'.$style) }}">
    @endforeach
    <style>
        .staff-role-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 14px 0 20px;
        }
        .staff-tag {
            display: inline-flex;
            align-items: center;
            background: #191b20;
            color: #c6c8ce;
            border: 1px solid #2d3139;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .staff-tag.highlight {
            border-color: #ef1b2d;
            color: #fff;
        }
    </style>
</head>
<body>
    <main class="login-page">
        <section class="login-brand">
            <div class="login-brand-inner">
                <a class="brand" href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive">
                </a>
                <div class="metal-line"></div>
                <h1>YZA INTERNAL</h1>
                <p>Portal Sistem Informasi & Komando Operasional YZA Automotive Dealer & Workshop.</p>
            </div>
        </section>

        <section class="login-form-side">
            <form class="login-form" method="POST" action="{{ route('petugas.login') }}" autocomplete="off" novalidate>
                @csrf
                <a class="login-home-link" href="{{ url('/') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"></path></svg>
                    <span>Kembali ke Beranda</span>
                </a>

                <span class="eyebrow" style="color:#ef1b2d;">YZA Operations & Admin Portal</span>
                <h2>Masuk Petugas & Admin</h2>
                <p class="intro">Akses khusus staf internal untuk Service Command Center, Workshop, Gudang, Finance, dan Administrasi Dealer.</p>

                <div class="staff-role-tags" aria-label="Divisi yang didukung">
                    <span class="staff-tag highlight">Service Advisor</span>
                    <span class="staff-tag highlight">Mekanik</span>
                    <span class="staff-tag">Staf Gudang</span>
                    <span class="staff-tag">Finance</span>
                    <span class="staff-tag">Operasional / QC</span>
                    <span class="staff-tag">Sales</span>
                    <span class="staff-tag">Admin & Owner</span>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="identity">Username atau Email Petugas</label>
                    <div class="input-with-icon">
                        <svg class="field-icon" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="3.2"></circle>
                            <path d="M5.5 19c.7-3.1 2.9-4.7 6.5-4.7s5.8 1.6 6.5 4.7"></path>
                        </svg>
                        <input class="form-control" id="identity" name="identity" type="text" value="{{ old('identity') }}" autocomplete="username" placeholder="misal: advisor506 atau admin506" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-field">
                        <svg class="field-icon" viewBox="0 0 24 24">
                            <rect x="5.5" y="10.5" width="13" height="9" rx="1.8"></rect>
                            <path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"></path>
                        </svg>
                        <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" aria-label="Tampilkan password">
                            <svg viewBox="0 0 24 24">
                                <path d="M2.5 12s3.4-5 9.5-5 9.5 5 9.5 5-3.4 5-9.5 5-9.5-5-9.5-5Z"></path>
                                <circle cx="12" cy="12" r="2.5"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="login-form-actions">
                    <button class="button button-primary login-submit" type="submit" style="background:#ef1b2d;border-color:#ef1b2d;">
                        <span>MASUK SEBAGAI PETUGAS</span>
                        <svg viewBox="0 0 24 24"><path d="M5 12h13M13 6l6 6-6 6"></path></svg>
                    </button>
                </div>

                <p class="login-register">Bukan petugas internal? <a href="{{ route('login') }}">Masuk Portal Member</a></p>
            </form>
        </section>
    </main>

    <script>
        const p = document.getElementById('password'),
              t = document.querySelector('.password-toggle');
        t?.addEventListener('click', () => {
            p.type = p.type === 'password' ? 'text' : 'password';
            t.setAttribute('aria-label', p.type === 'password' ? 'Tampilkan password' : 'Sembunyikan password');
        });

        // Selalu muat ulang dari server jika dibuka lewat history back browser agar token CSRF tidak stale
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>

