<?php
namespace App\Http\Controllers;
use App\Models\BookingServis;use App\Services\BookingServisService;use Illuminate\Http\Request;use Throwable;
class OperationsBookingServisController extends Controller {
 public function index(Request $r){$q=BookingServis::with(['pelanggan','kendaraan','jenisServis'])->latest('tanggal')->latest('jam');if($r->filled('status'))$q->where('status',$r->string('status'));if($r->filled('q')){$term='%'.$r->string('q').'%';$q->where(function($w)use($term){$w->where('kode_booking','like',$term)->orWhereHas('pelanggan',fn($p)=>$p->where('nama_lengkap','like',$term))->orWhereHas('kendaraan',fn($v)=>$v->where('nomor_polisi','like',$term)->orWhere('model','like',$term));});}return view('operations.booking-servis.index',['rows'=>$q->paginate(20)->withQueryString(),'role'=>$r->user()->roleName()]);}
 public function show(Request $r,BookingServis $booking_servis){$booking_servis->load(['pelanggan','kendaraan','jenisServis']);return view('operations.booking-servis.show',['booking'=>$booking_servis,'role'=>$r->user()->roleName()]);}
 public function action(Request $r,BookingServis $booking_servis,string $action,BookingServisService $svc){try{if($action==='confirm')$svc->confirm($booking_servis->id_booking_servis);elseif($action==='reject')$svc->reject($booking_servis->id_booking_servis);else abort(403);return back()->with('success','Status booking servis diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
