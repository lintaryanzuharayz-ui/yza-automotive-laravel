<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('mobil','catalog_identity')) Schema::table('mobil',fn (Blueprint $table) => $table->string('catalog_identity',120)->nullable()->index());
        // No inferred name-based backfill, new Mobil rows, VINs, price or stock changes.
    }
    public function down(): void
    {
        if (Schema::hasColumn('mobil','catalog_identity')) Schema::table('mobil',fn (Blueprint $table) => $table->dropColumn('catalog_identity'));
    }
};
