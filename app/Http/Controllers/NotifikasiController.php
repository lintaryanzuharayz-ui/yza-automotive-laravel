<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notifikasi::where('id_user', $request->user()->id_user)
            ->latest('created_at')->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->id_user === (int) $request->user()->id_user, 403);
        $notifikasi->update(['is_read' => true]);
        return back();
    }
}
