<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\FotoMobilController as AdminFotoMobilController;
use App\Http\Controllers\Admin\KategoriSparepartController;
use App\Http\Controllers\Admin\JenisServisController;
use App\Http\Controllers\Admin\PaketCuciController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PelangganController as AdminPelangganController;
use App\Http\Controllers\Admin\PetugasController as AdminPetugasController;
use App\Http\Controllers\Admin\MobilController as AdminMobilController;
use App\Http\Controllers\Admin\SparepartController as AdminSparepartController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Petugas\SparepartController as PetugasSparepartController;
use App\Http\Controllers\Petugas\MobilController as PetugasMobilController;
use App\Http\Controllers\Public\MobilController as PublicMobilController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Pelanggan\KendaraanController;
use App\Http\Controllers\Pelanggan\MemberVehiclePurchaseController as MemberVehiclePurchaseController;
use App\Http\Controllers\Admin\MemberVehiclePurchaseController as AdminMemberVehiclePurchaseController;
use App\Services\PengaturanService;
use App\Http\Controllers\Admin\{StokMasukController,InventoryController,NotifikasiController as AdminNotifikasiController};
use App\Http\Controllers\Admin\MemberVehiclePreorderController as AdminMemberVehiclePreorderController;
use App\Http\Controllers\Pelanggan\{BookingMobilController,TestDriveController};
use App\Http\Controllers\Pelanggan\MemberVehiclePreorderController;
use App\Http\Controllers\Pelanggan\PengajuanMobilController;
use App\Http\Controllers\{OperationsBookingController,OperationsTestDriveController};
use App\Http\Controllers\OperationsPengajuanMobilController;
use App\Http\Controllers\Pelanggan\BookingServisController;
use App\Http\Controllers\OperationsBookingServisController;
use App\Http\Controllers\Pelanggan\BookingCuciController;
use App\Http\Controllers\Pelanggan\ServisController as PelangganServisController;
use App\Http\Controllers\OperationsBookingCuciController;
use App\Http\Controllers\OperationsServisController;
use App\Http\Controllers\{PaymentController,InvoiceController};
use App\Http\Controllers\{OutgoingPaymentController,UsedCarSettlementController,UsedCarReceiptController,TradeInInvoiceController};
use App\Http\Controllers\Pelanggan\TradeInController;
use App\Http\Controllers\Pelanggan\PointController;
use App\Http\Controllers\Pelanggan\PromoMemberController as PelangganPromoMemberController;
use App\Http\Controllers\Pelanggan\PromoPreviewController;
use App\Http\Controllers\Pelanggan\ServiceReminderController;
use App\Http\Controllers\Admin\PromoMemberController as AdminPromoMemberController;
use App\Http\Controllers\Admin\PromoSimulationController;
use App\Http\Controllers\OperationsTradeInController;
use App\Http\Controllers\{CarSalePaymentController,CarSaleInvoiceController};
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\Admin\MembershipController as AdminMembershipController;
use App\Http\Controllers\Admin\PromoAnalyticsController;
use App\Http\Controllers\Admin\LaporanServisController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\OperationsPenjualanSparepartController;
use App\Http\Controllers\OperationsPenjualanMobilController;
use Illuminate\Support\Facades\Route;
use App\Models\Mobil;

Route::get('/', function () {
    $cars = Mobil::query()->whereIn('status', ['tersedia', 'dibooking'])->with('foto')->orderByDesc('id_mobil')->limit(6)->get();
    $featuredHeroCar = Mobil::query()->where('kode_mobil', 'YZA-TOYOTA-GR-SUPRA-2024')->whereIn('status', ['tersedia', 'dibooking'])->with('foto')->first();
    $featuredCars = app(PublicMobilController::class)->sportShowcases()->map(function (Mobil $vehicle): array {
        return [
            'id' => $vehicle->id_mobil,
            'name' => trim($vehicle->merk.' '.$vehicle->model).($vehicle->tipe ? ' · '.$vehicle->tipe : ''),
            'specification' => collect([$vehicle->tahun, $vehicle->tipe, $vehicle->transmisi ? ucfirst($vehicle->transmisi) : null])->filter()->implode(' · '),
            'price' => (float) $vehicle->harga_jual,
            'photo' => $vehicle->image,
            'brand' => $vehicle->merk,
            'condition' => strtoupper($vehicle->kondisi === 'bekas' ? 'BEKAS' : 'BARU'),
            'isSport' => true,
            'url' => route('mobil.showcase', ['slug' => $vehicle->showcase_slug, 'source' => 'landing']),
        ];
    });

    $membershipFee = app(PengaturanService::class)->membershipFee();
    $landingMemberState = 'guest';
    if (auth()->check()) {
        $landingMemberState = auth()->user()->roleName() === 'pelanggan'
            ? (auth()->user()->pelanggan?->membership?->status ?? 'guest')
            : 'staff';
    }

    return view('public.home', compact('cars', 'featuredHeroCar', 'featuredCars', 'membershipFee', 'landingMemberState'));
});

