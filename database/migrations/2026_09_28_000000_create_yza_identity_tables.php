<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id_role', true);
            $table->string('nama_role', 30)->unique('uq_roles_nama');
            $table->string('deskripsi', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_user', true);
            $table->unsignedTinyInteger('id_role');
            $table->string('username', 50)->unique('uq_users_username');
            $table->string('email', 120)->nullable()->unique('uq_users_email');
            $table->string('password', 255);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->dateTime('last_login')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index(['id_role', 'status'], 'idx_users_role_status');
            $table->foreign('id_role', 'fk_users_role')->references('id_role')->on('roles')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('petugas', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_petugas', true);
            $table->unsignedBigInteger('id_user')->unique('uq_petugas_user');
            $table->string('kode_petugas', 30)->unique('uq_petugas_kode');
            $table->string('nama_lengkap', 120);
            $table->string('no_hp', 25)->nullable();
            $table->text('alamat')->nullable();
            $table->string('jabatan', 80)->nullable();
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('status', 'idx_petugas_status');
            $table->foreign('id_user', 'fk_petugas_user')->references('id_user')->on('users')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('pelanggan', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_pelanggan', true);
            $table->unsignedBigInteger('id_user')->nullable()->unique('uq_pelanggan_user');
            $table->string('kode_pelanggan', 30)->unique('uq_pelanggan_kode');
            $table->string('nama_lengkap', 120);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('nama_lengkap', 'idx_pelanggan_nama');
            $table->index('status', 'idx_pelanggan_status');
            $table->foreign('id_user', 'fk_pelanggan_user')->references('id_user')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
        Schema::dropIfExists('petugas');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
