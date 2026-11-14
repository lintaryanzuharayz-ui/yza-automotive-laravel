<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mobil', 'is_limited_edition')) {
            Schema::table('mobil', function (Blueprint $table): void {
                $table->boolean('is_limited_edition')->default(false)->after('status')->index('idx_mobil_limited_edition');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mobil', 'is_limited_edition')) {
            Schema::table('mobil', function (Blueprint $table): void {
                $table->dropIndex('idx_mobil_limited_edition');
                $table->dropColumn('is_limited_edition');
            });
        }
    }
};