Route::get('/mobil', [PublicMobilController::class, 'index'])->name('mobil.index');
Route::get('/koleksi/{slug}', [PublicMobilController::class, 'showcase'])->name('mobil.showcase');
Route::get('/mobil/{mobil}', [PublicMobilController::class, 'show'])->whereNumber('mobil')->name('mobil.show');
Route::get('/trade-in/start', [PublicMobilController::class, 'tradeInEntry'])->name('trade-in.entry');
Route::get('/trade-in/membership-entry', [PublicMobilController::class, 'membershipEntry'])->name('trade-in.membership-entry');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::get('/register/regions/{level}/{parent?}', [RegistrationController::class, 'regions'])->name('register.regions');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
Route::post('/payment/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');

Route::middleware('auth')->group(function (): void {
    Route::get('/promo-preview', [PromoPreviewController::class, 'index'])->name('pelanggan.promo.preview.list');
    Route::get('/promo-preview/calculate', [PromoPreviewController::class, 'preview'])->name('pelanggan.promo.preview');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')->name('admin.dashboard');
    Route::get('/petugas/dashboard', [DashboardController::class, 'petugas'])
        ->middleware('role:petugas')->name('petugas.dashboard');
    Route::get('/pelanggan/dashboard', [DashboardController::class, 'pelanggan'])
        ->middleware(['role:pelanggan', 'member'])->name('pelanggan.dashboard');
    Route::get('/pelanggan/member', [DashboardController::class, 'pelanggan'])
        ->middleware(['role:pelanggan', 'member'])->name('pelanggan.member');
    Route::get('/pelanggan/profil', [ProfileController::class, 'pelanggan'])->middleware(['role:pelanggan', 'member'])->name('pelanggan.profile');
    Route::put('/pelanggan/profil', [ProfileController::class, 'updatePelanggan'])->middleware(['role:pelanggan', 'member'])->name('pelanggan.profile.update');
    Route::get('/pelanggan/membership/aktivasi', [MembershipController::class, 'show'])->middleware('role:pelanggan')->name('pelanggan.membership.activation');
    Route::get('/pelanggan/membership/finish', [MembershipController::class, 'finish'])->middleware('role:pelanggan')->name('pelanggan.membership.finish');
    Route::get('/petugas/profil', [ProfileController::class, 'petugas'])->middleware('role:petugas')->name('petugas.profile');
    Route::put('/petugas/profil', [ProfileController::class, 'updatePetugas'])->middleware('role:petugas')->name('petugas.profile.update');
    Route::get('/akun/password', [ProfileController::class, 'password'])->name('account.password');
    Route::put('/akun/password', [ProfileController::class, 'updatePassword'])->name('account.password.update');
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifications.index');
    Route::patch('/notifikasi/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('notifications.read');
    Route::middleware(['role:pelanggan', 'member'])->prefix('pelanggan')->name('pelanggan.')->group(function (): void {
        Route::post('membership/pay', [MembershipController::class, 'pay'])->name('membership.pay');
        Route::post('membership/sync', [MembershipController::class, 'sync'])->name('membership.sync');
        Route::get('katalog-mobil', [PublicMobilController::class, 'index'])->name('katalog-mobil.index');
        Route::get('katalog-mobil/koleksi/{slug}', [PublicMobilController::class, 'memberShowcase'])->name('katalog-mobil.showcase');
        Route::get('katalog-mobil/{mobil}', [PublicMobilController::class, 'memberShow'])->whereNumber('mobil')->name('katalog-mobil.show');
        Route::get('pesanan-mobil', [MemberVehiclePurchaseController::class, 'index'])->name('pesanan-mobil.index');
        Route::get('pesanan-mobil/create/{mobil}', [MemberVehiclePurchaseController::class, 'create'])->whereNumber('mobil')->name('pesanan-mobil.create');
        Route::post('pesanan-mobil/{mobil}', [MemberVehiclePurchaseController::class, 'store'])->whereNumber('mobil')->name('pesanan-mobil.store');
        Route::get('pesanan-mobil/{pesanan_mobil}', [MemberVehiclePurchaseController::class, 'show'])->name('pesanan-mobil.show');
        Route::get('pre-order', [MemberVehiclePreorderController::class, 'index'])->name('preorder.index');
        Route::get('pre-order/create', [MemberVehiclePreorderController::class, 'create'])->name('preorder.create');
        Route::post('pre-order', [MemberVehiclePreorderController::class, 'store'])->name('preorder.store');
        Route::patch('pre-order/{preorder}/cancel', [MemberVehiclePreorderController::class, 'cancel'])->name('preorder.cancel');
        Route::resource('kendaraan', KendaraanController::class)->only(['index','create','store','show','edit','update']);
        Route::patch('kendaraan/{kendaraan}/nonaktifkan', [KendaraanController::class, 'deactivate'])->name('kendaraan.deactivate');
        Route::get('booking-mobil', [BookingMobilController::class, 'index'])->name('booking.index');
        Route::get('booking-mobil/create', [BookingMobilController::class, 'create'])->name('booking.create');
        Route::post('booking-mobil', [BookingMobilController::class, 'store'])->name('booking.store');
        Route::get('booking-mobil/{booking_mobil}', [BookingMobilController::class, 'show'])->name('booking.show');
        Route::patch('booking-mobil/{booking_mobil}/cancel', [BookingMobilController::class, 'cancel'])->name('booking.cancel');
        Route::get('pengajuan-mobil', [PengajuanMobilController::class, 'index'])->name('pengajuan-mobil.index');
        Route::get('pengajuan-mobil/create', [PengajuanMobilController::class, 'create'])->name('pengajuan-mobil.create');
        Route::post('pengajuan-mobil', [PengajuanMobilController::class, 'store'])->name('pengajuan-mobil.store');
        Route::get('pengajuan-mobil/{pengajuan_mobil}', [PengajuanMobilController::class, 'show'])->name('pengajuan-mobil.show');
        Route::post('pengajuan-mobil/{pengajuan_mobil}/decision/{decision}', [PengajuanMobilController::class, 'decision'])->whereIn('decision',['disetujui','ditolak'])->name('pengajuan-mobil.decision');
        Route::get('trade-in', [TradeInController::class, 'index'])->name('trade-in.index');
        Route::get('trade-in/create', [TradeInController::class, 'create'])->middleware('verified.owner')->name('trade-in.create');
        Route::post('trade-in', [TradeInController::class, 'store'])->middleware('verified.owner')->name('trade-in.store');
        Route::get('trade-in/{trade_in}', [TradeInController::class, 'show'])->name('trade-in.show');
        Route::get('invoice/trade-in/{trade_in}', [TradeInInvoiceController::class, 'show'])->name('invoice.trade-in');
        Route::get('test-drive', [TestDriveController::class, 'index'])->name('test-drive.index');
        Route::get('test-drive/create', [TestDriveController::class, 'create'])->name('test-drive.create');
        Route::post('test-drive', [TestDriveController::class, 'store'])->name('test-drive.store');
        Route::get('test-drive/{test_drive}', [TestDriveController::class, 'show'])->name('test-drive.show');
        Route::patch('test-drive/{test_drive}/cancel', [TestDriveController::class, 'cancel'])->name('test-drive.cancel');
        Route::get('booking-servis', [BookingServisController::class, 'index'])->name('booking-servis.index');
        Route::get('booking-servis/create', [BookingServisController::class, 'create'])->middleware('verified.owner')->name('booking-servis.create');
        Route::post('booking-servis', [BookingServisController::class, 'store'])->middleware('verified.owner')->name('booking-servis.store');
        Route::get('booking-servis/{booking_servis}', [BookingServisController::class, 'show'])->name('booking-servis.show');
        Route::patch('booking-servis/{booking_servis}/cancel', [BookingServisController::class, 'cancel'])->name('booking-servis.cancel');
        Route::get('booking-cuci', [BookingCuciController::class, 'index'])->name('booking-cuci.index');
        Route::get('booking-cuci/create', [BookingCuciController::class, 'create'])->middleware('verified.owner')->name('booking-cuci.create');
        Route::post('booking-cuci', [BookingCuciController::class, 'store'])->middleware('verified.owner')->name('booking-cuci.store');
        Route::get('booking-cuci/{booking_cuci}', [BookingCuciController::class, 'show'])->name('booking-cuci.show');
        Route::patch('booking-cuci/{booking_cuci}/cancel', [BookingCuciController::class, 'cancel'])->name('booking-cuci.cancel');
        Route::get('servis', [PelangganServisController::class, 'index'])->name('servis.index');
    Route::get('servis/{servis}', [PelangganServisController::class, 'show'])->name('servis.show');
        Route::get('invoice/servis/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.servis');
        Route::get('invoice/car-wash/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.carwash');
        Route::get('invoice/sparepart/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.sparepart');
        Route::get('invoice/penjualan-mobil/{transaksi}', [CarSaleInvoiceController::class, 'show'])->name('invoice.car-sale');
        Route::get('invoice/pembelian-mobil/{pembelian_mobil}', [UsedCarReceiptController::class, 'show'])->name('invoice.used-car');
        Route::get('poin', [PointController::class, 'index'])->name('poin');
        Route::get('promo', [PelangganPromoMemberController::class, 'index'])->name('promo.index');
        Route::get('promo/{promo_member}', [PelangganPromoMemberController::class, 'show'])->name('promo.show');
        Route::get('pengingat-servis', [ServiceReminderController::class, 'index'])->name('pengingat-servis');
        Route::post('transaksi/{transaksi}/midtrans', [MidtransController::class, 'create'])->middleware('throttle:10,1')->name('midtrans.create');
        Route::post('pembayaran/midtrans/{gateway}/sync', [MidtransController::class, 'sync'])->middleware('throttle:10,1')->name('midtrans.sync');
        Route::get('pembayaran/midtrans/kembali', [MidtransController::class, 'returned'])->name('midtrans.returned');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('membership', [AdminMembershipController::class, 'index'])->name('membership.index');
    Route::resource('jenis-servis', JenisServisController::class)->only(['index','create','store','show','edit','update'])->parameters(['jenis-servis'=>'jenis_servis']);
    Route::patch('jenis-servis/{jenis_servis}/toggle', [JenisServisController::class, 'toggle'])->name('jenis-servis.toggle');
    Route::resource('paket-cuci', PaketCuciController::class)->only(['index','create','store','show','edit','update'])->parameters(['paket-cuci'=>'paket_cuci']);
    Route::patch('paket-cuci/{paket_cuci}/toggle', [PaketCuciController::class, 'toggle'])->name('paket-cuci.toggle');
    Route::get('promo-member/from-simulation/{promo_simulation}/create', [AdminPromoMemberController::class, 'createFromSimulation'])->name('promo-member.from-simulation.create');
    Route::resource('promo-member', AdminPromoMemberController::class)->only(['index','create','store','show','edit','update'])->parameters(['promo-member' => 'promo_member']);
    Route::patch('promo-member/{promo_member}/toggle', [AdminPromoMemberController::class, 'toggle'])->name('promo-member.toggle');
    Route::get('promo-simulations', [PromoSimulationController::class,'index'])->name('promo-simulations.index');
    Route::get('promo-simulations/create', [PromoSimulationController::class,'create'])->name('promo-simulations.create');
    Route::post('promo-simulations', [PromoSimulationController::class,'store'])->name('promo-simulations.store');
    Route::get('promo-simulations/{promo_simulation}', [PromoSimulationController::class,'show'])->name('promo-simulations.show');
    Route::get('promo-analytics', [PromoAnalyticsController::class, 'index'])->name('promo-analytics.index');
    Route::get('promo-analytics/{promo_member}', [PromoAnalyticsController::class, 'show'])->name('promo-analytics.show');
    Route::get('laporan-servis', [LaporanServisController::class, 'index'])->name('laporan-servis.index');
    Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('pengaturan/{pengaturan}', [PengaturanController::class, 'update'])->whereIn('pengaturan', PengaturanService::KEYS)->name('pengaturan.update');
    Route::resource('pelanggan', AdminPelangganController::class)->only(['index','create','store','show','edit','update']);
    Route::patch('pelanggan/{pelanggan}/toggle', [AdminPelangganController::class, 'toggle'])->name('pelanggan.toggle');
    Route::resource('petugas', AdminPetugasController::class)->only(['index','create','store','show','edit','update'])->parameters(['petugas' => 'petugas']);
    Route::patch('petugas/{petugas}/toggle', [AdminPetugasController::class, 'toggle'])->name('petugas.toggle');
    Route::resource('kategori-sparepart', KategoriSparepartController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('kategori-sparepart/{kategori_sparepart}/toggle', [KategoriSparepartController::class, 'toggle'])->name('kategori-sparepart.toggle');
    Route::resource('supplier', SupplierController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::patch('supplier/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('supplier.toggle');
    Route::resource('sparepart', AdminSparepartController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::patch('sparepart/{sparepart}/toggle', [AdminSparepartController::class, 'toggle'])->name('sparepart.toggle');
    Route::get('inventory/stok', [InventoryController::class, 'stock'])->name('inventory.stock');
    Route::get('inventory/riwayat', [InventoryController::class, 'history'])->name('inventory.history');
    Route::get('inventory/penyesuaian/{sparepart?}', [InventoryController::class, 'adjustment'])->name('inventory.adjustment');
    Route::post('inventory/penyesuaian', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::resource('stok-masuk', StokMasukController::class)->only(['index','create','store','show'])->parameters(['stok-masuk'=>'stok_masuk']);
    Route::post('stok-masuk/{stok_masuk}/terima', [StokMasukController::class, 'finalize'])->name('stok-masuk.finalize');
    Route::post('stok-masuk/{stok_masuk}/batal', [StokMasukController::class, 'cancel'])->name('stok-masuk.cancel');
    Route::post('stok-masuk/{stok_masuk}/detail', [StokMasukController::class, 'addDetail'])->name('stok-masuk.detail.store');
    Route::delete('stok-masuk/detail/{detail_stok_masuk}', [StokMasukController::class, 'removeDetail'])->name('stok-masuk.detail.destroy');
    Route::get('notifikasi', [AdminNotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('notifikasi/{notifikasi}/read', [AdminNotifikasiController::class, 'read'])->name('notifikasi.read');
    Route::get('inventory/stok', [InventoryController::class, 'stock'])->name('inventory.stock');
    Route::get('inventory/riwayat', [InventoryController::class, 'history'])->name('inventory.history');
    Route::get('inventory/penyesuaian/{sparepart?}', [InventoryController::class, 'adjustment'])->name('inventory.adjustment');
    Route::post('inventory/penyesuaian', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::resource('stok-masuk', StokMasukController::class)->only(['index','create','store','show'])->parameters(['stok-masuk'=>'stok_masuk']);
    Route::post('stok-masuk/{stok_masuk}/terima', [StokMasukController::class, 'finalize'])->name('stok-masuk.finalize');
    Route::post('stok-masuk/{stok_masuk}/batal', [StokMasukController::class, 'cancel'])->name('stok-masuk.cancel');
    Route::post('stok-masuk/{stok_masuk}/detail', [StokMasukController::class, 'addDetail'])->name('stok-masuk.detail.store');
    Route::put('stok-masuk/detail/{detail_stok_masuk}', [StokMasukController::class, 'updateDetail'])->name('stok-masuk.detail.update');
    Route::delete('stok-masuk/detail/{detail_stok_masuk}', [StokMasukController::class, 'removeDetail'])->name('stok-masuk.detail.destroy');
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('notifikasi/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('notifikasi.read');
    Route::resource('mobil', AdminMobilController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::get('pre-order-mobil', [AdminMemberVehiclePreorderController::class, 'index'])->name('preorder-mobil.index');
    Route::get('pesanan-mobil', [AdminMemberVehiclePurchaseController::class, 'index'])->name('pesanan-mobil.index');
    Route::get('pesanan-mobil/{pesanan_mobil}', [AdminMemberVehiclePurchaseController::class, 'show'])->name('pesanan-mobil.show');
    Route::post('pesanan-mobil/{pesanan_mobil}/approve', [AdminMemberVehiclePurchaseController::class, 'approve'])->name('pesanan-mobil.approve');
    Route::post('pesanan-mobil/{pesanan_mobil}/reject', [AdminMemberVehiclePurchaseController::class, 'reject'])->name('pesanan-mobil.reject');
    Route::post('pesanan-mobil/{pesanan_mobil}/schedule', [AdminMemberVehiclePurchaseController::class, 'schedule'])->name('pesanan-mobil.schedule');
    Route::post('pesanan-mobil/{pesanan_mobil}/pdi', [AdminMemberVehiclePurchaseController::class, 'pdi'])->name('pesanan-mobil.pdi');
    Route::post('pesanan-mobil/{pesanan_mobil}/handover', [AdminMemberVehiclePurchaseController::class, 'handover'])->name('pesanan-mobil.handover');
    Route::patch('mobil/{mobil}/nonaktifkan', [AdminMobilController::class, 'deactivate'])->name('mobil.deactivate');
    Route::post('mobil/{mobil}/foto', [AdminFotoMobilController::class, 'store'])->name('mobil.foto.store');
    Route::patch('mobil/{mobil}/foto/{foto}/primary', [AdminFotoMobilController::class, 'primary'])->name('mobil.foto.primary');
    Route::delete('mobil/{mobil}/foto/{foto}', [AdminFotoMobilController::class, 'destroy'])->name('mobil.foto.destroy');
    Route::get('booking-mobil', [OperationsBookingController::class, 'index'])->name('booking.index');
    Route::get('booking-mobil/{booking_mobil}', [OperationsBookingController::class, 'show'])->name('booking.show');
    Route::post('booking-mobil/{booking_mobil}/{action}', [OperationsBookingController::class, 'action'])->whereIn('action', ['confirm'])->name('booking.action');
    Route::get('pengajuan-mobil', [OperationsPengajuanMobilController::class, 'index'])->name('pengajuan-mobil.index');
    Route::get('pengajuan-mobil/{pengajuan_mobil}', [OperationsPengajuanMobilController::class, 'show'])->name('pengajuan-mobil.show');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/process', [OperationsPengajuanMobilController::class, 'process'])->name('pengajuan-mobil.process');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/start', [OperationsPengajuanMobilController::class, 'start'])->name('pengajuan-mobil.start');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/inspection', [OperationsPengajuanMobilController::class, 'inspection'])->name('pengajuan-mobil.inspection');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/offer', [OperationsPengajuanMobilController::class, 'offer'])->name('pengajuan-mobil.offer');
    Route::get('trade-in', [OperationsTradeInController::class, 'index'])->name('trade-in.index');
    Route::get('trade-in/{trade_in}', [OperationsTradeInController::class, 'show'])->name('trade-in.show');
    Route::post('trade-in/{trade_in}/finalize', [OperationsTradeInController::class, 'finalize'])->name('trade-in.finalize');
    Route::post('trade-in/{trade_in}/cancel', [OperationsTradeInController::class, 'cancel'])->name('trade-in.cancel');
    Route::get('invoice/trade-in/{trade_in}', [TradeInInvoiceController::class, 'show'])->name('invoice.trade-in');
    Route::get('transaksi-trade-in/{transaksi}/pembayaran/create', [PaymentController::class, 'create'])->name('trade-in.payment.create');
    Route::post('transaksi-trade-in/{transaksi}/pembayaran', [PaymentController::class, 'store'])->name('trade-in.payment.store');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/purchase', [UsedCarSettlementController::class, 'purchase'])->name('pengajuan-mobil.purchase');
    Route::post('pembelian-mobil/{pembelian_mobil}/finalize', [UsedCarSettlementController::class, 'finalize'])->name('pembelian-mobil.finalize');
    Route::get('transaksi-pembelian-mobil/{transaksi}/pembayaran/create', [OutgoingPaymentController::class, 'create'])->name('used-car.payment.create');
    Route::post('transaksi-pembelian-mobil/{transaksi}/pembayaran', [OutgoingPaymentController::class, 'store'])->name('used-car.payment.store');
    Route::post('pembayaran-keluar/{pembayaran}/verify', [OutgoingPaymentController::class, 'verify'])->name('used-car.payment.verify');
    Route::post('pembayaran-keluar/{pembayaran}/reject', [OutgoingPaymentController::class, 'reject'])->name('used-car.payment.reject');
    Route::get('invoice/pembelian-mobil/{pembelian_mobil}', [UsedCarReceiptController::class, 'show'])->name('invoice.used-car');
    Route::get('test-drive', [OperationsTestDriveController::class, 'index'])->name('test-drive.index');
    Route::get('test-drive/{test_drive}', [OperationsTestDriveController::class, 'show'])->name('test-drive.show');
    Route::post('test-drive/{test_drive}/{action}', [OperationsTestDriveController::class, 'action'])->whereIn('action', ['approve','reject','complete'])->name('test-drive.action');
    Route::get('booking-servis', [OperationsBookingServisController::class, 'index'])->name('booking-servis.index');
    Route::get('booking-servis/{booking_servis}', [OperationsBookingServisController::class, 'show'])->name('booking-servis.show');
    Route::post('booking-servis/{booking_servis}/{action}', [OperationsBookingServisController::class, 'action'])->whereIn('action', ['confirm','reject'])->name('booking-servis.action');
    Route::get('servis', [OperationsServisController::class, 'index'])->name('servis.index');
    Route::get('servis/{servis}', [OperationsServisController::class, 'show'])->name('servis.show');
    Route::post('servis/from-booking/{booking_servis}', [OperationsServisController::class, 'fromBooking'])->name('servis.from-booking');
    Route::post('servis/{servis}/inspection', [OperationsServisController::class, 'inspection'])->name('servis.inspection');
    Route::post('servis/{servis}/detail', [OperationsServisController::class, 'detail'])->name('servis.detail.store');
    Route::delete('servis/{servis}/detail/{detail_servis}', [OperationsServisController::class, 'removeDetail'])->name('servis.detail.destroy');
    Route::post('servis/{servis}/sparepart', [OperationsServisController::class, 'sparepart'])->name('servis.sparepart.store');
    Route::delete('servis/{servis}/sparepart/{servis_sparepart}', [OperationsServisController::class, 'removeSparepart'])->name('servis.sparepart.destroy');
    Route::post('servis/{servis}/finalize', [OperationsServisController::class, 'finalize'])->name('servis.finalize');
    Route::get('transaksi-servis/{transaksi}/pembayaran/create', [PaymentController::class, 'create'])->name('payment.create');
    Route::post('transaksi-servis/{transaksi}/pembayaran', [PaymentController::class, 'store'])->name('payment.store');
    Route::get('transaksi-mobil/{transaksi}/pembayaran/create', [CarSalePaymentController::class, 'create'])->name('car-sale.payment.create');
    Route::post('transaksi-mobil/{transaksi}/pembayaran', [CarSalePaymentController::class, 'store'])->name('car-sale.payment.store');
    Route::post('pembayaran/{pembayaran}/verify', [PaymentController::class, 'verify'])->name('payment.verify');
    Route::post('pembayaran/{pembayaran}/reject', [PaymentController::class, 'reject'])->name('payment.reject');
    Route::get('invoice/servis/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.servis');
    Route::get('invoice/car-wash/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.carwash');
    Route::get('invoice/sparepart/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.sparepart');
    Route::get('invoice/penjualan-mobil/{transaksi}', [CarSaleInvoiceController::class, 'show'])->name('invoice.car-sale');
    Route::get('booking-cuci', [OperationsBookingCuciController::class, 'index'])->name('booking-cuci.index');
    Route::get('booking-cuci/{booking_cuci}', [OperationsBookingCuciController::class, 'show'])->name('booking-cuci.show');
    Route::post('booking-cuci/{booking_cuci}/{action}', [OperationsBookingCuciController::class, 'action'])->whereIn('action', ['confirm','start','finalize'])->name('booking-cuci.action');
    Route::get('penjualan-sparepart', [OperationsPenjualanSparepartController::class, 'index'])->name('penjualan-sparepart.index');
    Route::get('penjualan-sparepart/create', [OperationsPenjualanSparepartController::class, 'create'])->name('penjualan-sparepart.create');
    Route::post('penjualan-sparepart', [OperationsPenjualanSparepartController::class, 'store'])->name('penjualan-sparepart.store');
    Route::get('penjualan-sparepart/{penjualan_sparepart}', [OperationsPenjualanSparepartController::class, 'show'])->name('penjualan-sparepart.show');
    Route::post('penjualan-sparepart/{penjualan_sparepart}/detail', [OperationsPenjualanSparepartController::class, 'addDetail'])->name('penjualan-sparepart.detail.store');
    Route::put('penjualan-sparepart/detail/{detail_penjualan_sparepart}', [OperationsPenjualanSparepartController::class, 'updateDetail'])->name('penjualan-sparepart.detail.update');
    Route::delete('penjualan-sparepart/detail/{detail_penjualan_sparepart}', [OperationsPenjualanSparepartController::class, 'deleteDetail'])->name('penjualan-sparepart.detail.destroy');
    Route::post('penjualan-sparepart/{penjualan_sparepart}/finalize', [OperationsPenjualanSparepartController::class, 'finalize'])->name('penjualan-sparepart.finalize');
    Route::post('penjualan-sparepart/{penjualan_sparepart}/cancel', [OperationsPenjualanSparepartController::class, 'cancel'])->name('penjualan-sparepart.cancel');
    Route::get('penjualan-mobil', [OperationsPenjualanMobilController::class, 'index'])->name('penjualan-mobil.index');
    Route::get('penjualan-mobil/create', [OperationsPenjualanMobilController::class, 'create'])->name('penjualan-mobil.create');
    Route::post('penjualan-mobil', [OperationsPenjualanMobilController::class, 'store'])->name('penjualan-mobil.store');
    Route::get('penjualan-mobil/{penjualan_mobil}', [OperationsPenjualanMobilController::class, 'show'])->name('penjualan-mobil.show');
});

Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function (): void {
    Route::get('sparepart', [PetugasSparepartController::class, 'index'])->name('sparepart.index');
    Route::get('sparepart/{sparepart}', [PetugasSparepartController::class, 'show'])->whereNumber('sparepart')->name('sparepart.show');
    Route::get('mobil', [PetugasMobilController::class, 'index'])->name('mobil.index');
    Route::get('mobil/{mobil}', [PetugasMobilController::class, 'show'])->whereNumber('mobil')->name('mobil.show');
    Route::get('booking-mobil', [OperationsBookingController::class, 'index'])->name('booking.index');
    Route::get('booking-mobil/{booking_mobil}', [OperationsBookingController::class, 'show'])->name('booking.show');
    Route::post('booking-mobil/{booking_mobil}/{action}', [OperationsBookingController::class, 'action'])->whereIn('action', ['confirm','cancel','complete'])->name('booking.action');
    Route::get('pengajuan-mobil', [OperationsPengajuanMobilController::class, 'index'])->name('pengajuan-mobil.index');
    Route::get('pengajuan-mobil/{pengajuan_mobil}', [OperationsPengajuanMobilController::class, 'show'])->name('pengajuan-mobil.show');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/process', [OperationsPengajuanMobilController::class, 'process'])->name('pengajuan-mobil.process');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/start', [OperationsPengajuanMobilController::class, 'start'])->name('pengajuan-mobil.start');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/inspection', [OperationsPengajuanMobilController::class, 'inspection'])->name('pengajuan-mobil.inspection');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/offer', [OperationsPengajuanMobilController::class, 'offer'])->name('pengajuan-mobil.offer');
    Route::get('trade-in', [OperationsTradeInController::class, 'index'])->name('trade-in.index');
    Route::get('trade-in/{trade_in}', [OperationsTradeInController::class, 'show'])->name('trade-in.show');
    Route::post('trade-in/{trade_in}/finalize', [OperationsTradeInController::class, 'finalize'])->name('trade-in.finalize');
    Route::post('trade-in/{trade_in}/cancel', [OperationsTradeInController::class, 'cancel'])->name('trade-in.cancel');
    Route::get('invoice/trade-in/{trade_in}', [TradeInInvoiceController::class, 'show'])->name('invoice.trade-in');
    Route::get('transaksi-trade-in/{transaksi}/pembayaran/create', [PaymentController::class, 'create'])->name('trade-in.payment.create');
    Route::post('transaksi-trade-in/{transaksi}/pembayaran', [PaymentController::class, 'store'])->name('trade-in.payment.store');
    Route::post('pengajuan-mobil/{pengajuan_mobil}/purchase', [UsedCarSettlementController::class, 'purchase'])->name('pengajuan-mobil.purchase');
    Route::post('pembelian-mobil/{pembelian_mobil}/finalize', [UsedCarSettlementController::class, 'finalize'])->name('pembelian-mobil.finalize');
    Route::get('transaksi-pembelian-mobil/{transaksi}/pembayaran/create', [OutgoingPaymentController::class, 'create'])->name('used-car.payment.create');
    Route::post('transaksi-pembelian-mobil/{transaksi}/pembayaran', [OutgoingPaymentController::class, 'store'])->name('used-car.payment.store');
    Route::post('pembayaran-keluar/{pembayaran}/verify', [OutgoingPaymentController::class, 'verify'])->name('used-car.payment.verify');
    Route::post('pembayaran-keluar/{pembayaran}/reject', [OutgoingPaymentController::class, 'reject'])->name('used-car.payment.reject');
    Route::get('invoice/pembelian-mobil/{pembelian_mobil}', [UsedCarReceiptController::class, 'show'])->name('invoice.used-car');
    Route::get('test-drive', [OperationsTestDriveController::class, 'index'])->name('test-drive.index');
    Route::get('test-drive/{test_drive}', [OperationsTestDriveController::class, 'show'])->name('test-drive.show');
    Route::post('test-drive/{test_drive}/{action}', [OperationsTestDriveController::class, 'action'])->whereIn('action', ['approve','reject','complete','cancel'])->name('test-drive.action');
    Route::get('booking-servis', [OperationsBookingServisController::class, 'index'])->name('booking-servis.index');
    Route::get('booking-servis/{booking_servis}', [OperationsBookingServisController::class, 'show'])->name('booking-servis.show');
    Route::post('booking-servis/{booking_servis}/{action}', [OperationsBookingServisController::class, 'action'])->whereIn('action', ['confirm','reject'])->name('booking-servis.action');
    Route::get('servis', [OperationsServisController::class, 'index'])->name('servis.index');
    Route::get('servis/{servis}', [OperationsServisController::class, 'show'])->name('servis.show');
    Route::post('servis/from-booking/{booking_servis}', [OperationsServisController::class, 'fromBooking'])->name('servis.from-booking');
    Route::post('servis/{servis}/inspection', [OperationsServisController::class, 'inspection'])->name('servis.inspection');
    Route::post('servis/{servis}/detail', [OperationsServisController::class, 'detail'])->name('servis.detail.store');
    Route::delete('servis/{servis}/detail/{detail_servis}', [OperationsServisController::class, 'removeDetail'])->name('servis.detail.destroy');
    Route::post('servis/{servis}/sparepart', [OperationsServisController::class, 'sparepart'])->name('servis.sparepart.store');
    Route::delete('servis/{servis}/sparepart/{servis_sparepart}', [OperationsServisController::class, 'removeSparepart'])->name('servis.sparepart.destroy');
    Route::post('servis/{servis}/finalize', [OperationsServisController::class, 'finalize'])->name('servis.finalize');
    Route::get('transaksi-servis/{transaksi}/pembayaran/create', [PaymentController::class, 'create'])->name('payment.create');
    Route::post('transaksi-servis/{transaksi}/pembayaran', [PaymentController::class, 'store'])->name('payment.store');
    Route::get('transaksi-mobil/{transaksi}/pembayaran/create', [CarSalePaymentController::class, 'create'])->name('car-sale.payment.create');
    Route::post('transaksi-mobil/{transaksi}/pembayaran', [CarSalePaymentController::class, 'store'])->name('car-sale.payment.store');
    Route::post('pembayaran/{pembayaran}/verify', [PaymentController::class, 'verify'])->name('payment.verify');
    Route::post('pembayaran/{pembayaran}/reject', [PaymentController::class, 'reject'])->name('payment.reject');
    Route::get('invoice/servis/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.servis');
    Route::get('invoice/car-wash/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.carwash');
    Route::get('invoice/sparepart/{transaksi}', [InvoiceController::class, 'show'])->name('invoice.sparepart');
    Route::get('invoice/penjualan-mobil/{transaksi}', [CarSaleInvoiceController::class, 'show'])->name('invoice.car-sale');
    Route::get('booking-cuci', [OperationsBookingCuciController::class, 'index'])->name('booking-cuci.index');
    Route::get('booking-cuci/{booking_cuci}', [OperationsBookingCuciController::class, 'show'])->name('booking-cuci.show');
    Route::post('booking-cuci/{booking_cuci}/{action}', [OperationsBookingCuciController::class, 'action'])->whereIn('action', ['confirm','start','finalize'])->name('booking-cuci.action');
    Route::get('penjualan-sparepart', [OperationsPenjualanSparepartController::class, 'index'])->name('penjualan-sparepart.index');
    Route::get('penjualan-sparepart/create', [OperationsPenjualanSparepartController::class, 'create'])->name('penjualan-sparepart.create');
    Route::post('penjualan-sparepart', [OperationsPenjualanSparepartController::class, 'store'])->name('penjualan-sparepart.store');
    Route::get('penjualan-sparepart/{penjualan_sparepart}', [OperationsPenjualanSparepartController::class, 'show'])->name('penjualan-sparepart.show');
    Route::post('penjualan-sparepart/{penjualan_sparepart}/detail', [OperationsPenjualanSparepartController::class, 'addDetail'])->name('penjualan-sparepart.detail.store');
    Route::put('penjualan-sparepart/detail/{detail_penjualan_sparepart}', [OperationsPenjualanSparepartController::class, 'updateDetail'])->name('penjualan-sparepart.detail.update');
    Route::delete('penjualan-sparepart/detail/{detail_penjualan_sparepart}', [OperationsPenjualanSparepartController::class, 'deleteDetail'])->name('penjualan-sparepart.detail.destroy');
    Route::post('penjualan-sparepart/{penjualan_sparepart}/finalize', [OperationsPenjualanSparepartController::class, 'finalize'])->name('penjualan-sparepart.finalize');
    Route::get('penjualan-mobil', [OperationsPenjualanMobilController::class, 'index'])->name('penjualan-mobil.index');
    Route::get('penjualan-mobil/create', [OperationsPenjualanMobilController::class, 'create'])->name('penjualan-mobil.create');
    Route::post('penjualan-mobil', [OperationsPenjualanMobilController::class, 'store'])->name('penjualan-mobil.store');
    Route::get('penjualan-mobil/{penjualan_mobil}', [OperationsPenjualanMobilController::class, 'show'])->name('penjualan-mobil.show');
});
