<?php
namespace Tests\Feature;

use App\Models\{Mobil, Pelanggan, User};
use App\Services\PenjualanMobilService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class CarSalesStage7ATest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User { return User::whereHas('role', fn($q) => $q->where('nama_role', $role))->firstOrFail(); }
    private function actor(): int { return $this->user('petugas')->petugas->id_petugas; }
    private function car(): Mobil { return Mobil::where('status','tersedia')->whereNotIn('model', ['GR Supra', 'Supra MK4', 'Revuelto', 'SF90 Stradale', '911 GT3 RS', 'GT Black Series', 'DBS 770 Ultimate', 'Corvette Z06', 'Mustang Shelby GT500', 'Challenger SRT Hellcat Redeye', 'GT-R Nismo', 'LFA'])->firstOrFail(); }

    public function test_native_baseline_is_imported_without_payment_or_unit_mutation(): void
    {
        $this->assertSame(1, DB::table('penjualan_mobil')->count());
        $this->assertDatabaseHas('penjualan_mobil', ['id_penjualan'=>1, 'kode_penjualan'=>'SALE-202609-8203F497', 'status'=>'selesai']);
        $this->assertSame(11, DB::table('pembayaran')->count());
    }

    public function test_create_snapshots_price_creates_unpaid_process_sale_and_reserves_unit(): void
    {
        $car = $this->car();
        $customer = $this->user('pelanggan')->pelanggan;
        $original = (string)$car->harga_jual;
        $sale = app(PenjualanMobilService::class)->create(['id_pelanggan'=>$customer->id_pelanggan,'id_mobil'=>$car->id_mobil,'diskon'=>1000,'dp'=>0,'metode_pembelian'=>'tunai','catatan'=>'QA'], $this->actor());
        $this->assertSame($original, (string)$sale->harga_mobil);
        $this->assertSame('proses', $sale->status);
        $this->assertSame('belum_bayar', $sale->transaksi->status);
        $this->assertSame('penjualan_mobil', $sale->transaksi->jenis_transaksi);
        $this->assertSame('dibooking', $car->fresh()->status);
        $this->assertSame(11, DB::table('pembayaran')->count());
        DB::table('mobil')->where('id_mobil',$car->id_mobil)->update(['harga_jual'=>999999999]);
        $this->assertSame($original, (string)$sale->fresh()->harga_mobil);
    }

    public function test_invalid_discount_or_dp_is_rejected_atomically(): void
    {
        $car = $this->car(); $customer=$this->user('pelanggan')->pelanggan; $before=DB::table('transaksi')->count();
        $this->expectException(RuntimeException::class);
        try { app(PenjualanMobilService::class)->create(['id_pelanggan'=>$customer->id_pelanggan,'id_mobil'=>$car->id_mobil,'diskon'=>(float)$car->harga_jual+1,'dp'=>0,'metode_pembelian'=>'tunai'], $this->actor()); }
        finally { $this->assertSame($before, DB::table('transaksi')->count()); $this->assertSame('tersedia', $car->fresh()->status); }
    }

    public function test_customer_cannot_access_or_mutate_car_sales_operations(): void
    {
        $customer=$this->user('pelanggan');
        $this->actingAs($customer)->get('/admin/penjualan-mobil')->assertForbidden();
        $this->actingAs($customer)->post('/admin/penjualan-mobil')->assertForbidden();
    }
}
