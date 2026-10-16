@php
    $adminSections = [
        ['title' => 'Overview', 'items' => [
            ['label' => 'Pusat Notifikasi', 'icon' => '!', 'route' => 'admin.notifikasi.index', 'badge' => 'notifications'],
            ['label' => 'Aktivitas & Tugas Admin', 'icon' => '◌', 'route' => 'admin.notifikasi.index'],
        ]],
        ['title' => 'Penjualan Mobil', 'items' => [
            ['label' => 'Pesanan Mobil', 'icon' => 'car', 'route' => 'admin.pesanan-mobil.index', 'badge' => 'orders'],
            ['label' => 'Penjualan Mobil', 'icon' => 'car', 'route' => 'admin.penjualan-mobil.index'],
            ['label' => 'Pre-Order', 'icon' => '◷', 'route' => 'admin.preorder-mobil.index', 'badge' => 'preorders'],
            ['label' => 'Inventori & Stok Mobil', 'icon' => 'box', 'route' => 'admin.mobil.index', 'badge' => 'stock'],
            ['label' => 'Katalog & Showroom', 'icon' => '◇', 'route' => 'admin.mobil.index'],
            ['label' => 'PDI', 'icon' => '✓', 'route' => 'admin.pesanan-mobil.index'],
            ['label' => 'Jadwal Pengambilan', 'icon' => '◫', 'route' => 'admin.pesanan-mobil.index'],
            ['label' => 'Serah Terima', 'icon' => '⇄', 'route' => 'admin.pesanan-mobil.index'],
        ]],
        ['title' => 'Keuangan', 'items' => [
            ['label' => 'Dashboard Keuangan', 'icon' => 'Rp', 'route' => 'admin.payments.index'],
            ['label' => 'Pembayaran Online / Offline', 'icon' => 'card', 'route' => 'admin.payments.index', 'badge' => 'payments'],
            ['label' => 'Semua Transaksi', 'icon' => '≡', 'route' => 'admin.penjualan-sparepart.index'],
            ['label' => 'Invoice & Kwitansi', 'icon' => '▧', 'route' => 'admin.penjualan-mobil.index'],
            ['label' => 'Rekonsiliasi / Pencairan', 'icon' => '⇆', 'route' => 'admin.pesanan-mobil.index'],
        ]],
        ['title' => 'Layanan & Operasional', 'items' => [
            ['label' => 'Booking Mobil', 'icon' => '▣', 'route' => 'admin.booking.index', 'badge' => 'bookings'],
            ['label' => 'Test Drive', 'icon' => '◉', 'route' => 'admin.test-drive.index', 'badge' => 'testDrives'],
            ['label' => 'Servis / Work Order', 'icon' => '⚙', 'route' => 'admin.servis.index'],
            ['label' => 'Jadwal Bengkel', 'icon' => '◫', 'route' => 'admin.servis.schedule'],
            ['label' => 'Personel Servis', 'icon' => '♙', 'route' => 'admin.servis.personnel'],
            ['label' => 'Car Wash', 'icon' => '✦', 'route' => 'admin.booking-cuci.index'],
            ['label' => 'Sparepart & Inventori', 'icon' => '▦', 'route' => 'admin.sparepart.index'],
            ['label' => 'Trade-In', 'icon' => '⇄', 'route' => 'admin.trade-in.index', 'badge' => 'tradeIns'],
            ['label' => 'Pembelian Mobil Bekas', 'icon' => '▰', 'route' => 'admin.pengajuan-mobil.index'],
        ]],
        ['title' => 'Pelanggan & Membership', 'items' => [
            ['label' => 'Data Pelanggan', 'icon' => '♙', 'route' => 'admin.pelanggan.index'],
            ['label' => 'Data Member', 'icon' => '♚', 'route' => 'admin.membership.index'],
            ['label' => 'My Garage', 'icon' => '▱', 'route' => 'admin.pelanggan.index'],
            ['label' => 'Promo & YZA Points', 'icon' => '%', 'route' => 'admin.promo-member.index'],
        ]],
        ['title' => 'Manajemen Perusahaan', 'items' => [
            ['label' => 'Manajemen Karyawan', 'icon' => '♟', 'route' => 'admin.petugas.index'],
            ['label' => 'Role & Permission', 'icon' => '♟', 'route' => 'admin.petugas.index'],
            ['label' => 'Laporan Penjualan', 'icon' => '▥', 'route' => 'admin.penjualan-mobil.index'],
            ['label' => 'Laporan Keuangan', 'icon' => '▥', 'route' => 'admin.penjualan-mobil.index'],
            ['label' => 'Laporan Operasional', 'icon' => '▥', 'route' => 'admin.laporan-servis.index'],
            ['label' => 'Audit Log', 'icon' => '⌁', 'route' => 'admin.notifikasi.index'],
            ['label' => 'Pengaturan Website', 'icon' => '⚙', 'route' => 'admin.pengaturan.index'],
            ['label' => 'Profil Admin', 'icon' => '●', 'route' => 'admin.pengaturan.index'],
        ]],
    ];
