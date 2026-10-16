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
        $query = Notifikasi::where('id_user', $request->user()->id_user);
        $filter = $request->string('filter')->toString();
        if ($filter === 'unread') {
            $query->where('is_read', false);
        }
        $notifications = $query->latest('created_at')->paginate(20)->withQueryString();
        $unreadCount = Notifikasi::where('id_user', $request->user()->id_user)
            ->where('is_read', false)->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }

    public function sync(Request $request)
    {
        $userId = (int) $request->user()->id_user;
        $afterId = max(0, (int) $request->query('after_id', 0));
        $notifications = Notifikasi::query()
            ->where('id_user', $userId)
            ->when($afterId > 0, fn ($query) => $query->where('id_notifikasi', '>', $afterId))
            ->latest('id_notifikasi')
            ->limit(20)
            ->get();
        $latestId = (int) (Notifikasi::where('id_user', $userId)->max('id_notifikasi') ?: 0);
        $unreadCount = (int) Notifikasi::where('id_user', $userId)->where('is_read', false)->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'latest_id' => $latestId,
            'notifications' => $notifications->map(fn (Notifikasi $notification) => [
                'id' => (int) $notification->id_notifikasi,
                'type' => (string) $notification->tipe,
                'title' => (string) $notification->judul,
                'message' => (string) $notification->pesan,
                'url' => $this->sourceUrl($request, $notification),
                'open_url' => route('notifications.open', $notification),
                'created_at' => $notification->created_at?->toIso8601String(),
                'is_read' => (bool) $notification->is_read,
            ])->values(),
        ]);
    }

    public function read(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->id_user === (int) $request->user()->id_user, 403);
        $notifikasi->update(['is_read' => true]);
        return back();
    }

    public function readAll(Request $request)
    {
        Notifikasi::query()
            ->where('id_user', $request->user()->id_user)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('status', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    public function open(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->id_user === (int) $request->user()->id_user, 403);

        $notifikasi->update(['is_read' => true]);

        return redirect()->to($this->sourceUrl($request, $notifikasi));
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
