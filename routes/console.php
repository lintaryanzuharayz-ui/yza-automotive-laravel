<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:reconcile-pending-orders', function () {
    app(\App\Services\StaffNotificationService::class)->reconcilePendingOrders();
    $this->info('Notifikasi pesanan dan pre-order tertunda diselaraskan. Status bisnis dan status baca lama tetap.');
})->purpose('Deliver missing admin notifications for pending member orders without duplicates');

Artisan::command('preorders:deliver-inventory-events', function () {
    $this->info('Inventory events processed: '.app(\App\Services\PreorderInventoryNotificationService::class)->drain());
})->purpose('Retry committed pre-order inventory outbox events idempotently');

\Illuminate\Support\Facades\Schedule::command('preorders:deliver-inventory-events')->everyMinute()->withoutOverlapping();
