@php
    $adminSections = [
        ['title' => 'Overview', 'items' => [
            ['label' => 'Dashboard Utama', 'icon' => '⌂', 'route' => 'admin.dashboard'],
            ['label' => 'Pusat Notifikasi', 'icon' => '!', 'route' => 'admin.notifikasi.index', 'badge' => 'notifications'],
            ['label' => 'Aktivitas & Tugas Admin', 'icon' => '◌', 'route' => 'admin.notifikasi.index'],
        ]],
        ['title' => 'Penjualan Mobil', 'items' => [
            ['label' => 'Pesanan Mobil', 'icon' => '▣', 'route' => 'admin.pesanan-mobil.index', 'badge' => 'orders'],
            ['label' => 'Penjualan Mobil', 'icon' => '↗', 'route' => 'admin.penjualan-mobil.index'],
            ['label' => 'Pre-Order', 'icon' => '◷', 'route' => 'admin.preorder-mobil.index', 'badge' => 'preorders'],
            ['label' => 'Inventori & Stok Mobil', 'icon' => '▤', 'route' => 'admin.mobil.index', 'badge' => 'stock'],
            ['label' => 'Katalog & Showroom', 'icon' => '◇', 'route' => 'admin.mobil.index'],
            ['label' => 'PDI', 'icon' => '✓', 'route' => 'admin.pesanan-mobil.index'],
            ['label' => 'Jadwal Pengambilan', 'icon' => '◫', 'route' => 'admin.pesanan-mobil.index'],
            ['label' => 'Serah Terima', 'icon' => '⇄', 'route' => 'admin.pesanan-mobil.index'],
        ]],
        ['title' => 'Keuangan', 'items' => [
            ['label' => 'Dashboard Keuangan', 'icon' => 'Rp', 'route' => 'admin.penjualan-mobil.index'],
            ['label' => 'Pembayaran Online / Offline', 'icon' => '▥', 'route' => 'admin.pesanan-mobil.index', 'badge' => 'payments'],
            ['label' => 'Semua Transaksi', 'icon' => '≡', 'route' => 'admin.penjualan-sparepart.index'],
            ['label' => 'Invoice & Kwitansi', 'icon' => '▧', 'route' => 'admin.penjualan-mobil.index'],
            ['label' => 'Rekonsiliasi / Pencairan', 'icon' => '⇆', 'route' => 'admin.pesanan-mobil.index'],
        ]],
        ['title' => 'Layanan & Operasional', 'items' => [
            ['label' => 'Booking Mobil', 'icon' => '▣', 'route' => 'admin.booking.index', 'badge' => 'bookings'],
            ['label' => 'Test Drive', 'icon' => '◉', 'route' => 'admin.test-drive.index', 'badge' => 'testDrives'],
            ['label' => 'Servis / Work Order', 'icon' => '⚙', 'route' => 'admin.servis.index'],
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
            ['label' => 'Petugas', 'icon' => '♟', 'route' => 'admin.petugas.index'],
            ['label' => 'User & Role', 'icon' => '♟', 'route' => 'admin.petugas.index'],
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
    <div class="admin-sidebar-brand">
        <img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive">
        <span><strong>YZA AUTOMOTIVE</strong><small>Admin Management</small></span>
    </div>
    <nav class="admin-nav">
        @foreach($adminSections as $section)
            <div class="admin-nav-section">
                <div class="admin-nav-label">{{ $section['title'] }}</div>
                @foreach($section['items'] as $item)
                    @if(!empty($item['disabled']) || !Route::has($item['route']))
                        <span class="admin-nav-item is-disabled"><i>{{ $item['icon'] }}</i><span>{{ $item['label'] }}</span></span>
                    @else
                        <a class="admin-nav-item {{ request()->routeIs($item['route']) ? 'is-active' : '' }}" href="{{ route($item['route']) }}">
                            <i>{{ $item['icon'] }}</i><span>{{ $item['label'] }}</span>@if(isset($item['badge']) && ($adminBadges[$item['badge']] ?? 0) > 0)<b class="admin-nav-badge">{{ ($adminBadges[$item['badge']] ?? 0) > 99 ? '99+' : $adminBadges[$item['badge']] }}</b>@endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>
</aside>
