<?php
namespace App\Http\Controllers;
use App\Models\BookingMobil;use App\Services\BookingMobilService;use Illuminate\Http\Request;use Throwable;
class OperationsBookingController extends Controller {
 public function index(Request $r){$q=BookingMobil::with(['pelanggan','mobil','petugasKonfirmasi'])->latest();if($r->filled('status'))$q->where('status',$r->string('status'));return view('operations.booking.index',['rows'=>$q->paginate(20)->withQueryString(),'role'=>$r->user()->roleName()]);}
 public function show(Request $r,BookingMobil $booking_mobil){$booking_mobil->load(['pelanggan','mobil','petugasKonfirmasi']);return view('operations.booking.show',['booking'=>$booking_mobil,'role'=>$r->user()->roleName()]);}
 public function action(Request $r,BookingMobil $booking_mobil,string $action,BookingMobilService $svc){try{$role=$r->user()->roleName();$petugas=$svc->actorPetugasId($r->user()->id_user);if($action==='confirm'&&in_array($role,['admin','petugas'],true))$svc->confirm($booking_mobil->id_booking,(int)$petugas);elseif($action==='cancel'&&$role==='petugas')$svc->cancel($booking_mobil->id_booking);elseif($action==='complete'&&$role==='petugas')$svc->complete($booking_mobil->id_booking);else abort(403);return back()->with('success','Status booking diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
