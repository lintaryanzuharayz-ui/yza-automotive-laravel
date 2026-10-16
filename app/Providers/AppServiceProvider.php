<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\{Servis, ServiceWorkOrderAssignment};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Servis::created(function (Servis $servis): void {
            if ($servis->id_petugas && class_exists(ServiceWorkOrderAssignment::class)) {
                ServiceWorkOrderAssignment::create([
                    'id_servis' => $servis->id_servis,
                    'id_petugas' => $servis->id_petugas,
                    'assignment_role' => 'primary',
                    'assigned_at' => now(),
                    'assigned_by' => $servis->id_petugas,
                    'reason' => 'Penugasan awal Work Order',
                ]);
            }
        });
    }
}
