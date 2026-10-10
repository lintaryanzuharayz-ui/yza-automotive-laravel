<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mobil_warna', function (Blueprint $table): void {
            $table->id('id_warna');
            $table->unsignedBigInteger('id_mobil');
            $table->string('slug', 80);
            $table->string('nama', 100);
            $table->string('hex', 7);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->unique(['id_mobil', 'slug'], 'uq_mobil_warna_slug');
            $table->foreign('id_mobil')->references('id_mobil')->on('mobil')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('mobil_galeri', function (Blueprint $table): void {
            $table->id('id_galeri');
            $table->unsignedBigInteger('id_warna');
            $table->enum('sudut', ['front', 'side', 'rear', 'interior']);
            $table->string('path', 255);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->unique(['id_warna', 'sudut'], 'uq_mobil_galeri_sudut');
            $table->foreign('id_warna')->references('id_warna')->on('mobil_warna')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobil_galeri');
        Schema::dropIfExists('mobil_warna');
    }
};
