<?php

namespace Tests\Feature;

use App\Models\{JenisServis, Sparepart, SparepartFitment, User};
use App\Services\{BookingServisService, ServisService, SparepartFitmentService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use Tests\TestCase;

class SparepartFitmentDependencyTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    private function service(): array
    {
        $member = $this->user('pelanggan');
        $vehicle = $member->pelanggan->kendaraan()->create([
            'merk' => 'QA', 'model' => 'Fitment Vehicle', 'tahun' => 2026,
            'nomor_polisi' => 'FT-' . random_int(1000, 9999), 'status' => 'aktif',
        ]);
        $type = JenisServis::where('status', 'aktif')->firstOrFail();
        $booking = app(BookingServisService::class)->create($member->pelanggan->id_pelanggan, $vehicle->id_kendaraan_pelanggan, $type->id_jenis_servis, now()->next(Carbon::TUESDAY)->toDateString(), '10:00', null, null);
        $booking->forceFill(['status' => 'diterima'])->save();
        $staff = \App\Models\ServiceStaffProfile::firstOrFail();
        $staff->update(['can_be_technician' => true, 'is_bookable' => true]);
        $service = app(ServisService::class)->createFromBooking($booking->id_booking_servis, $staff->id_petugas, 1000, null, null, true);
        app(ServisService::class)->updateInspection($service->id_servis, 'Temuan QA', null);

        return [$service->fresh(), $vehicle];
    }

    public function test_approval_page_renders_without_fitment_rows_and_marks_unknown(): void
    {
        [$service] = $this->service();
        $this->actingAs($this->user('admin'))
            ->get(route('admin.servis.persetujuan', $service))
            ->assertOk()
            ->assertSee('KOMPATIBILITAS BELUM TERVERIFIKASI', false);
    }

    public function test_valid_fitment_is_available_without_implying_other_parts_are_compatible(): void
    {
        [$service, $vehicle] = $this->service();
        $part = Sparepart::where('status', 'aktif')->firstOrFail();
        $unverified = Sparepart::create([
            'kode_sparepart' => 'QA-UNVERIFIED-' . random_int(1000, 9999),
            'id_kategori' => $part->id_kategori,
            'nama_sparepart' => 'QA Sparepart Tanpa Fitment',
            'harga_beli' => 10000,
            'harga_jual' => 15000,
            'satuan' => 'pcs',
            'status' => 'aktif',
            'stok' => 5,
            'stok_minimum' => 1,
        ]);
        SparepartFitment::create([
            'id_sparepart' => $part->id_sparepart,
            'merk_kendaraan' => $vehicle->merk,
            'model_kendaraan' => $vehicle->model,
            'tahun_mulai' => 2025,
            'tahun_akhir' => 2027,
            'status_verifikasi' => 'verified_fit',
            'sumber_bukti' => 'QA',
        ]);

        $response = $this->actingAs($this->user('admin'))->get(route('admin.servis.persetujuan', $service));
        $response->assertOk()->assertSee('KOMPATIBEL TERVERIFIKASI', false);
        $this->assertSame(SparepartFitmentService::UNVERIFIED, app(SparepartFitmentService::class)->statusFor($unverified, $vehicle)['status']);
    }

    public function test_stage_two_render_does_not_mutate_work_order_state_or_use_stale_fitment_variable(): void
    {
        [$service] = $this->service();
        $approvalCount = $service->approvalRequests()->count();
        $stockBefore = (int) Sparepart::sum('stok');

        $response = $this->actingAs($this->user('admin'))
            ->get(route('admin.servis.persetujuan', $service));

        $response->assertOk()
            ->assertSee('KOMPATIBILITAS SUKU CADANG', false)
            ->assertSee('KOMPATIBILITAS BELUM TERVERIFIKASI', false)
            ->assertDontSee('Undefined variable $fitmentStatuses', false);

        $this->assertSame($approvalCount, $service->fresh()->approvalRequests()->count());
        $this->assertSame($stockBefore, (int) Sparepart::sum('stok'));
    }

    public function test_stage_two_approval_form_exposes_active_stocked_spareparts(): void
    {
        [$service] = $this->service();
        $part = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->firstOrFail();

        $this->actingAs($this->user('admin'))
            ->get(route('admin.servis.persetujuan', $service))
            ->assertOk()
            ->assertSee('value="'.$part->id_sparepart.'"', false)
            ->assertSee($part->nama_sparepart, false);
    }

    public function test_admin_can_view_fitment_master_but_member_cannot(): void
    {
        $part = Sparepart::where('status', 'aktif')->firstOrFail();

        $this->actingAs($this->user('admin'))
            ->get(route('admin.sparepart.show', $part))
            ->assertOk()
            ->assertSee('Kompatibilitas Kendaraan', false);

        $this->actingAs($this->user('pelanggan'))
            ->get(route('admin.sparepart.show', $part))
            ->assertForbidden();
    }

    public function test_super_admin_can_store_fitment_master_data(): void
    {
        $part = Sparepart::where('status', 'aktif')->firstOrFail();
        $this->actingAs($this->user('super_admin'))
            ->post(route('admin.sparepart.fitment.store', $part), [
                'merk_kendaraan' => 'QA',
                'model_kendaraan' => 'Super Admin Vehicle',
                'tahun_mulai' => 2026,
                'tahun_akhir' => 2026,
                'kode_mesin' => 'QA-SUPER-ENGINE',
                'status_verifikasi' => 'verified_fit',
                'sumber_bukti' => 'QA test evidence',
                'nomor_part_referensi' => 'QA-SUPER-REF-001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sparepart_fitments', [
            'id_sparepart' => $part->id_sparepart,
            'model_kendaraan' => 'Super Admin Vehicle',
            'status_verifikasi' => 'verified_fit',
        ]);
    }

    public function test_duplicate_fitment_is_rejected_without_creating_a_second_row(): void
    {
        $part = Sparepart::where('status', 'aktif')->firstOrFail();
        $payload = [
            'merk_kendaraan' => 'QA',
            'model_kendaraan' => 'Duplicate Vehicle',
            'tahun_mulai' => 2026,
            'tahun_akhir' => 2026,
            'kode_mesin' => 'QA-ENGINE',
            'status_verifikasi' => 'verified_fit',
            'sumber_bukti' => 'QA test evidence',
            'nomor_part_referensi' => 'QA-REF-001',
        ];

        $admin = $this->actingAs($this->user('admin'));
        $admin->post(route('admin.sparepart.fitment.store', $part), $payload)->assertRedirect();
        $count = $part->fitments()->count();
        $admin->post(route('admin.sparepart.fitment.store', $part), $payload)
            ->assertRedirect()
            ->assertSessionHasErrors('fitment');

        $this->assertSame($count, $part->fresh()->fitments()->count());
    }

    public function test_search_returns_relevant_parts_with_explicit_compatibility_state(): void
    {
        [$service, $vehicle] = $this->service();
        $part = Sparepart::where('status', 'aktif')->firstOrFail();
        $part->update(['nama_sparepart' => 'QA Filter Oli Relevan '.random_int(1000, 9999)]);
        SparepartFitment::create([
            'id_sparepart' => $part->id_sparepart,
            'merk_kendaraan' => $vehicle->merk,
            'model_kendaraan' => $vehicle->model,
            'tahun_mulai' => 2026,
            'tahun_akhir' => 2026,
            'status_verifikasi' => 'verified_fit',
            'sumber_bukti' => 'QA',
        ]);

        $this->actingAs($this->user('admin'))
            ->getJson(route('admin.servis.sparepart-search', $service).'?q='.urlencode('QA Filter Oli Relevan'))
            ->assertOk()
            ->assertJsonPath('results.0.status', SparepartFitmentService::VERIFIED_FIT)
            ->assertJsonPath('results.0.can_select', true)
            ->assertJsonPath('results.0.label', 'KOMPATIBEL TERVERIFIKASI');

        $html = $this->actingAs($this->user('admin'))
            ->get(route('admin.servis.persetujuan', $service))
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('Pilih spare part aktif', $html);
    }

    public function test_sparepart_detail_auto_detects_authoritative_work_order_vehicle(): void
    {
        [$service, $vehicle] = $this->service();
        $part = Sparepart::where('status', 'aktif')->firstOrFail();

        $this->actingAs($this->user('admin'))
            ->get(route('admin.sparepart.show', $part).'?work_order='.$service->id_servis)
            ->assertOk()
            ->assertSee('Data kendaraan berhasil dibaca', false)
            ->assertSee($vehicle->merk.' '.$vehicle->model, false)
            ->assertSee('Work Order #'.$service->id_servis, false)
            ->assertSee('Belum Terverifikasi', false);
    }

    public function test_verified_fit_requires_a_traceable_reference_and_source(): void
    {
        $part = Sparepart::where('status', 'aktif')->firstOrFail();
        $response = $this->actingAs($this->user('admin'))->post(route('admin.sparepart.fitment.store', $part), [
            'status_verifikasi' => 'verified_fit',
        ]);

        $response->assertRedirect()->assertSessionHasErrors(['nomor_part_referensi', 'sumber_bukti']);
        $this->assertSame(0, $part->fresh()->fitments()->where('status_verifikasi', 'verified_fit')->count());
    }
}
