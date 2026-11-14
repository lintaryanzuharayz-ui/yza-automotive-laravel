<?php

namespace App\Services;

use App\Models\{Notifikasi, User};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NotificationStateService
{
    public function query(User $user): Builder
    {
        $query = Notifikasi::where('id_user', $user->id_user);
        if ($user->roleKey() === 'mechanic') {
            $staffId = $user->petugas?->id_petugas;
            $query->where(function ($q) use ($staffId) {
                $q->whereNull('referensi_tipe')->orWhere('referensi_tipe', '!=', 'servis')
                    ->orWhereIn('referensi_id', DB::table('service_work_order_assignments')
                        ->select('id_servis')->where('id_petugas', $staffId ?: 0)
                        ->whereIn('assignment_role', ['primary', 'assistant'])
                        ->where('assigned_at', '<=', now())->whereNull('unassigned_at'));
            });
            $query->where(function ($q) use ($staffId) {
                $q->whereNull('referensi_tipe')->orWhere('referensi_tipe', '!=', 'service_assignment')
                    ->orWhereIn('referensi_id', DB::table('service_work_order_assignments')
                        ->select('id_service_work_order_assignment')->where('id_petugas', $staffId ?: 0)
                        ->whereIn('assignment_role', ['primary', 'assistant'])
                        ->where('assigned_at', '<=', now())->whereNull('unassigned_at'));
            });
        }
        return $query;
    }

    public function category(?string $reference, ?string $type, ?string $title): string
    {
        return match ($reference) {
            'pesanan_mobil_member' => $title === 'Serah terima selesai' ? 'garage' : 'orders',
            'pre_order_mobil' => 'buy',
            'kendaraan_pelanggan' => 'garage',
            'servis', 'service_assignment', 'booking_servis', 'booking_cuci', 'test_drive', 'trade_in', 'pengajuan_mobil' => 'services',
            'booking_mobil' => 'activity',
            'sparepart', 'stok_masuk' => 'inventory',
            'pembayaran', 'transaksi' => 'finance',
            'promo_member', 'poin', 'membership' => 'benefits',
            default => match ($type) { 'promo', 'poin', 'membership' => 'benefits', default => 'other' },
        };
    }

    public function menu(User $user, ?string $reference, ?string $type, ?string $title): ?string
    {
        if ($user->roleKey() === 'pelanggan') return $this->category($reference, $type, $title);
        if ($user->roleKey() === 'owner') return null;
        return match ($reference) {
            'pesanan_mobil_member' => $user->roleKey() === 'sales' ? 'sales.orders.index' : 'admin.pesanan-mobil.index',
            'pre_order_mobil' => 'admin.preorder-mobil.index',
            'servis', 'service_assignment' => 'admin.servis.index',
            'booking_servis' => 'admin.booking-servis.index',
            'booking_cuci' => 'admin.booking-cuci.index',
            'booking_mobil' => 'admin.booking.index',
            'test_drive' => $user->roleKey() === 'sales' ? 'sales.test-drives.index' : 'admin.test-drive.index',
            'sparepart', 'stok_masuk' => 'admin.sparepart.index',
            'pembayaran', 'transaksi' => 'admin.payments.index',
            default => null,
        };
    }

    public function summary(User $user): array
    {
        $snapshotStarted = microtime(true);
        $categories = array_fill_keys(['buy', 'orders', 'garage', 'services', 'activity', 'benefits', 'inventory', 'finance', 'other'], 0);
        $menus = [];
        $groups = $this->query($user)->where('is_read', false)
            ->selectRaw('referensi_tipe, tipe, judul, COUNT(*) AS aggregate_count')
            ->groupBy('referensi_tipe', 'tipe', 'judul')->get();
        foreach ($groups as $group) {
            $count = (int) $group->aggregate_count;
            $category = $this->category($group->referensi_tipe, $group->tipe, $group->judul);
            $categories[$category] += $count;
            $menu = $this->menu($user, $group->referensi_tipe, $group->tipe, $group->judul);
            if ($menu) $menus[$menu] = ($menus[$menu] ?? 0) + $count;
        }
        $pending = $user->roleKey() === 'pelanggan' || $user->roleKey() === 'owner'
            ? [] : app(AdminNotificationBadgeService::class)->for((int) $user->id_user);
        unset($pending['notifications']);
        $pendingActions = [];
        foreach ([
            'orders' => ['Pesanan menunggu verifikasi', $user->roleKey() === 'sales' ? 'sales.orders.index' : 'admin.pesanan-mobil.index'],
            'preorders' => ['Pre-order aktif', 'admin.preorder-mobil.index'],
            'payments' => ['Pembayaran perlu ditinjau', 'admin.payments.index'],
            'bookings' => ['Booking menunggu', 'admin.booking.index'],
            'testDrives' => ['Test drive menunggu', $user->roleKey() === 'sales' ? 'sales.test-drives.index' : 'admin.test-drive.index'],
            'tradeIns' => ['Trade-in perlu diproses', 'admin.trade-in.index'],
            'stock' => ['Sparepart mencapai stok minimum', 'admin.sparepart.index'],
        ] as $key => [$label, $route]) {
            if (($pending[$key] ?? 0) > 0 && \Illuminate\Support\Facades\Route::has($route)) {
                $pendingActions[] = ['key' => $key, 'label' => $label, 'count' => (int) $pending[$key], 'url' => route($route)];
            }
        }
        return ['account_id' => (int) $user->id_user, 'snapshot_started_at' => $snapshotStarted,
            'latest_id' => (int) ($this->query($user)->max('id_notifikasi') ?: 0), 'total_count' => $this->query($user)->count(),
            'unread_count' => array_sum($categories), 'category_unread' => $categories, 'menu_unread' => $menus, 'pending_counts' => $pending,
            'pending_actions' => $pendingActions];
    }

    public function snapshot(User $user, int $afterId = 0, bool $panel = false, string $filter = '', array $visibleIds = [], bool $incremental = false): array
    {
        return DB::transaction(function () use ($user, $afterId, $panel, $filter, $visibleIds, $incremental) {
            $state = $this->summary($user);
            $query = $this->query($user);
            // Poll from oldest unseen ID, so bursts larger than a page never skip records.
            $updates = (clone $query)->when(!$panel && $afterId > 0, fn ($q) => $q->where('id_notifikasi', '>', $afterId))
                ->when($panel && $filter === 'unread', fn ($q) => $q->where('is_read', false));
            $state['notifications'] = !$panel && ($afterId > 0 || $incremental)
                ? $updates->oldest('id_notifikasi')->limit(20)->get() : $updates->latest('id_notifikasi')->limit($panel ? 4 : 20)->get();
            $state['cursor'] = !$panel && ($afterId > 0 || $incremental)
                ? max($afterId, (int) ($state['notifications']->max('id_notifikasi') ?: 0)) : $state['latest_id'];
            $state['recent_notifications'] = (clone $query)->latest('id_notifikasi')->limit(4)->get();
            $state['unread_recent_notifications'] = (clone $query)->where('is_read', false)->latest('id_notifikasi')->limit(4)->get();
            $state['visible_read_state'] = (clone $query)->whereIn('id_notifikasi', array_slice($visibleIds, 0, 40))
                ->pluck('is_read', 'id_notifikasi');
            return $state;
        });
    }
}
