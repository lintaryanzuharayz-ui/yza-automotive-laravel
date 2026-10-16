<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->string('filter')->toString();
        $notificationQuery = Notifikasi::query()
            ->where('id_user', $request->user()->id_user)
            ->when($filter === 'unread', fn ($query) => $query->where('is_read', false))
            ->latest('created_at');
        $notifications = $notificationQuery->paginate(20)->withQueryString();

        $unreadCount = Notifikasi::where('id_user', $request->user()->id_user)
            ->where('is_read', false)
            ->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount', 'filter'));
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
            'notifications' => $notifications->map(fn (Notifikasi $notification) => $this->payload($notification))->values(),
        ]);
    }

    public function read(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->id_user === (int) $request->user()->id_user, 403);
        $notifikasi->update(['is_read' => true]);

        return back();
    }

    public function open(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->id_user === (int) $request->user()->id_user, 403);
        $notifikasi->update(['is_read' => true]);

        return redirect()->to($this->notificationUrl($notifikasi));
    }

    private function payload(Notifikasi $notification): array
    {
        return [
            'id' => (int) $notification->id_notifikasi,
            'type' => (string) $notification->tipe,
            'title' => (string) $notification->judul,
            'message' => (string) $notification->pesan,
            'url' => $this->notificationUrl($notification),
            'open_url' => route('admin.notifikasi.open', $notification),
            'read_url' => route('admin.notifikasi.read', $notification),
            'created_at' => $notification->created_at?->toIso8601String(),
            'is_read' => (bool) $notification->is_read,
        ];
    }

    private function notificationUrl(Notifikasi $notification): string
    {
        return match ($notification->referensi_tipe) {
            'booking_servis' => route('admin.booking-servis.show', $notification->referensi_id),
            'servis' => route('admin.servis.show', $notification->referensi_id),
            'sparepart' => route('admin.sparepart.show', $notification->referensi_id),
            default => route('admin.notifikasi.index'),
        };
    }
}
