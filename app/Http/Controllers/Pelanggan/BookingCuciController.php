<?php
namespace App\Http\Controllers\Pelanggan;
use App\Http\Controllers\Controller;use App\Http\Requests\StoreBookingCuciRequest;use App\Models\{BookingCuci,PaketCuci};use App\Services\BookingCuciService;use Illuminate\Http\Request;use Throwable;
class BookingCuciController extends Controller {
 private function customer(Request $r){$p=$r->user()->pelanggan;abort_unless($p,404);return $p;}
 public function index(Request $r){$rows=BookingCuci::with(['kendaraan','paket','petugas'])->where('id_pelanggan',$this->customer($r)->id_pelanggan)->latest('tanggal')->latest('jam')->paginate(15);return view('pelanggan.booking-cuci.index',compact('rows'));}
 public function create(Request $r){$p=$this->customer($r);$vehicles=$p->kendaraan()->where('status','aktif')->orderBy('merk')->orderBy('model')->get();$packages=PaketCuci::where('status','aktif')->orderBy('harga')->get();return view('pelanggan.booking-cuci.create',compact('vehicles','packages'));}
 public function store(StoreBookingCuciRequest $req,BookingCuciService $svc){try{$d=$req->validated();$svc->create($this->customer($req)->id_pelanggan,(int)$d['id_kendaraan_pelanggan'],(int)$d['id_paket'],$d['tanggal'],$d['jam'],$d['catatan']??null);return redirect()->route('pelanggan.booking-cuci.index')->with('success','Booking cuci berhasil diajukan.');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function show(Request $r,BookingCuci $booking_cuci){abort_unless((int)$booking_cuci->id_pelanggan===(int)$this->customer($r)->id_pelanggan,403);$booking_cuci->load(['kendaraan','paket','petugas']);return view('pelanggan.booking-cuci.show',compact('booking_cuci'));}
 public function cancel(Request $r,BookingCuci $booking_cuci,BookingCuciService $svc){abort_unless((int)$booking_cuci->id_pelanggan===(int)$this->customer($r)->id_pelanggan,403);try{$svc->cancel($booking_cuci->id_booking_cuci,$this->customer($r)->id_pelanggan);return back()->with('success','Booking cuci dibatalkan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
