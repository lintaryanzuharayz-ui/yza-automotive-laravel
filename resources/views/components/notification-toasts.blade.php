@auth
    @unless(request()->routeIs('login', 'register', 'password.*'))
        @php
            $toastRole = auth()->user()->roleName() === 'admin' ? 'admin' : auth()->user()->roleKey();
            $toastGreetings = [
                'super_admin' => ['Selamat Bertugas, Super Admin', 'Pantau seluruh aktivitas operasional hari ini'],
                'admin' => ['Selamat Bertugas, Administrator', 'Pantau aktivitas operasional hari ini'],
                'owner' => ['Selamat Datang Kembali, Owner', 'Pantau performa bisnis YZA Automotive'],
                'sales' => ['Selamat Bertugas, Sales', 'Pantau prospek dan perkembangan penjualan'],
                'service_advisor' => ['Selamat Bertugas, Service Advisor', 'Kelola jadwal dan pelayanan servis pelanggan'],
                'mechanic' => ['Selamat Bertugas, Mekanik', 'Periksa penugasan dan progres pekerjaan Anda'],
                'warehouse_staff' => ['Selamat Bertugas, Petugas Gudang', 'Pantau stok dan permintaan sparepart'],
                'finance' => ['Selamat Bertugas, Tim Finance', 'Pantau pembayaran dan verifikasi transaksi'],
                'operational_staff' => ['Selamat Bertugas, Tim Operasional', 'Kelola jadwal dan aktivitas operasional hari ini'],
                'quality_control' => ['Selamat Bertugas, Tim Quality Control', 'Pantau pekerjaan yang memerlukan pemeriksaan'],
                'pelanggan' => ['Selamat Datang Kembali', 'Nikmati layanan premium YZA Automotive'],
                'petugas' => ['Selamat Bertugas', 'Pantau aktivitas operasional hari ini'],
            ];
            [$toastTitle, $toastMessage] = $toastGreetings[$toastRole] ?? ['Selamat Bertugas', 'Pantau aktivitas Anda hari ini'];
            $toastWelcome = (string) session('yza_toast_welcome_user') === (string) auth()->id();
            if ($toastWelcome) session()->forget('yza_toast_welcome_user');
        @endphp
        <link rel="stylesheet" href="{{ asset('assets/css/notification-toasts.css') }}?v={{ filemtime(public_path('assets/css/notification-toasts.css')) }}">
        <div class="yza-toast-stack" data-yza-toast-stack data-account="{{ auth()->id() }}"
             data-welcome="{{ $toastWelcome ? 'true' : 'false' }}" data-member="{{ $toastRole === 'pelanggan' ? 'true' : 'false' }}"
             data-title="{{ $toastTitle }}" data-message="{{ $toastMessage }}" aria-live="polite" aria-relevant="additions"></div>
        <script src="{{ asset('assets/js/notification-toasts.js') }}?v={{ filemtime(public_path('assets/js/notification-toasts.js')) }}"></script>
    @endunless
@endauth
