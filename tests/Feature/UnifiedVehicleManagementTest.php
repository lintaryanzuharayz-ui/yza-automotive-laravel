<?php

namespace Tests\Feature;

use App\Models\{Mobil, MobilWarna, User, VehicleInventory};
use App\Support\AdminNavigation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UnifiedVehicleManagementTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesColorStockUnit;

    private function actor(string $role = 'admin'): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
    }

    /** 1. Sidebar Super Admin hanya memiliki satu menu 'Manajemen Mobil & Showroom' */
    public function test_sidebar_has_single_merged_vehicle_management_menu(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get(route('admin.dashboard'))->assertOk();

        $content = $response->getContent();
        preg_match('/<aside class="admin-sidebar".*?<\/aside>/s', $content, $matches);
        $sidebarContent = $matches[0] ?? $content;

        $this->assertStringContainsString('Manajemen Mobil &amp; Showroom', $sidebarContent);
        $this->assertStringNotContainsString('Inventori &amp; Stok Mobil', $sidebarContent);
        $this->assertStringNotContainsString('Katalog &amp; Showroom', $sidebarContent);

        // Pastikan route yang dituju adalah admin.mobil.index
        $this->assertStringContainsString(route('admin.mobil.index'), $sidebarContent);

        // Semantic icon check
        $this->assertSame('car', AdminNavigation::icon('admin.mobil.index'));
    }

    /** 2. Halaman terpadu menampilkan header, 6 KPI, dan 3 Tab */
    public function test_unified_page_renders_header_kpis_and_three_tabs(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get(route('admin.mobil.index'))->assertOk();

        // Header & Description
        $response->assertSee('Manajemen Mobil &amp; Showroom', false);
        $response->assertSee('Kelola kendaraan, stok, warna, galeri, dan tampilan showroom YZA Automotive dalam satu halaman.', false);
        $response->assertSee('Tambah Mobil', false);


        // 6 KPI compact
        $response->assertSee('Total Model Katalog', false);
        $response->assertSee('Unit Ready', false);
        $response->assertSee('Reservasi Aktif', false);
        $response->assertSee('Kendaraan Terjual', false);
        $response->assertSee('Pre-Order Aktif', false);
        $response->assertSee('Unit Fisik Tercatat', false);

        // 3 Tabs
        $response->assertSee('Inventori &amp; Stok', false);
        $response->assertSee('Katalog &amp; Showroom', false);
        $response->assertSee('Foto &amp; Warna', false);
    }

    /** 3. Tab 1: Inventori & Stok menampilkan tabel dengan nomor rangka (VIN) dan stok */
    public function test_tab_1_inventory_renders_compact_table_with_vin(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get(route('admin.mobil.index', ['tab' => 'inventori']))->assertOk();

        $response->assertSee('KENDARAAN &amp; KODE', false);
        $response->assertSee('STATUS KATALOG', false);
        $response->assertSee('STOK FISIK', false);
        $response->assertSee('STOK READY', false);
        $response->assertSee('RESERVASI AKTIF', false);
        $response->assertSee('PRE-ORDER AKTIF', false);
        $response->assertSee('KETERSEDIAAN', false);
        $response->assertSee('HARGA', false);
        $response->assertSee('VIN:', false);
    }

    /** 4. Tab 2: Katalog & Showroom menampilkan card grid dan preview publik */
    public function test_tab_2_catalog_renders_card_grid_and_public_preview(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get(route('admin.mobil.index', ['tab' => 'katalog']))->assertOk();

        $response->assertSee('intro-title">Katalog &amp; Showroom', false);
        $response->assertSee('showroom-car-card', false);
        $response->assertSee('Lihat Detail', false);
        $response->assertSee('Edit', false);
        $response->assertSee('Preview', false);
    }

    /** 5. Tab 3: Foto & Warna menampilkan multi-sudut dan pembeda warna katalog vs unit fisik */
    public function test_tab_3_photos_and_colors_renders_multi_angle_and_color_distinction(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get(route('admin.mobil.index', ['tab' => 'foto']))->assertOk();

        $response->assertSee('Foto &amp; Master Warna Kendaraan', false);
        $response->assertSee('Depan (Front 3/4)', false);
        $response->assertSee('Samping (Side Profile)', false);
        $response->assertSee('Belakang (Rear 3/4)', false);
        $response->assertSee('Kabin / Interior', false);
        $response->assertSee('Warna Fisik Unit:', false);
        $response->assertSee('Pilihan Warna Katalog Showroom &amp; Master Konfigurasi', false);
    }

    /** 6. Kompatibilitas route lama: /admin/katalog-showroom redirect aman ke /admin/mobil?tab=katalog dengan query param */
    public function test_legacy_katalog_showroom_route_redirects_safely_with_parameters(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get('/admin/katalog-showroom?q=supra&merk=Toyota');

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/admin/mobil', $targetUrl);
        $this->assertStringContainsString('tab=katalog', $targetUrl);
        $this->assertStringContainsString('q=supra', $targetUrl);
        $this->assertStringContainsString('merk=Toyota', $targetUrl);
    }

    /** 7. Data stok dan harga konsisten antara Inventori dan Katalog */
    public function test_inventory_and_catalog_share_consistent_authoritative_data(): void
    {
        $car = Mobil::firstOrFail();
        $response = $this->actingAs($this->actor('admin'))->get(route('admin.mobil.index', ['q' => $car->kode_mobil]))->assertOk();

        $priceFormatted = 'Rp ' . number_format((float) $car->harga_jual, 0, ',', '.');
        $response->assertSee($priceFormatted);
    }

    /** 8. Owner tetap read-only: dapat melihat tetapi tidak dapat memutasi data */
    public function test_owner_is_read_only_on_unified_vehicle_management(): void
    {
        $owner = $this->actor('owner');

        // Owner can view the unified page
        $this->actingAs($owner)->get(route('admin.mobil.index'))->assertOk();

        // Owner cannot deactivate car
        $car = Mobil::firstOrFail();
        $this->actingAs($owner)->patch(route('admin.mobil.deactivate', $car))->assertForbidden();

        // Owner cannot store car
        $this->actingAs($owner)->post(route('admin.mobil.store'), [])->assertForbidden();
    }

    /** 9. Member dan Guest tidak dapat mengakses halaman manajemen mobil admin */
    public function test_member_and_guest_cannot_access_admin_vehicle_management(): void
    {
        // Guest redirects to login
        $this->get(route('admin.mobil.index'))->assertRedirect(route('login'));

        // Member gets 403 Forbidden
        $member = $this->actor('pelanggan');
        $this->actingAs($member)->get(route('admin.mobil.index'))->assertForbidden();
    }

    /** 10. Halaman Manajemen Mobil me-render trigger modal dan elemen reusable modal */
    public function test_actions_render_as_modal_triggers_and_not_full_page_links(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get(route('admin.mobil.index'))->assertOk();

        // Modal container & backdrop
        $response->assertSee('id="vehicleModalBackdrop"', false);
        $response->assertSee('id="vehicleModalDialog"', false);
        $response->assertSee('id="vehicleModalTitle"', false);
        $response->assertSee('id="vehicleModalBody"', false);

        // Sticky action column & floating action dropdown
        $response->assertSee('sticky-col', false);
        $response->assertSee('id="floatingActionDropdown"', false);

        // Modal action triggers in data attributes (Single Three-Dot Menu System)
        $response->assertSee('data-action="detail"', false);
        $response->assertSee('data-action="stock"', false);
        $response->assertSee('data-action="edit"', false);
        $response->assertSee('data-action="add_unit"', false);
        $response->assertSee('data-action="preorder"', false);
        $response->assertSee('data-action="gallery"', false);
        $response->assertSee('data-action="deactivate"', false);
    }

    /** 11. Endpoint show mengembalikan payload JSON lengkap untuk modal detail & spesifikasi */
    public function test_show_endpoint_returns_json_for_modal(): void
    {
        $car = Mobil::firstOrFail();
        $response = $this->actingAs($this->actor('admin'))
            ->getJson(route('admin.mobil.show', $car))
            ->assertOk();

        $response->assertJsonStructure([
            'success',
            'mobil' => ['id_mobil', 'kode_mobil', 'merk', 'model', 'nomor_rangka', 'nomor_mesin'],
            'availability' => ['stock', 'label', 'status_class'],
            'histories',
            'active_orders',
            'active_bookings',
            'is_deactivatable',
        ]);
        $this->assertTrue($response->json('success'));
    }

    /** 12. Endpoint stock mengembalikan data ketersediaan & histori pergerakan untuk modal stok */
    public function test_stock_endpoint_returns_json_for_modal(): void
    {
        $car = Mobil::firstOrFail();
        $response = $this->actingAs($this->actor('admin'))
            ->getJson(route('admin.mobil.stock', $car))
            ->assertOk();

        $response->assertJsonStructure([
            'success',
            'mobil',
            'availability',
            'histories',
        ]);
        $this->assertTrue($response->json('success'));
    }

    /** 13. Endpoint edit mengembalikan data mobil lengkap untuk modal edit */
    public function test_edit_endpoint_returns_json_for_modal(): void
    {
        $car = Mobil::firstOrFail();
        $response = $this->actingAs($this->actor('admin'))
            ->getJson(route('admin.mobil.edit', $car))
            ->assertOk();

        $response->assertJsonStructure([
            'success',
            'mobil' => ['id_mobil', 'kode_mobil', 'merk', 'model', 'harga_jual', 'kondisi', 'status'],
        ]);
        $this->assertTrue($response->json('success'));
    }

    /** 14. Penyesuaian stok via AJAX memvalidasi input dan mencatat pergerakan stok */
    public function test_adjust_stock_ajax_updates_stock_and_validates(): void
    {
        $admin = $this->actor('admin');
        $car = Mobil::where('status', 'tersedia')->firstOrFail();

        // 14a: Valid adjustment
        $response = $this->actingAs($admin)
            ->postJson(route('admin.mobil.stock.adjust', $car), [
                'stok_baru' => app(\App\Services\VehicleColorStockService::class)->state($car)['stock_total'],
                'bukti' => 'BAST-QA-1',
                'alasan' => 'penambahan',
                'catatan' => 'Uji validasi modal stok terpadu',
            ])
            ->assertOk();

        $response->assertJson([
            'success' => true,
            'message' => 'Stok kendaraan berhasil disesuaikan dan dicatat.',
        ]);

        // 14b: Validation failure on negative stock
        $failResponse = $this->actingAs($admin)
            ->postJson(route('admin.mobil.stock.adjust', $car), [
                'stok_baru' => -5,
                'alasan' => '',
            ])
            ->assertStatus(422);

        $failResponse->assertJsonValidationErrors(['stok_baru', 'alasan']);
    }

    /** 15. Update mobil via AJAX memperbarui data dan mengembalikan JSON */
    public function test_update_car_ajax_updates_and_validates(): void
    {
        $admin = $this->actor('admin');
        $car = Mobil::firstOrFail();

        // 15a: Validation failure on empty required fields
        $this->actingAs($admin)
            ->putJson(route('admin.mobil.update', $car), [
                'merk' => '',
                'model' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['merk', 'model']);

        // 15b: Valid update
        $payload = [
            'alasan_stok' => 'QA verifikasi unit', 'bukti_stok' => 'BAST-QA-1',
            'kode_mobil' => $car->kode_mobil,
            'nomor_rangka' => $car->nomor_rangka,
            'nomor_mesin' => $car->nomor_mesin,
            'merk' => $car->merk,
            'model' => $car->model,
            'tipe' => $car->tipe ?? 'Tipe Modifikasi Modal',
            'tahun' => (int) ($car->tahun ?? 2024),
            'warna' => $car->warna ?? 'Hitam',
            'transmisi' => $car->transmisi ?? 'automatic',
            'bahan_bakar' => $car->bahan_bakar ?? 'bensin',
            'kilometer' => 500,
            'harga_beli' => 150000000,
            'harga_jual' => 200000000,
            'kondisi' => $car->kondisi ?? 'baru',
            'status' => $car->status ?? 'tersedia',
            'deskripsi' => 'Deskripsi diperbarui via popup modal.',
        ];

        $updateResponse = $this->actingAs($admin)
            ->putJson(route('admin.mobil.update', $car), $payload)
            ->assertOk();

        $updateResponse->assertJson([
            'success' => true,
            'message' => 'Data kendaraan berhasil diperbarui.',
        ]);
    }

    /** 16. Nonaktifkan mobil via AJAX dan pencegahan penonaktifan unit terjual */
    public function test_deactivate_car_ajax_and_guards(): void
    {
        $admin = $this->actor('admin');
        $availableCar = $this->colorStockUnit(Mobil::where('status', 'tersedia')->firstOrFail());

        // Deactivate available car succeeds
        $this->actingAs($admin)
            ->patchJson(route('admin.mobil.deactivate', $availableCar), ['alasan_stok' => 'QA opname', 'bukti_stok' => 'BAST-QA-1'])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'nonaktif',
                'id_mobil' => $availableCar->id_mobil,
            ]);

        // Sold car cannot be deactivated (guards integrity)
        $soldCar = Mobil::where('status', 'terjual')->first();
        if ($soldCar) {
            $this->actingAs($admin)
                ->patchJson(route('admin.mobil.deactivate', $soldCar))
                ->assertStatus(422);
        }
    }

    /** 17. Owner strictly read-only: dicegah dari semua mutasi AJAX modal */
    public function test_owner_is_forbidden_from_all_modal_mutations(): void
    {
        $owner = $this->actor('owner');
        $car = Mobil::firstOrFail();

        // Stock adjustment blocked
        $this->actingAs($owner)
            ->postJson(route('admin.mobil.stock.adjust', $car), [
                'stok_baru' => 10,
                'alasan' => 'penambahan',
            ])
            ->assertForbidden();

        // Car update blocked
        $this->actingAs($owner)
            ->putJson(route('admin.mobil.update', $car), [
                'merk' => 'Toyota',
            ])
            ->assertForbidden();

        // Deactivation blocked
        $this->actingAs($owner)
            ->patchJson(route('admin.mobil.deactivate', $car))
            ->assertForbidden();
    }

    /** 18. Verifikasi pemisahan konten tab modal: Setiap tab memiliki struktur dan fungsi yang berbeda */
    public function test_modal_tab_segregation_and_distinct_content_rendering(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get(route('admin.mobil.index'))->assertOk();

        // 18a: CSS isolasi pane modal (hidden strictly display: none)
        $response->assertSee('.modal-pane.hidden', false);
        $response->assertSee('display: none !important;', false);

        // 18b: Detail Modal: 3 pane terpisah
        $response->assertSee('pane-detail-overview', false);
        $response->assertSee('pane-detail-specs', false);
        $response->assertSee('pane-detail-stock', false);
        $response->assertSee('Parameter Teknis Kendaraan', false);
        $response->assertSee('Identitas Registrasi Unit (Dealer Tracking)', false);
        $response->assertSee('Pesanan Member &amp; Reservasi Aktif', false);

        // 18c: Stock Modal: Ikhtisar vs Penyesuaian
        $response->assertSee('pane-stock-overview', false);
        $response->assertSee('pane-stock-adjust', false);
        $response->assertSee('Riwayat Pergerakan Stok Terbaru', false);
        $response->assertSee('stockDeltaBox', false);
        $response->assertSee('stockAdjustForm', false);
        $response->assertSee('alasan_preset', false);

        // 18d: Gallery Modal: Foto vs Warna Katalog vs Warna Unit
        $response->assertSee('pane-gallery-photos', false);
        $response->assertSee('pane-gallery-catalog-colors', false);
        $response->assertSee('pane-gallery-inventory-color', false);
        $response->assertSee('galleryActivePreview', false);
        $response->assertSee('catalogColorDetailPanel', false);
        $response->assertSee('Data Alokasi Stok Fisik Berdasarkan Warna', false);
        $response->assertSee('Warna Fisik Unit yang tercatat pada inventori operasional', false);
    }

    /** 19. Unified table: Menampilkan semua model (fisik, 0 stok, virtual katalog) dalam 1 tabel tanpa duplikasi */
    public function test_unified_vehicle_table_renders_all_catalog_models_including_virtual_and_zero_stock(): void
    {
        $admin = $this->actor('admin');

        // Buat 1 pre-order aktif untuk Bugatti Chiron
        $member = app(\App\Services\ActiveMemberCustomerService::class)->query()->firstOrFail();
        $model = app(\App\Services\CatalogVehicleIdentityService::class)->resolve('showcase:bugatti-chiron');
        app(\App\Services\MemberVehiclePreorderService::class)->create($member, $model);

        $response = $this->actingAs($admin)->get(route('admin.mobil.index'))->assertOk();

        // Verifikasi tidak ada lagi card top container terpisah "Model Katalog · Belum Ada Unit Fisik"
        $response->assertDontSee('Model Katalog · Belum Ada Unit Fisik', false);

        // Verifikasi Bugatti Chiron (virtual catalog dengan pre-order aktif) muncul di tabel
        $response->assertSee('Bugatti Chiron', false);
        $response->assertSee('Belum Ada Unit Fisik', false);
        $response->assertSee('1 permintaan', false);

        // Verifikasi Audi R8, BMW M4, McLaren 720S muncul sebagai model katalog di tabel
        $response->assertSee('Audi R8', false);
        $response->assertSee('BMW M4', false);
        $response->assertSee('McLaren 720S', false);

        // Pre-order link menuju /admin/pre-order-mobil
        $response->assertSee(route('admin.preorder-mobil.index'), false);
    }

    /** 20. Filter Status & Pre-order pada tabel terpadu */
    public function test_filter_by_status_and_preorder(): void
    {
        $admin = $this->actor('admin');

        // Buat 1 pre-order aktif untuk Bugatti Chiron
        $member = app(\App\Services\ActiveMemberCustomerService::class)->query()->firstOrFail();
        $model = app(\App\Services\CatalogVehicleIdentityService::class)->resolve('showcase:bugatti-chiron');
        app(\App\Services\MemberVehiclePreorderService::class)->create($member, $model);

        // Filter ada_preorder: Menampilkan unit dengan permintaan pre-order aktif (Bugatti Chiron)
        $responsePreorder = $this->actingAs($admin)->get(route('admin.mobil.index', ['status' => 'ada_preorder']))->assertOk();
        $responsePreorder->assertSee('Bugatti Chiron', false);
        $responsePreorder->assertSee('1 permintaan', false);

        // Filter habis_atau_belum_ada: Menampilkan unit habis & katalog tanpa fisik
        $responseHabis = $this->actingAs($admin)->get(route('admin.mobil.index', ['status' => 'habis_atau_belum_ada']))->assertOk();
        $responseHabis->assertSee('Belum Ada Unit Fisik', false);

        // Filter ready: Menampilkan unit yang ready
        $responseReady = $this->actingAs($admin)->get(route('admin.mobil.index', ['status' => 'ready']))->assertOk();
        $responseReady->assertSee('Ready', false);
    }

    /** 21. Seluruh tombol aksi kendaraan, header tambah mobil, dan badge pre-order membuka modal, bukan navigasi halaman baru */
    public function test_all_table_vehicle_actions_and_header_button_open_modal_without_navigation_links(): void
    {
        $admin = $this->actor('admin');

        // Buat 1 pre-order aktif untuk Bugatti Chiron
        $member = app(\App\Services\ActiveMemberCustomerService::class)->query()->firstOrFail();
        $model = app(\App\Services\CatalogVehicleIdentityService::class)->resolve('showcase:bugatti-chiron');
        app(\App\Services\MemberVehiclePreorderService::class)->create($member, $model);

        $response = $this->actingAs($admin)->get(route('admin.mobil.index'))->assertOk();

        // 1. Header "+ Tambah Mobil" adalah <button type="button"> dengan openVehicleActionModal('new', 'add_unit')
        $response->assertSee('onclick="openVehicleActionModal(\'new\', \'add_unit\')"', false);
        $response->assertDontSee('href="'.route('admin.mobil.create').'"', false);

        // 2. Tombol Aksi tabel fisik & virtual adalah satu tombol titik tiga (...) dengan toggleDropdownFixed
        $response->assertSee('class="btn-more-options"', false);
        $response->assertSee('toggleDropdownFixed', false);

        // 3. Badge Pre-Order pada tabel membuka modal preorder
        $response->assertSee('preorder-count-badge', false);

        // 4. Struktur markup modal terintegrasi: Modal dialog, form tambah unit, dan preorder nested view
        $response->assertSee('id="vehicleModalDialog"', false);
        $response->assertSee('addUnitVehicleForm', false);
        $response->assertSee('preorderListView', false);
        $response->assertSee('preorderReviewView', false);
        $response->assertSee('btn-preorder-back', false);

        // 5. Pastikan floating 3-dot dropdown memiliki aksi yang membuka modal
        $response->assertSee('floatingActionDropdown', false);
        $response->assertSee('data-action="detail"', false);
        $response->assertSee('data-action="stock"', false);
        $response->assertSee('data-action="edit"', false);
        $response->assertSee('data-action="add_unit"', false);
        $response->assertSee('data-action="preorder"', false);
        $response->assertSee('data-action="gallery"', false);
        $response->assertSee('data-action="deactivate"', false);
    }

    /** 22. Endpoint POST admin.mobil.store mendukung registrasi unit fisik via AJAX modal */
    public function test_ajax_store_vehicle_registers_physical_unit_and_returns_json_response(): void
    {
        $admin = $this->actor('admin');
        $randomSuffix = rand(10000, 99999);

        $payload = [
            'alasan_stok' => 'QA verifikasi unit', 'bukti_stok' => 'BAST-QA-1',
            'merk' => 'Toyota',
            'model' => 'GR Yaris',
            'tipe' => '1.6 Turbo AWD',
            'tahun' => 2024,
            'warna' => 'Platinum White Pearl',
            'kode_mobil' => 'TEST-GRY-' . $randomSuffix,
            'nomor_rangka' => 'VIN-TEST-GRY-' . $randomSuffix,
            'nomor_mesin' => 'ENG-TEST-G16E-' . $randomSuffix,
            'kondisi' => 'baru',
            'kilometer' => 0,
            'harga_beli' => 750000000,
            'harga_jual' => 880000000,
            'transmisi' => 'manual',
            'bahan_bakar' => 'bensin',
            'status' => 'tersedia',
            'deskripsi' => 'Unit fisik baru masuk showroom untuk pengujian modal aksi.',
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('admin.mobil.store'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'mobil' => ['id_mobil', 'nomor_rangka', 'nomor_mesin', 'merk', 'model'],
        ]);

        $this->assertDatabaseHas('mobil', [
            'nomor_rangka' => 'VIN-TEST-GRY-' . $randomSuffix,
            'nomor_mesin' => 'ENG-TEST-G16E-' . $randomSuffix,
            'status' => 'tersedia',
        ]);

        $this->assertDatabaseHas('vehicle_inventories', [
            'id_mobil' => $response->json('mobil.id_mobil'),
            'stock_total' => 1,
        ]);
    }

    /** 23. Owner dilarang mendaftarkan unit fisik kendaraan (Read-Only / 403 Forbidden) */
    public function test_owner_role_forbidden_from_submitting_vehicle_registration(): void
    {
        $owner = $this->actor('owner');

        $payload = [
            'alasan_stok' => 'QA verifikasi unit', 'bukti_stok' => 'BAST-QA-1',
            'merk' => 'Porsche',
            'model' => '911 GT3 RS',
            'warna' => 'Guards Red',
            'kode_mobil' => 'TEST-OWNER-FAIL',
            'nomor_rangka' => 'VIN-OWNER-FORBIDDEN',
            'nomor_mesin' => 'ENG-OWNER-FORBIDDEN',
            'kondisi' => 'baru',
            'kilometer' => 0,
            'harga_beli' => 4500000000,
            'harga_jual' => 5200000000,
            'status' => 'tersedia',
        ];

        $response = $this->actingAs($owner)
            ->postJson(route('admin.mobil.store'), $payload);

        $response->assertForbidden();
    }

    /** 24. Endpoint pre-order detail mengembalikan data JSON untuk review modal */
    public function test_preorder_detail_endpoint_returns_json_for_review_modal(): void
    {
        $admin = $this->actor('admin');

        $member = app(\App\Services\ActiveMemberCustomerService::class)->query()->firstOrFail();
        $model = app(\App\Services\CatalogVehicleIdentityService::class)->resolve('showcase:bugatti-chiron');
        $preorder = app(\App\Services\MemberVehiclePreorderService::class)->create($member, $model);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.preorder-mobil.detail', $preorder));

        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'code',
            'vehicle',
            'customer' => ['name', 'phone'],
        ]);
    }

    /** 25. Regression Test: Seluruh tab halaman Manajemen Mobil & Showroom mengembalikan HTTP 200 tanpa Undefined variable $identity */
    public function test_all_tabs_render_http_200_with_all_physical_and_virtual_models(): void
    {
        $admin = $this->actor('admin');

        $tabs = [
            route('admin.mobil.index'),
            route('admin.mobil.index', ['tab' => 'inventori']),
            route('admin.mobil.index', ['tab' => 'katalog']),
            route('admin.mobil.index', ['tab' => 'foto']),
        ];

        foreach ($tabs as $url) {
            $response = $this->actingAs($admin)->get($url);

            $response->assertOk();
            $response->assertDontSee('ErrorException', false);
            $response->assertDontSee('Undefined variable', false);
            $response->assertDontSee('500 Internal Server Error', false);
        }

        // Verifikasi render pada tab inventori menampilkan 6 model kendaraan fisik & virtual
        $inventoryResponse = $this->actingAs($admin)->get(route('admin.mobil.index', ['tab' => 'inventori']))->assertOk();

        // 1. Lexus LFA (Fisik)
        $inventoryResponse->assertSee('Lexus LFA', false);
        // 2. Nissan GT-R Nismo (Fisik)
        $inventoryResponse->assertSee('Nissan GT-R Nismo', false);
        // 3. Bugatti Chiron (Virtual Katalog)
        $inventoryResponse->assertSee('Bugatti Chiron', false);
        // 4. Audi R8 (Virtual Katalog)
        $inventoryResponse->assertSee('Audi R8', false);
        // 5. BMW M4 (Virtual Katalog)
        $inventoryResponse->assertSee('BMW M4', false);
        // 6. McLaren 720S (Virtual Katalog)
        $inventoryResponse->assertSee('McLaren 720S', false);

        // Verifikasi tidak ada error PHP Blade yang bocor ke HTML
        $inventoryResponse->assertDontSee('Undefined variable $identity', false);
    }

    /** 26. Pre-Order Count & Modal Payload Synchronized: jumlah permintaan di tabel sama persis dengan isi array modal */
    public function test_preorder_count_and_modal_json_payload_are_strictly_synchronized(): void
    {
        $admin = $this->actor('admin');

        // Pastikan ada preorder Bugatti Chiron aktif
        $member = app(\App\Services\ActiveMemberCustomerService::class)->query()->firstOrFail();
        $identity = app(\App\Services\CatalogVehicleIdentityService::class);
        $chiron = $identity->resolve('showcase:bugatti-chiron');

        $activeExisting = $identity->activePreorders($chiron);
        if ($activeExisting === 0) {
            app(\App\Services\MemberVehiclePreorderService::class)->create($member, $chiron);
        }

        $response = $this->actingAs($admin)->get(route('admin.mobil.index', ['tab' => 'inventori']))->assertOk();
        $content = $response->getContent();

        // 1. Script JSON modal payload untuk Bugatti Chiron wajib ada
        $this->assertMatchesRegularExpression('/<script type="application\/json" id="car-data-showcase:bugatti-chiron">([\s\S]*?)<\/script>/', $content, 'JSON payload car-data-showcase:bugatti-chiron harus ditemukan');

        preg_match('/<script type="application\/json" id="car-data-showcase:bugatti-chiron">([\s\S]*?)<\/script>/', $content, $matches);
        $payload = json_decode($matches[1], true);

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('preorders', $payload);
        $this->assertGreaterThanOrEqual(1, count($payload['preorders']), 'Array preorders di modal tidak boleh kosong jika di tabel ada pre-order aktif');

        $preorderCount = count($payload['preorders']);

        // 2. Pre-Order aktif di tabel: tidak ada tombol PO luar, badge berada di kolom PRE-ORDER AKTIF
        $response->assertDontSee("PO ({$preorderCount})", false);
        $response->assertSee("{$preorderCount} permintaan", false);

        // 3. Data member di payload harus lengkap
        $firstPo = $payload['preorders'][0];
        $this->assertArrayHasKey('id_preorder', $firstPo);
        $this->assertArrayHasKey('kode_preorder', $firstPo);
        $this->assertArrayHasKey('customer_name', $firstPo);
        $this->assertNotEmpty($firstPo['customer_name']);
        $this->assertArrayHasKey('status', $firstPo);
        $this->assertArrayHasKey('variant', $firstPo);
        $this->assertArrayHasKey('color', $firstPo);
    }

    /** 27. Single Three-Dot Action Menu: setiap baris hanya memiliki 1 tombol (...), lebar kompak, tanpa duplikasi */
    public function test_single_three_dot_action_menu_architecture(): void
    {
        $admin = $this->actor('admin');
        $response = $this->actingAs($admin)->get(route('admin.mobil.index', ['tab' => 'inventori']))->assertOk();
        $content = $response->getContent();

        // 1. Header AKSI memiliki lebar kompak 72px
        $response->assertSee('<th class="th-action sticky-col" style="width: 72px; min-width: 72px; text-align: center;">AKSI</th>', false);

        // 2. Tidak ada tombol aksi luar (.btn-row-action) di HTML tabel
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*class="[^"]*btn-row-action[^"]*"/', $content, 'Tidak boleh ada elemen tombol .btn-row-action di luar menu titik tiga');

        // 3. Setiap td.td-action hanya memiliki tepat satu tombol .btn-more-options
        preg_match_all('/<td class="td-action sticky-col"[^>]*>([\s\S]*?)<\/td>/', $content, $tdMatches);
        $this->assertNotEmpty($tdMatches[0], 'Kolom aksi td.td-action harus ditemukan pada tabel');

        foreach ($tdMatches[1] as $idx => $tdInner) {
            preg_match_all('/<button\b/', $tdInner, $innerButtons);
            $this->assertCount(1, $innerButtons[0], "td.td-action baris ke-{$idx} harus tepat memiliki 1 tombol");
            $this->assertStringContainsString('class="btn-more-options"', $tdInner);
            $this->assertStringContainsString('toggleDropdownFixed', $tdInner);
        }

        // 4. Model virtual Bugatti Chiron memiliki handler fixed dropdown
        $this->assertStringContainsString("toggleDropdownFixed(this, 'showcase:bugatti-chiron')", $content);

        // 5. Floating action dropdown portal terpasang dengan role menu
        $response->assertSee('id="floatingActionDropdown"', false);
        $response->assertSee('role="menu"', false);
    }
}
