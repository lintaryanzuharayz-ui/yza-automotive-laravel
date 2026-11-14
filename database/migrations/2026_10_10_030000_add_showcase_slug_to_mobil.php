<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mobil', 'showcase_slug')) {
            Schema::table('mobil', function (Blueprint $table): void {
                $table->string('showcase_slug', 80)->nullable()->unique('uq_mobil_showcase_slug')->after('kode_mobil');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mobil', 'showcase_slug')) {
            Schema::table('mobil', function (Blueprint $table): void {
                $table->dropUnique('uq_mobil_showcase_slug');
                $table->dropColumn('showcase_slug');
            });
        }
    }
};
