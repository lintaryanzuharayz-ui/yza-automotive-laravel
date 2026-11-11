<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id('id_permission');
            $table->string('kode', 80)->unique();
            $table->string('nama', 120);
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id_role');
            $table->unsignedBigInteger('id_permission');
            $table->timestamps();
            $table->primary(['id_role', 'id_permission']);
            $table->foreign('id_role')->references('id_role')->on('roles')->cascadeOnDelete();
            $table->foreign('id_permission')->references('id_permission')->on('permissions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
