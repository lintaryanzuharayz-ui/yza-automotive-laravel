<?php
namespace App\Http\Controllers;
use App\Models\{BookingCuci,PaketCuci,Transaksi};use App\Services\BookingCuciService;use Illuminate\Http\Request;use Illuminate\Support\Carbon;use Illuminate\View\View;use Throwable;
class OperationsBookingCuciController extends Controller {
 public function index(Request $r):View
 {
  $search=trim((string)$r->input('q',''));$status=(string)$r->input('status','');$date=(string)$r->input('date','');
  $perPage=in_array((int)$r->input('perPage',10),[10,20,50],true)?(int)$r->input('perPage',10):10;
  $base=BookingCuci::query();
  $rows=(clone $base)->with(['pelanggan','kendaraan','paket','petugas'])->when($status!=='',fn($q)=>$q->where('status',$status))->when($date!=='',fn($q)=>$q->whereDate('tanggal',$date))->when($search!=='',function($q)use($search){$term="%{$search}%";$q->where(function($w)use($term){$w->where('kode_booking','like',$term)->orWhereHas('pelanggan',fn($p)=>$p->where('nama_lengkap','like',$term))->orWhereHas('kendaraan',fn($v)=>$v->where('nomor_polisi','like',$term)->orWhere('model','like',$term));});})->latest('tanggal')->latest('jam')->paginate($perPage)->withQueryString();
  $today=now(config('app.timezone'))->toDateString();
  $eligible=fn($q)=>$q->whereNotIn('status',['dibatalkan']);
  $total=(clone $base)->count();$waiting=(clone $base)->where('status','menunggu')->count();$processing=(clone $base)->where('status','proses')->count();$completedToday=(clone $base)->where('status','selesai')->whereDate('tanggal',$today)->count();
  $revenue=(float)Transaksi::where('jenis_transaksi','cuci_mobil')->where('status','lunas')->whereDate('tanggal',$today)->sum('total');
  $favoritePackages=PaketCuci::query()->withCount(['bookingCuci'=>fn($q)=>$eligible($q)])->orderByDesc('booking_cuci_count')->orderBy('nama_paket')->get()->filter(fn($package)=>(int)$package->booking_cuci_count>0)->values();
  $favoriteTotal=max(1,(int)$favoritePackages->sum('booking_cuci_count'));
  $todayBookings=(clone $base)->whereDate('tanggal',$today)->whereNotIn('status',['dibatalkan']);
  $queueToday=(clone $todayBookings)->whereIn('status',['menunggu','diterima','proses'])->count();$assignedToday=(clone $todayBookings)->whereNotNull('id_petugas')->distinct('id_petugas')->count('id_petugas');
  return view('operations.booking-cuci.index',['rows'=>$rows,'role'=>$r->user()->roleName(),'search'=>$search,'status'=>$status,'date'=>$date,'perPage'=>$perPage,'statuses'=>['menunggu','diterima','proses','selesai','dibatalkan'],'bookingStats'=>['total'=>$total,'waiting'=>$waiting,'processing'=>$processing,'completedToday'=>$completedToday,'revenue'=>$revenue],'favoritePackages'=>$favoritePackages,'favoriteTotal'=>$favoriteTotal,'operational'=>['queue'=>$queueToday,'completed'=>$completedToday,'averageDuration'=>null,'assigned'=>$assignedToday]]);
 }
 public function show(Request $r,BookingCuci $booking_cuci){$booking_cuci->load(['pelanggan','kendaraan','paket','petugas','transaksi']);$promos=\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','cuci_mobil'])->get();return view('operations.booking-cuci.show',['booking'=>$booking_cuci,'role'=>$r->user()->roleName(),'promos'=>$promos]);}
 public function action(Request $r,BookingCuci $booking_cuci,string $action,BookingCuciService $svc){try{$petugas=$svc->actorPetugasId($r->user()->id_user);if($action==='confirm')$svc->confirm($booking_cuci->id_booking_cuci,(int)$petugas);elseif($action==='start')$svc->start($booking_cuci->id_booking_cuci,(int)$petugas);elseif($action==='finalize'){$tx=$svc->finalizeWithPromo($booking_cuci->id_booking_cuci,(int)$petugas,$r->integer('promo_id')?:null);return back()->with('success','Car Wash difinalisasi. Tagihan '.$tx->kode_transaksi.' dibuat.');}else abort(403);return back()->with('success','Status car wash diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
