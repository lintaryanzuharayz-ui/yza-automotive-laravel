<?php
namespace App\Http\Controllers;
use App\Models\BookingMobil;use App\Services\BookingMobilService;use Illuminate\Http\Request;use Throwable;
class OperationsBookingController extends Controller {
 public function index(Request $r){$q=BookingMobil::with(['pelanggan','mobil','petugasKonfirmasi'])->latest();if($r->filled('status'))$q->where('status',$r->string('status'));$role=$r->user()->isAdmin()?'admin':'petugas';return view('operations.booking.index',['rows'=>$q->paginate(20)->withQueryString(),'role'=>$role]);}
 public function show(Request $r,BookingMobil $booking_mobil){$booking_mobil->load(['pelanggan','mobil','petugasKonfirmasi']);$role=$r->user()->isAdmin()?'admin':'petugas';return view('operations.booking.show',['booking'=>$booking_mobil,'role'=>$role]);}
 public function action(Request $r,BookingMobil $booking_mobil,string $action,BookingMobilService $svc){try{$petugas=$svc->actorPetugasId($r->user()->id_user);$isStaff=$r->user()->isStaff();$isAdmin=$r->user()->isAdmin();if($action==='confirm'&&($isAdmin||$isStaff))$svc->confirm($booking_mobil->id_booking,(int)$petugas);elseif($action==='cancel'&&$isStaff)$svc->cancel($booking_mobil->id_booking);elseif($action==='complete'&&$isStaff)$svc->complete($booking_mobil->id_booking);else abort(403);return back()->with('success','Status booking diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
