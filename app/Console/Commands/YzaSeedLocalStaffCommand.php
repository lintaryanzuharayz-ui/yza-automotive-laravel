<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Petugas;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class YzaSeedLocalStaffCommand extends Command
{
    protected $signature = 'yza:seed-local-staff';
    protected $description = 'Provision idempotent YZA staff accounts for local/testing only';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing', 'uat'])) {
            $this->error('Dibatalkan: akun password awal hanya boleh dibuat pada local/testing/uat.');
            return self::FAILURE;
        }

        $roles = [
            'super_admin' => 'Akses penuh seluruh sistem',
            'owner' => 'Pimpinan bisnis dan laporan read-only',
            'sales' => 'Penjualan dan prospek pelanggan',
            'service_advisor' => 'Booking dan operasional service advisor',
            'mechanic' => 'Pekerjaan mekanik dan work order terassign',
            'warehouse_staff' => 'Sparepart dan inventori gudang',
            'finance' => 'Pembayaran dan laporan penerimaan',
            'operational_staff' => 'Operasional kendaraan, test drive, dan car wash',
            'quality_control' => 'QC dan PDI kendaraan',
        ];

        $permissions = [
            'dashboard.view' => 'Melihat dashboard',
            'sales.view' => 'Melihat penjualan',
            'sales.manage' => 'Mengelola penjualan',
            'customers.view' => 'Melihat pelanggan',
            'service.view' => 'Melihat layanan dan work order',
            'service.manage' => 'Mengelola work order',
            'service.assigned' => 'Mengelola pekerjaan yang ditugaskan',
            'inventory.view' => 'Melihat inventori',
            'inventory.manage' => 'Mengelola inventori',
            'finance.view' => 'Melihat keuangan',
            'finance.verify' => 'Memverifikasi pembayaran',
            'reports.view' => 'Melihat laporan',
            'staff.manage' => 'Mengelola karyawan dan role',
            'audit.view' => 'Melihat audit aktivitas',
            'qc.manage' => 'Mengelola QC dan PDI',
        ];

        DB::transaction(function () use ($roles, $permissions): void {
            foreach ($roles as $name => $description) {
                Role::firstOrCreate(['nama_role' => $name], ['deskripsi' => $description]);
            }
            foreach ($permissions as $code => $name) {
                Permission::firstOrCreate(['kode' => $code], ['nama' => $name]);
            }

            $grants = [
                'owner' => ['dashboard.view', 'sales.view', 'customers.view', 'reports.view', 'inventory.view', 'finance.view', 'service.view'],
                'sales' => ['dashboard.view', 'sales.view', 'sales.manage', 'customers.view'],
                'service_advisor' => ['dashboard.view', 'service.view', 'service.manage', 'customers.view'],
                'mechanic' => ['dashboard.view', 'service.view', 'service.assigned', 'inventory.view'],
                'warehouse_staff' => ['dashboard.view', 'inventory.view', 'inventory.manage'],
                'finance' => ['dashboard.view', 'finance.view', 'finance.verify', 'reports.view'],
                'operational_staff' => ['dashboard.view', 'service.view'],
                'quality_control' => ['dashboard.view', 'service.view', 'qc.manage'],
            ];
            foreach ($grants as $roleName => $codes) {
                $role = Role::where('nama_role', $roleName)->firstOrFail();
                $role->permissions()->sync(Permission::whereIn('kode', $codes)->pluck('id_permission'));
            }
        });

        $accounts = [
            ['role' => 'super_admin', 'username' => 'superadmin506', 'name' => 'Super Admin YZA'],
            ['role' => 'owner', 'username' => 'owner506', 'name' => 'Owner YZA'],
            ['role' => 'sales', 'username' => 'sales506', 'name' => 'Sales YZA'],
            ['role' => 'service_advisor', 'username' => 'advisor506', 'name' => 'Service Advisor YZA'],
            ['role' => 'mechanic', 'username' => 'mekanik506', 'name' => 'Mekanik YZA'],
            ['role' => 'warehouse_staff', 'username' => 'gudang506', 'name' => 'Petugas Gudang YZA'],
            ['role' => 'finance', 'username' => 'finance506', 'name' => 'Finance YZA'],
            ['role' => 'operational_staff', 'username' => 'operasional506', 'name' => 'Operasional YZA'],
            ['role' => 'quality_control', 'username' => 'qc506', 'name' => 'QC / PDI YZA'],
        ];

        foreach ($accounts as $account) {
            $role = Role::where('nama_role', $account['role'])->firstOrFail();
            $user = User::where('username', $account['username'])->first();
            if ($user) {
                $this->line("Lewati {$account['username']}: username sudah ada, data tidak diubah.");
                continue;
            }

            $user = User::forceCreate([
                'id_role' => $role->id_role,
                'username' => $account['username'],
                'email' => $account['username'].'@yza.local',
                'password' => Hash::make($account['username']),
                'status' => 'aktif',
            ]);
            Petugas::create([
                'id_user' => $user->id_user,
                'kode_petugas' => 'PTG-'.strtoupper($account['role']).'-'.strtoupper(substr(md5($account['username']), 0, 8)),
                'nama_lengkap' => $account['name'],
                'jabatan' => ucwords(str_replace('_', ' ', $account['role'])),
                'status' => 'aktif',
            ]);
            $this->info("Dibuat {$account['username']} ({$account['role']}).");
        }

        $this->info('Seed role, permission, dan akun lokal selesai.');
        return self::SUCCESS;
    }
}
