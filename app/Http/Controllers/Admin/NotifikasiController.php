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
        $notificationQuery = app(\App\Services\NotificationStateService::class)->query($request->user())
            ->when($filter === 'unread', fn ($query) => $query->where('is_read', false))
            ->latest('created_at');
        $notifications = $notificationQuery->paginate(20)->withQueryString();

        $totalCount = app(\App\Services\NotificationStateService::class)->query($request->user())->count();
        $unreadCount = app(\App\Services\NotificationStateService::class)->query($request->user())
            ->where('is_read', false)
            ->count();

        return view('admin.notifications.index', compact('notifications', 'totalCount', 'unreadCount', 'filter'));
    }

    public function sync(Request $request)
    {
        $state = app(\App\Services\NotificationStateService::class)->snapshot(
            $request->user(), max(0, (int) $request->query('after_id', 0)),
            $request->boolean('panel'), (string) $request->query('filter', ''),
            array_map('intval', (array) $request->input('visible_ids', [])), $request->boolean('incremental'),
        );
        $payload = fn (Notifikasi $notification) => $this->payload($notification);
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

    public function open(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->id_user === (int) $request->user()->id_user, 403);
        abort_unless(app(\App\Services\NotificationStateService::class)->query($request->user())->whereKey($notifikasi->getKey())->exists(), 403);
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
        return app(\App\Services\NotificationDestinationService::class)->for(request()->user(), $notification);
    }
}
