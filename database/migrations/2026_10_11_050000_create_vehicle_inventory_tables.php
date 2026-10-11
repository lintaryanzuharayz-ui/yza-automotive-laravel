<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_inventories', function (Blueprint $table): void {
            $table->id('id_vehicle_inventory');
            $table->string('inventory_key', 255)->unique('uq_vehicle_inventory_key');
            $table->unsignedBigInteger('id_mobil')->nullable();
            $table->unsignedInteger('stock_total')->default(0);
            $table->unsignedInteger('sold_units')->default(0);
            $table->timestamps();
            $table->foreign('id_mobil')->references('id_mobil')->on('mobil')->nullOnDelete()->cascadeOnUpdate();
            $table->index('id_mobil', 'idx_vehicle_inventory_mobil');
        });

        Schema::create('vehicle_inventory_histories', function (Blueprint $table): void {
            $table->id('id_vehicle_inventory_history');
            $table->unsignedBigInteger('id_vehicle_inventory');
            $table->string('jenis', 40);
            $table->integer('jumlah_perubahan')->default(0);
            $table->unsignedInteger('stock_before')->default(0);
            $table->unsignedInteger('stock_after')->default(0);
            $table->unsignedInteger('sold_before')->default(0);
            $table->unsignedInteger('sold_after')->default(0);
            $table->string('referensi_tipe', 80)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('id_vehicle_inventory')->references('id_vehicle_inventory')->on('vehicle_inventories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->index(['id_vehicle_inventory', 'created_at'], 'idx_vehicle_inventory_history');
        });

        $targets = [
            ['Toyota', 'GR Supra', 5], ['Lamborghini', 'Revuelto', 8], ['Toyota', 'Supra MK4', 3],
            ['Ferrari', 'SF90 Stradale', 6], ['Porsche', '911 GT3 RS', 2], ['Mercedes Amg', 'GT Black Series', 4],
            ['Aston Martin', 'DBS 770 Ultimate', 7], ['Chevrolet', 'Corvette Z06', 5], ['Ford', 'Mustang Shelby GT500', 3],
            ['Dodge', 'Challenger SRT Hellcat Redeye', 8], ['Nissan', 'GT-R Nismo', 6], ['Lexus', 'LFA', 2],
        ];

        foreach ($targets as [$brand, $model, $stock]) {
            $car = DB::table('mobil')->where('merk', $brand)->where('model', $model)->where('status', '!=', 'nonaktif')->orderBy('id_mobil')->first();
            if (!$car) throw new RuntimeException("Kendaraan target {$brand} {$model} tidak ditemukan.");

            $inventoryKey = implode('|', array_map(static fn ($value): string => (string) ($value ?? ''), [
                strtolower((string) $car->merk), strtolower((string) $car->model), strtolower((string) $car->tipe), (string) $car->tahun,
                strtolower((string) $car->warna), strtolower((string) $car->transmisi), strtolower((string) $car->bahan_bakar),
                strtolower((string) $car->kondisi), (bool) $car->is_limited_edition ? '1' : '0',
            ]));
            $sold = (int) DB::table('penjualan_mobil')->where('id_mobil', $car->id_mobil)->where('status', 'selesai')->count();
            if ($sold > $stock) throw new RuntimeException("Histori penjualan {$brand} {$model} melebihi stok operasional target.");

            $id = DB::table('vehicle_inventories')->insertGetId([
                'inventory_key' => $inventoryKey, 'id_mobil' => $car->id_mobil, 'stock_total' => $stock,
                'sold_units' => $sold, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('vehicle_inventory_histories')->insert([
                'id_vehicle_inventory' => $id, 'jenis' => 'stok_awal_operasional', 'jumlah_perubahan' => $stock,
                'stock_before' => 0, 'stock_after' => $stock, 'sold_before' => 0, 'sold_after' => $sold,
                'referensi_tipe' => 'migration', 'catatan' => 'Penetapan stok operasional awal YZA Automotive.', 'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inventory_histories');
        Schema::dropIfExists('vehicle_inventories');
    }
};
