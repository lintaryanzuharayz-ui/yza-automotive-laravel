<?php
namespace App\Services;
use App\Models\{BookingCuci,BookingMobil,BookingServis,Pelanggan,PromoMember,PenjualanSparepart,Servis,TestDrive,TradeIn,Transaksi};
use Illuminate\Support\Collection;
class MemberDashboardService
{
 public function __construct(private MemberIdentityService $identity, private ServiceReminderService $reminderService){}
 public function for(Pelanggan $customer,$user):array
 {
  $this->identity->assertActive($customer,$user);$id=$customer->id_pelanggan;
  $vehicles=$customer->kendaraan()->latest()->limit(6)->get();$pointAccount=$customer->poinMember;$pointHistory=$customer->riwayatPoin()->with('transaksi')->latest('created_at')->limit(3)->get();$reminders=$this->reminderService->forCustomer($id);$promoPreview=PromoMember::visible()->orderBy('selesai_at')->limit(3)->get();$activePromoCount=PromoMember::visible()->count();
  $bookings=collect();
  foreach(BookingMobil::with('mobil')->where('id_pelanggan',$id)->latest('tanggal_booking')->limit(5)->get() as $b)$bookings->push($this->booking('Booking Mobil',$b->kode_booking,$b->tanggal_booking,$b->status,route('pelanggan.booking.show',$b),$b->mobil?->merk.' '.$b->mobil?->model));
  foreach(TestDrive::with('mobil')->where('id_pelanggan',$id)->latest()->limit(5)->get() as $b)$bookings->push($this->booking('Test Drive',$b->kode_test_drive,$b->tanggal,$b->status,route('pelanggan.test-drive.show',$b),$b->mobil?->merk.' '.$b->mobil?->model));
  foreach(BookingServis::with('kendaraan')->where('id_pelanggan',$id)->latest()->limit(5)->get() as $b)$bookings->push($this->booking('Booking Servis',$b->kode_booking,$b->tanggal,$b->status,route('pelanggan.booking-servis.show',$b),$b->kendaraan?->merk.' '.$b->kendaraan?->model));
  foreach(BookingCuci::with('kendaraan')->where('id_pelanggan',$id)->latest('tanggal')->limit(5)->get() as $b)$bookings->push($this->booking('Car Wash',$b->kode_booking,$b->tanggal,$b->status,route('pelanggan.booking-cuci.show',$b),$b->kendaraan?->merk.' '.$b->kendaraan?->model));
  $bookings=$bookings->sortByDesc(fn($b)=>$b['date']?->timestamp??0)->take(8)->values();
  $transactions=Transaksi::with(['pembayarans','servis','bookingCuci','penjualanSparepart','penjualanMobil','pembelianMobilBekas','tradeIn'])->where('id_pelanggan',$id)->latest('tanggal')->limit(12)->get()->map(fn($tx)=>$this->transaction($tx))->values();
  $services=Servis::with(['kendaraan','bookingServis'])->where('id_pelanggan',$id)->latest('id_servis')->limit(5)->get();
  $submissions=$customer->pengajuanMobil()->with('inspeksi')->latest()->limit(5)->get();
  $trades=TradeIn::with(['pengajuan','mobilDibeli','transaksi'])->where('id_pelanggan',$id)->latest()->limit(5)->get();
  $activities=$transactions->map(fn($x)=>['type'=>'Transaksi','title'=>$x['label'],'code'=>$x['code'],'date'=>$x['date']])->concat($bookings->map(fn($x)=>['type'=>$x['type'],'title'=>$x['type'],'code'=>$x['code'],'date'=>$x['date']]))->sortByDesc(fn($x)=>$x['date']?->timestamp??0)->take(8)->values();
  $terminal=['selesai','ditolak','dibatalkan','kadaluarsa','expired'];$upcomingActivities=$bookings->filter(fn($b)=>$b['date']&&!in_array($b['status'],$terminal,true)&&$b['date']->toDateString()>=now()->toDateString())->sortBy('date')->take(3)->values();$activeBookingCount=$bookings->reject(fn($b)=>in_array($b['status'],$terminal,true))->count();
  return compact('customer','vehicles','bookings','transactions','services','submissions','trades','activities','pointAccount','pointHistory','reminders','promoPreview','activePromoCount','upcomingActivities','activeBookingCount')+['memberId'=>$this->identity->memberCode($customer),'memberSince'=>$this->identity->since($customer),'pointRate'=>config('yza.points.rupiah_per_point',10000),'overdueReminderCount'=>collect($reminders)->where('status','overdue')->count(),'dueSoonReminderCount'=>collect($reminders)->where('status','due_soon')->count()];
 }
 private function booking(string $type,string $code,$date,string $status,string $url,?string $vehicle=null):array{return compact('type','code','date','status','url','vehicle')+['statusLabel'=>ucwords(str_replace('_',' ',$status))];}
 private function transaction(Transaksi $tx):array
 {
  $direction=$tx->jenis_transaksi==='pembelian_mobil'?'keluar':'masuk';$paid=(float)$tx->pembayarans->where('arah',$direction)->where('status','terverifikasi')->sum('jumlah');
  $labels=['servis'=>'Servis Kendaraan','cuci_mobil'=>'Car Wash','penjualan_sparepart'=>'Pembelian Spare Part','penjualan_mobil'=>'Pembelian Mobil','pembelian_mobil'=>'Pembayaran dari YZA','trade_in'=>'Trade-In'];
  $url=null;if($tx->jenis_transaksi==='servis')$url=route('pelanggan.invoice.servis',$tx);elseif($tx->jenis_transaksi==='cuci_mobil')$url=route('pelanggan.invoice.carwash',$tx);elseif($tx->jenis_transaksi==='penjualan_sparepart')$url=route('pelanggan.invoice.sparepart',$tx);elseif($tx->jenis_transaksi==='penjualan_mobil')$url=route('pelanggan.invoice.car-sale',$tx);elseif($tx->jenis_transaksi==='trade_in'&&$tx->tradeIn)$url=route('pelanggan.invoice.trade-in',$tx->tradeIn);elseif($tx->jenis_transaksi==='pembelian_mobil'&&$tx->pembelianMobilBekas)$url=route('pelanggan.invoice.used-car',$tx->pembelianMobilBekas);
  return ['id'=>$tx->id_transaksi,'code'=>$tx->kode_transaksi,'kind'=>$tx->jenis_transaksi,'label'=>$labels[$tx->jenis_transaksi]??ucwords(str_replace('_',' ',$tx->jenis_transaksi)),'date'=>$tx->tanggal,'total'=>$tx->total,'paid'=>$paid,'remaining'=>max(0,(float)$tx->total-$paid),'status'=>$tx->status,'direction'=>$direction,'directionLabel'=>$direction==='keluar'?'Pembayaran dari YZA':'Pembayaran Anda','url'=>$url];
 }
}
