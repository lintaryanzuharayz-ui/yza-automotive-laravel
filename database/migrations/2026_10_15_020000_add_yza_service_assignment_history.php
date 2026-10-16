<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_work_order_assignments', function (Blueprint $table): void {
            $table->id('id_service_work_order_assignment');
            $table->foreignId('id_servis')->constrained('servis', 'id_servis')->cascadeOnDelete();
            $table->foreignId('id_petugas')->constrained('petugas', 'id_petugas')->restrictOnDelete();
            $table->string('assignment_role', 20); // primary / assistant
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamps();
            $table->index(['id_servis', 'unassigned_at']);
            $table->index(['id_petugas', 'unassigned_at']);
        });

        DB::table('servis')->whereNotNull('id_petugas')->orderBy('id_servis')->get()->each(function ($service): void {
            DB::table('service_work_order_assignments')->insert([
                'id_servis' => $service->id_servis,
                'id_petugas' => $service->id_petugas,
                'assignment_role' => 'primary',
                'assigned_at' => $service->created_at ?: now(),
                'assigned_by' => $service->id_petugas,
                'reason' => 'Migrasi riwayat penugasan awal',
                'created_at' => $service->created_at ?: now(),
                'updated_at' => $service->updated_at ?: now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_work_order_assignments');
    }
};
