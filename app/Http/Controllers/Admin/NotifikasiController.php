<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\Notifikasi; use Illuminate\Http\Request;
class NotifikasiController extends Controller { public function index(Request $r){$notifications=Notifikasi::where('id_user',$r->user()->id_user)->latest('created_at')->paginate(20);return view('admin.notifications.index',compact('notifications'));} public function read(Request $r,Notifikasi $notifikasi){abort_unless($notifikasi->id_user===$r->user()->id_user,403);$notifikasi->update(['is_read'=>true]);return back();} }
