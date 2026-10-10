<?php

namespace Tests\Feature;

use App\Models\KategoriSparepart;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SparepartMasterTest extends TestCase
{
    use DatabaseTransactions;

    private function userWithRole(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    public function test_admin_can_open_all_sparepart_master_pages(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $this->get('/admin/kategori-sparepart')->assertOk();
        $this->get('/admin/supplier')->assertOk();
        $this->get('/admin/sparepart')->assertOk();
        $this->get('/admin/sparepart/1')->assertOk();
    }

    public function test_duplicate_category_and_negative_price_are_rejected(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($this->userWithRole('admin'));
        $this->post('/admin/kategori-sparepart', ['nama_kategori' => 'Oli'])->assertSessionHasErrors('nama_kategori');
        $this->post('/admin/sparepart', ['kode_sparepart' => 'NEW', 'id_kategori' => 1, 'nama_sparepart' => 'Invalid', 'harga_beli' => -1, 'harga_jual' => 1, 'stok_minimum' => 0, 'satuan' => 'pcs'])->assertSessionHasErrors('harga_beli');
    }

    public function test_admin_can_create_and_edit_master_without_mutating_stock(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($this->userWithRole('admin'));
        $category = $this->post('/admin/kategori-sparepart', ['nama_kategori' => 'QA Category '.uniqid(), 'deskripsi' => 'QA'])->assertRedirect();
        $categoryId = KategoriSparepart::latest('id_kategori')->value('id_kategori');
        $this->post('/admin/supplier', ['kode_supplier' => 'QA-'.uniqid(), 'nama_supplier' => 'QA Supplier'])->assertRedirect();
        $this->post('/admin/sparepart', ['kode_sparepart' => 'QA-'.uniqid(), 'id_kategori' => $categoryId, 'nama_sparepart' => 'QA Sparepart', 'harga_beli' => '10.00', 'harga_jual' => '12.00', 'stok_minimum' => 2, 'satuan' => 'pcs'])->assertRedirect();
        $item = Sparepart::firstOrFail();
        $before = $item->stok;
        $this->put('/admin/sparepart/'.$item->id_sparepart, ['kode_sparepart' => $item->kode_sparepart, 'id_kategori' => $item->id_kategori, 'nama_sparepart' => 'Edited '.$item->nama_sparepart, 'merk' => $item->merk, 'harga_beli' => $item->harga_beli, 'harga_jual' => $item->harga_jual, 'stok_minimum' => $item->stok_minimum, 'satuan' => $item->satuan])->assertRedirect();
        $this->assertSame($before, Sparepart::find($item->id_sparepart)->stok);
    }

    public function test_relationship_and_stock_protection_are_preserved(): void
    {
        $item = Sparepart::with('kategori')->firstOrFail();
        $this->assertInstanceOf(KategoriSparepart::class, $item->kategori);
        $this->assertContains('stok', $item->getGuarded());
        $this->assertNotContains('stok', array_keys((new \App\Http\Requests\UpdateSparepartRequest)->rules()));
    }

    public function test_petugas_and_pelanggan_cannot_access_admin_master(): void
    {
        $this->actingAs($this->userWithRole('petugas'))->get('/admin/sparepart')->assertForbidden();
        $this->actingAs($this->userWithRole('pelanggan'))->get('/admin/sparepart')->assertForbidden();
        $this->actingAs($this->userWithRole('petugas'))->post('/admin/sparepart', [])->assertForbidden();
    }

    public function test_petugas_is_read_only_and_guest_is_redirected(): void
    {
        $this->get('/admin/sparepart')->assertRedirect('/login');
        $this->actingAs($this->userWithRole('petugas'))->get('/petugas/sparepart')->assertOk();
        $this->actingAs($this->userWithRole('petugas'))->get('/petugas/sparepart/1')->assertOk();
    }

    public function test_previous_mobil_public_smoke_still_works(): void
    {
        $this->get('/mobil')->assertOk();
        $this->get('/mobil/9')->assertOk();
    }
}
