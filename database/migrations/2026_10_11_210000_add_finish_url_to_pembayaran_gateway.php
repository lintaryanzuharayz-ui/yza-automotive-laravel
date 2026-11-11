<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('pembayaran_gateway', 'finish_url')) {
            Schema::table('pembayaran_gateway', function (Blueprint $table): void {
                $table->string('finish_url', 500)->nullable()->after('redirect_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pembayaran_gateway', 'finish_url')) {
            Schema::table('pembayaran_gateway', function (Blueprint $table): void {
                $table->dropColumn('finish_url');
            });
        }
    }
};
