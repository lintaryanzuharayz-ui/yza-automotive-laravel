<?php
namespace App\Http\Controllers\Pelanggan;
use App\Http\Controllers\Controller;use App\Http\Requests\StoreBookingServisRequest;use App\Models\{BookingServis,JenisServis};use App\Services\BookingServisService;use Illuminate\Http\Request;use Throwable;
class BookingServisController extends Controller {
 private function customer(Request $r){$p=$r->user()->pelanggan;abort_unless($p,404);return $p;}
 public function index(Request $r){$rows=BookingServis::with(['kendaraan','jenisServis'])->where('id_pelanggan',$this->customer($r)->id_pelanggan)->latest('tanggal')->latest('jam')->paginate(15);return view('pelanggan.booking-servis.index',compact('rows'));}
 public function create(Request $r){$p=$this->customer($r);$vehicles=$p->kendaraan()->where('status','aktif')->orderBy('merk')->orderBy('model')->get();$types=JenisServis::where('status','aktif')->orderBy('nama_servis')->get();return view('pelanggan.booking-servis.create',compact('vehicles','types'));}
 public function store(StoreBookingServisRequest $req,BookingServisService $svc){try{$d=$req->validated();$b=$svc->create($this->customer($req)->id_pelanggan,(int)$d['id_kendaraan_pelanggan'],(int)$d['id_jenis_servis'],$d['tanggal'],$d['jam'],$d['keluhan']??null,$d['catatan']??null);return redirect()->route('pelanggan.booking-servis.index')->with('success','Booking servis berhasil diajukan.');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function show(Request $r,BookingServis $booking_servis){abort_unless((int)$booking_servis->id_pelanggan===(int)$this->customer($r)->id_pelanggan,403);$booking_servis->load(['kendaraan','jenisServis']);return view('pelanggan.booking-servis.show',compact('booking_servis'));}
 public function cancel(Request $r,BookingServis $booking_servis,BookingServisService $svc){abort_unless((int)$booking_servis->id_pelanggan===(int)$this->customer($r)->id_pelanggan,403);try{$svc->cancel($booking_servis->id_booking_servis,$this->customer($r)->id_pelanggan);return back()->with('success','Booking servis dibatalkan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
