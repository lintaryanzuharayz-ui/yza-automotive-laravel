<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\BookingServis;
use App\Models\MemberVehiclePreorder;
use App\Models\PesananMobilMember;
use App\Models\Servis;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $query = app(\App\Services\NotificationStateService::class)->query($request->user());
        $filter = $request->string('filter')->toString();
        if ($filter === 'unread') {
            $query->where('is_read', false);
        }
        $notifications = $query->latest('created_at')->paginate(20)->withQueryString();
        $unreadCount = app(\App\Services\NotificationStateService::class)->query($request->user())
            ->where('is_read', false)->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }

    public function sync(Request $request)
    {
        $state = app(\App\Services\NotificationStateService::class)->snapshot(
            $request->user(), max(0, (int) $request->query('after_id', 0)),
            $request->boolean('panel'), (string) $request->query('filter', ''),
            array_map('intval', (array) $request->input('visible_ids', [])), $request->boolean('incremental'),
        );
        $payload = fn (Notifikasi $notification) => [
                'id' => (int) $notification->id_notifikasi, 'type' => (string) $notification->tipe,
                'title' => (string) $notification->judul, 'message' => (string) $notification->pesan,
                'url' => app(\App\Services\NotificationDestinationService::class)->for($request->user(), $notification),
                'open_url' => route('notifications.open', $notification),
                'created_at' => $notification->created_at?->toIso8601String(), 'is_read' => (bool) $notification->is_read,
            ];
        $state['notifications'] = $state['notifications']->map($payload)->values();
        $state['recent_notifications'] = $state['recent_notifications']->map($payload)->values();
        $state['unread_recent_notifications'] = $state['unread_recent_notifications']->map($payload)->values();
        return response()->json($state)->header('Cache-Control', 'no-store, private');
    }

    public function read(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->id_user === (int) $request->user()->id_user, 403);
        abort_unless(app(\App\Services\NotificationStateService::class)->query($request->user())->whereKey($notifikasi->getKey())->exists(), 403);
        $notifikasi->update(['is_read' => true]);
        if ($request->expectsJson()) return response()->json(app(\App\Services\NotificationStateService::class)->summary($request->user()))->header('Cache-Control', 'no-store, private');
        return back();
    }

    public function readAll(Request $request)
    {
        app(\App\Services\NotificationStateService::class)->query($request->user())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->expectsJson()) return response()->json(app(\App\Services\NotificationStateService::class)->summary($request->user()))->header('Cache-Control', 'no-store, private');

        return back()->with('status', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    public function open(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->id_user === (int) $request->user()->id_user, 403);
        abort_unless(app(\App\Services\NotificationStateService::class)->query($request->user())->whereKey($notifikasi->getKey())->exists(), 403);

        $notifikasi->update(['is_read' => true]);

        return redirect()->to(app(\App\Services\NotificationDestinationService::class)->for($request->user(), $notifikasi));
    }

    private function sourceUrl(Request $request, Notifikasi $notifikasi): string
    {
        $referenceId = (int) $notifikasi->referensi_id;
        $customerId = (int) $request->user()->pelanggan()->value('id_pelanggan');

        return match ($notifikasi->referensi_tipe) {
            'servis' => $this->serviceUrl($referenceId, $customerId),
            'booking_servis' => $this->bookingServiceUrl($referenceId, $customerId),
            'pesanan_mobil_member' => $this->memberVehicleOrderUrl($referenceId, $customerId),
            'pre_order_mobil' => $this->preorderUrl($referenceId, $customerId),
            default => route('notifications.index'),
        };
    }

    private function serviceUrl(int $serviceId, int $customerId): string
    {
        $service = Servis::query()->whereKey($serviceId)->where('id_pelanggan', $customerId)->first();
        return $service ? route('pelanggan.servis.approval', $service) : route('notifications.index');
    }

    private function bookingServiceUrl(int $bookingId, int $customerId): string
    {
        $booking = BookingServis::query()->whereKey($bookingId)->where('id_pelanggan', $customerId)->first();
        if (!$booking) return route('notifications.index');

        $service = $booking->servis()->where('id_pelanggan', $customerId)->first();
        return $service ? route('pelanggan.servis.approval', $service) : route('pelanggan.booking-servis.show', $booking);
    }

    private function memberVehicleOrderUrl(int $orderId, int $customerId): string
    {
        $order = PesananMobilMember::query()->whereKey($orderId)->where('id_pelanggan', $customerId)->first();
        return $order ? route('pelanggan.pesanan-mobil.show', $order) : route('notifications.index');
    }

    private function preorderUrl(int $preorderId, int $customerId): string
    {
        $exists = MemberVehiclePreorder::query()->whereKey($preorderId)->where('id_pelanggan', $customerId)->exists();
        return $exists ? route('pelanggan.preorder.index') : route('notifications.index');
    }
}
