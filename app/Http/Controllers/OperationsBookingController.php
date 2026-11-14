<?php
namespace App\Http\Controllers;
use App\Models\BookingMobil;use App\Services\BookingMobilService;use Illuminate\Http\Request;use Throwable;
class OperationsBookingController extends Controller {
 public function index(Request $r){
  $search=trim((string)$r->query('q'));$status=trim((string)$r->query('status'));$from=trim((string)$r->query('from'));$to=trim((string)$r->query('to'));
  $perPage=(int)$r->query('perPage',10);if(!in_array($perPage,[10,20,50],true))$perPage=10;
  $q=BookingMobil::with(['pelanggan','mobil.foto','petugasKonfirmasi'])->latest('tanggal_booking');
  if($search!=='')$q->where(function($query)use($search){$query->where('kode_booking','like','%'.$search.'%')->orWhereHas('pelanggan',fn($customer)=>$customer->where('nama_lengkap','like','%'.$search.'%'))->orWhereHas('mobil',fn($car)=>$car->where('kode_mobil','like','%'.$search.'%')->orWhere('merk','like','%'.$search.'%')->orWhere('model','like','%'.$search.'%'));});
  if($status!=='')$q->where('status',$status);if($from!=='')$q->whereDate('tanggal_booking','>=',$from);if($to!=='')$q->whereDate('tanggal_booking','<=',$to);
  $stats=['total'=>BookingMobil::count(),'waiting'=>BookingMobil::where('status','menunggu')->count(),'confirmed'=>BookingMobil::where('status','dikonfirmasi')->count(),'inactive'=>BookingMobil::whereIn('status',['kadaluarsa','dibatalkan'])->count(),'completed'=>BookingMobil::where('status','selesai')->count()];
  return view('operations.booking.index',['rows'=>$q->paginate($perPage)->withQueryString(),'role'=>$r->user()->roleName(),'search'=>$search,'status'=>$status,'from'=>$from,'to'=>$to,'perPage'=>$perPage,'bookingStats'=>$stats,'statuses'=>['menunggu','dikonfirmasi','kadaluarsa','dibatalkan','selesai']]);
 }
 public function show(Request $r,BookingMobil $booking_mobil){$booking_mobil->load(['pelanggan','mobil','petugasKonfirmasi']);return view('operations.booking.show',['booking'=>$booking_mobil,'role'=>$r->user()->roleName()]);}
 public function action(Request $r,BookingMobil $booking_mobil,string $action,BookingMobilService $svc){try{$role=$r->user()->roleName();$petugas=$svc->actorPetugasId($r->user()->id_user);if($action==='confirm'&&in_array($role,['admin','petugas'],true))$svc->confirm($booking_mobil->id_booking,(int)$petugas);elseif($action==='cancel'&&$role==='petugas')$svc->cancel($booking_mobil->id_booking);elseif($action==='complete'&&$role==='petugas')$svc->complete($booking_mobil->id_booking);else abort(403);return back()->with('success','Status booking diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