@endphp
<aside class="admin-sidebar" data-admin-sidebar aria-label="Navigasi admin">
    <a class="admin-sidebar-brand" href="{{ $adminUser->roleKey() === 'sales' ? route('sales.dashboard') : ($adminUser->roleKey() === 'owner' ? route('owner.dashboard') : route('admin.dashboard')) }}" aria-label="Ke Dashboard YZA">
        <img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive">
        <span><strong>YZA AUTOMOTIVE</strong><small>Admin Management</small></span>
    </a>
    @if($adminUser->roleKey() === 'super_admin')
    <nav class="admin-nav">
        @foreach($adminSections as $section)
            <div class="admin-nav-section">
                <div class="admin-nav-label">{{ $section['title'] }}</div>
                @foreach($section['items'] as $item)
                    @if(!empty($item['disabled']) || !Route::has($item['route']))
                        <span class="admin-nav-item is-disabled">
                            <i>
                                @if($item['icon'] === 'car')
                                    <svg class="admin-nav-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                                @elseif($item['icon'] === 'card')
                                    <svg class="admin-nav-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                @else
                                    {{ $item['icon'] }}
                                @endif
                            </i>
                            <span>{{ $item['label'] }}</span>
                        </span>
                    @else
                        @php
                            $fallbackLabels = ['Aktivitas & Tugas Admin', 'Katalog & Showroom', 'PDI', 'Jadwal Pengambilan', 'Serah Terima', 'Dashboard Keuangan', 'Invoice & Kwitansi', 'Rekonsiliasi / Pencairan', 'My Garage', 'User & Role', 'Laporan Penjualan', 'Laporan Keuangan', 'Audit Log', 'Profil Admin'];
                            $isAdminNavActive = !in_array($item['label'], $fallbackLabels, true) && request()->routeIs($item['route']);
                        @endphp
                        <a class="admin-nav-item {{ $isAdminNavActive ? 'is-active' : '' }}" href="{{ route($item['route']) }}">
                            <i>
                                @if($item['icon'] === 'car')
                                    <svg class="admin-nav-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                                @elseif($item['icon'] === 'card')
                                    <svg class="admin-nav-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                @elseif($item['icon'] === 'box')
                                    <svg class="admin-nav-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                @else
                                    {{ $item['icon'] }}
                                @endif
                            </i>
                            <span>{{ $item['label'] }}</span>
                            @if(isset($item['badge']) && ($adminBadges[$item['badge']] ?? 0) > 0)
                                <b class="admin-nav-badge">{{ ($adminBadges[$item['badge']] ?? 0) > 99 ? '99+' : $adminBadges[$item['badge']] }}</b>
                            @endif
                            @if($isAdminNavActive)
                                <span class="admin-nav-chevron" aria-hidden="true">›</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>
    @else
    <nav class="admin-nav" aria-label="Navigasi role">
        <div class="admin-nav-section"><div class="admin-nav-label">Ruang Kerja</div><a class="admin-nav-item is-active" href="{{ $adminUser->roleKey() === 'owner' ? route('owner.dashboard') : route('staff.dashboard') }}"><i>⌂</i><span>Dashboard</span></a>
            @php
                $roleLinks = [
                    'owner' => [['Executive dashboard','owner.dashboard','reports.view'],['Sales Excellence Awards','owner.sales-awards','reports.view'],['Rekap penjualan','owner.sales-report','reports.view'],['Performa Sales','owner.performance-report','reports.view'],['Laporan Servis','owner.service-report','reports.view'],['Pendapatan & Pembayaran','owner.payment-report','reports.view'],['Inventori Kendaraan','owner.inventory-report','reports.view']],
                    'sales' => [['Dashboard Sales','sales.dashboard','sales.view'],['Sales Excellence Awards','sales.awards','sales.view'],['Prospek pelanggan','sales.leads.index','sales.view'],['Jadwal test drive','sales.test-drives.index','sales.view'],['Pesanan mobil','sales.orders.index','sales.view'],['Penjualan saya','sales.sales.index','sales.view'],['Target penjualan','sales.targets.index','sales.view']],
                    'service_advisor' => [['Booking servis','admin.booking-servis.index','service.view'],['Work Order','admin.servis.index','service.manage']],
                    'mechanic' => [['Pekerjaan servis','admin.servis.index','service.assigned'],['Sparepart','admin.sparepart.index','inventory.view']],
                    'warehouse_staff' => [['Sparepart','admin.sparepart.index','inventory.view'],['Stok masuk','admin.stok-masuk.index','inventory.manage']],
                    'finance' => [['Pembayaran','admin.payments.index','finance.view'],['Refund & Reversal','admin.financial-adjustments.index','finance.view'],['Laporan keuangan','admin.payments.index','reports.view']],
                    'operational_staff' => [['Booking mobil','admin.booking.index','service.view'],['Test drive','admin.test-drive.index','service.view']],
                    'quality_control' => [['QC / PDI','admin.servis.index','qc.manage']],
                ][$adminUser->roleKey()] ?? [];
            @endphp
            @foreach($roleLinks as [$label, $routeName, $permission])
                @if(Route::has($routeName) && $adminUser->hasPermission($permission))<a class="admin-nav-item" href="{{ route($routeName) }}"><i>•</i><span>{{ $label }}</span></a>@endif
            @endforeach
        </div>
    </nav>
    @endif
</aside>
