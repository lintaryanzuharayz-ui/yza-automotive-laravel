<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesananMobilMember;
use App\Services\{InventoryService, MemberVehiclePurchaseService, VehicleGalleryService};
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MemberVehiclePurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $gallery = app(VehicleGalleryService::class);
        $query = PesananMobilMember::with(['pelanggan', 'mobil.foto', 'pdi', 'transaksi.pembayarans', 'penjualan'])->latest('created_at');
        $keyword = trim((string) $request->input('q'));
        $status = (string) $request->input('status');
        $from = (string) $request->input('from');
        $to = (string) $request->input('to');

        $query->when($keyword !== '', function ($builder) use ($keyword): void {
            $builder->where(function ($search) use ($keyword): void {
                $search->where('nomor_pesanan', 'like', "%{$keyword}%")
                    ->orWhere('nama_pembeli', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_merk', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_model', 'like', "%{$keyword}%")
                    ->orWhereHas('penjualan', function ($q) use ($keyword) {
                        $q->where('kode_penjualan', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('pelanggan', function ($q) use ($keyword) {
                        $q->where('nama_lengkap', 'like', "%{$keyword}%")
                            ->orWhere('kode_pelanggan', 'like', "%{$keyword}%")
                            ->orWhere('no_hp', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('mobil', function ($q) use ($keyword) {
                        $q->where('kode_mobil', 'like', "%{$keyword}%");
                    });
            });
        });

        $query->when($status !== '', function ($builder) use ($status) {
            if ($status === 'proses') {
                $builder->whereIn('status', ['lunas', 'pdi_lulus', 'siap_diambil']);
            } elseif ($status === 'dikonfirmasi') {
                $builder->where('status', 'menunggu_pembayaran');
            } else {
                $builder->where('status', $status);
            }
        });

        $query->when($from !== '', fn ($builder) => $builder->whereDate('created_at', '>=', $from));
        $query->when($to !== '', fn ($builder) => $builder->whereDate('created_at', '<=', $to));

        $orders = $query->paginate(10)->withQueryString();
        $orders->getCollection()->each(function (PesananMobilMember $order) use ($gallery): void {
            $sameVehicle = $order->matchesCatalogVehicle();
            $selectedGallery = $sameVehicle ? $gallery->forColor($order->mobil, $order->snapshot_warna) : [];
            $image = $selectedGallery['images'][0]['src'] ?? null;
            // A general showroom hero is not a photo of the ordered vehicle.
            if ($image === asset('assets/images/hero/yza-hero-reference-v3.png')) $image = null;
            $order->setAttribute('ordered_vehicle_image', $image ?: asset('assets/images/vehicle-photo-unavailable.svg'));
            $order->setAttribute('ordered_vehicle_swatch', $selectedGallery['swatch'] ?? null);
            $isAggregate = $order->mobil && app(\App\Services\VehicleInventoryService::class)->find($order->mobil);
            // Aggregate stock is a catalog variant, not an allocated physical VIN.
            $order->setAttribute('ordered_vehicle_vin', $order->pdi?->nomor_rangka_snapshot
                ?: (!$isAggregate && $sameVehicle ? $order->mobil?->nomor_rangka : null));
            $order->setAttribute('ordered_vehicle_engine_number', $order->pdi?->nomor_mesin_snapshot
                ?: (!$isAggregate && $sameVehicle ? $order->mobil?->nomor_mesin : null));
        });
        $stats = [
            'total' => PesananMobilMember::count(),
            'semua' => PesananMobilMember::count(),
            'menunggu_verifikasi' => PesananMobilMember::where('status', 'menunggu_verifikasi')->count(),
            'dikonfirmasi' => PesananMobilMember::where('status', 'menunggu_pembayaran')->count(),
            'proses' => PesananMobilMember::whereIn('status', ['lunas', 'pdi_lulus', 'siap_diambil'])->count(),
            'selesai' => PesananMobilMember::where('status', 'diserahkan')->count(),
        ];

        $detailOrder = null;
        if ($request->filled('detail')) {
            $detailOrder = PesananMobilMember::with(['pelanggan', 'mobil.foto', 'transaksi.pembayarans', 'penjualan', 'pdi', 'serahTerima'])->find($request->input('detail'));
        }

        $checklistDefinition = app(MemberVehiclePurchaseService::class)->checklistDefinition();
        $orderCustomers = app(\App\Services\ActiveMemberCustomerService::class)->query()
            ->with(['membership','user'])->orderBy('nama_lengkap')->orderBy('id_pelanggan')->get();
        $orderCars = \App\Models\Mobil::where('status', 'tersedia')->orderBy('merk')->get();
        $spkVehicles = app(\App\Services\SmartSpkOrderService::class)->vehicles();
        $spkCatalog = app(\App\Services\SmartSpkOrderService::class)->catalog($spkVehicles);
        $spkStorageReady = app(\App\Services\SmartSpkOrderService::class)->ready();

        return view('admin.pesanan-mobil.index', compact('orders', 'stats', 'keyword', 'status', 'from', 'to', 'detailOrder', 'checklistDefinition', 'orderCustomers', 'orderCars', 'spkVehicles', 'spkCatalog', 'spkStorageReady'));
    }
    public function store(Request $request, MemberVehiclePurchaseService $service)
    {
        $data = $request->validateWithBag('createOrder', [
            'id_pelanggan' => ['required', 'integer', 'exists:pelanggan,id_pelanggan'],
            'id_user' => ['prohibited'],
            'id_mobil' => ['required', 'integer', 'exists:mobil,id_mobil'],
            'nama_pembeli' => ['required', 'string', 'max:120'],
            'email_pembeli' => ['nullable', 'email', 'max:120'],
            'telepon_pembeli' => ['required', 'string', 'max:25'],
            'alamat_pembeli' => ['required', 'string', 'max:2000'],
            'syarat_disetujui' => ['required', 'accepted'],
            'warna_pilihan' => ['nullable', 'string', 'max:80'],
            'manual_discount_type' => ['nullable', 'in:none,percentage,nominal'],
            'manual_discount_value' => ['nullable', 'regex:/^\d{1,13}(?:\.\d{1,2})?$/D'],
            'manual_discount_reason' => ['nullable', 'string', 'max:1000'],
            'expected_base_price' => ['nullable', 'regex:/^\d{1,13}(?:\.\d{1,2})?$/D'],
            'promo_id' => ['prohibited'], 'discount_amount' => ['prohibited'], 'total_tagihan' => ['prohibited'],
            'nomor_pesanan' => ['prohibited'], 'manual_discount_status' => ['prohibited'],
        ]);
        try {
            $order = $service->createForStaff(\App\Models\Pelanggan::findOrFail($data['id_pelanggan']),
                \App\Models\Mobil::findOrFail($data['id_mobil']), $data);
            return redirect()->route('admin.pesanan-mobil.index', ['detail' => $order->id_pesanan])
                ->with('success', (float) $order->manual_discount_value > 0
                    ? 'Draft SPK dicatat. Diskon menunggu persetujuan independen dan kebijakan harga minimum/margin; belum ada invoice atau pembayaran dibuat.'
                    : 'Pesanan dicatat dan menunggu verifikasi. Belum ada pembayaran atau penjualan dibuat.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['order' => $e->getMessage()], 'createOrder');
        }
    }
    public function quote(Request $request, \App\Services\SmartSpkOrderService $spk)
    {
        $input = $request->validate(['id_mobil'=>['required','integer','exists:mobil,id_mobil'],
            'warna_pilihan'=>['required','string','max:80'],'manual_discount_type'=>['required','in:none,percentage,nominal'],
            'manual_discount_value'=>['required','regex:/^\d{1,13}(?:\.\d{1,2})?$/D']]);
        try { return response()->json($spk->quote(\App\Models\Mobil::findOrFail($input['id_mobil']), $input))->header('Cache-Control','private, no-store'); }
        catch (\RuntimeException $e) { return response()->json(['message'=>$e->getMessage()],422); }
    }

    public function discount(Request $request, PesananMobilMember $pesanan_mobil, \App\Services\SmartSpkOrderService $spk)
    {
        $data = $request->validate(['decision'=>['required','in:approve,reject,revise'],
            'manual_discount_reason'=>['required','string','max:1000'], 'manual_discount_type'=>['nullable','in:none,percentage,nominal'],
            'manual_discount_value'=>['nullable','regex:/^\d{1,13}(?:\.\d{1,2})?$/D']]);
        try {
            if ($data['decision'] === 'revise') {
                \Illuminate\Support\Facades\DB::transaction(function () use ($pesanan_mobil,$request,$spk,$data) {
                    $order = PesananMobilMember::lockForUpdate()->findOrFail($pesanan_mobil->id_pesanan);
                    abort_unless((int) $order->manual_discount_requested_by === (int) $request->user()->id_user, 403);
                    if ($order->status !== 'menunggu_verifikasi' || $order->id_transaksi) throw new \RuntimeException('Pesanan ini tidak dapat diubah.');
                    $quote = $spk->calculate((string) $order->snapshot_harga, $data['manual_discount_type'] ?? 'none', (string) ($data['manual_discount_value'] ?? '0'));
                    $spk->recordProposal($order,$data,$quote,$request->user(),'revised');
                });
            } else $spk->review($pesanan_mobil,$request->user(),$data['decision'],$data['manual_discount_reason']);
            return back()->with('success','Pengajuan diskon SPK diperbarui; invoice dan harga mengikat tidak berubah.');
        } catch (\RuntimeException $e) { return back()->withErrors($e->getMessage()); }
    }
    public function show(PesananMobilMember $pesanan_mobil) {
        return redirect()->route('admin.pesanan-mobil.index', ['detail' => $pesanan_mobil->id_pesanan]);
    }
    private function validateDecision(Request $request, PesananMobilMember $order, string $decision): void
    {
        $request->validate([
            'confirmation_reviewed' => ['required', 'accepted'],
            'confirmation_order' => ['required', 'string', \Illuminate\Validation\Rule::in([$order->nomor_pesanan])],
            'confirmation_decision' => ['required', \Illuminate\Validation\Rule::in([$decision])],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ], [
            'confirmation_reviewed.*' => 'Periksa ringkasan pesanan terlebih dahulu.',
            'confirmation_order.*' => 'Ketik nomor pesanan yang benar untuk mengonfirmasi keputusan.',
            'confirmation_decision.*' => 'Konfirmasi keputusan tidak sesuai. Silakan ulangi.',
        ]);
    }

    public function approve(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory)
    {
        $this->validateDecision($request, $pesanan_mobil, 'approve');
        try { $service->approve($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->input('catatan_admin')); return back()->with('success', 'Pesanan disetujui dan transaksi pembayaran dibuat.'); } catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function reject(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory)
    {
        $this->validateDecision($request, $pesanan_mobil, 'reject');
        try { $service->reject($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), (string) $request->input('catatan_admin')); return back()->with('success', 'Pesanan ditolak dan unit dilepas.'); } catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }
    public function schedule(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service) { $data = $request->validate(['jadwal_pengambilan' => ['required', 'date'], 'lokasi_pengambilan' => ['required', 'string', 'max:180'], 'catatan_pengambilan' => ['nullable', 'string', 'max:1000']]); try { $service->schedule($pesanan_mobil, $data['jadwal_pengambilan'], $data['lokasi_pengambilan'], $data['catatan_pengambilan'] ?? null); return back()->with('success', 'Jadwal pengambilan disimpan.'); } catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function startPdi(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { $service->startPdi($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user)); return back()->with('success', 'PDI dimulai.'); } catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function pdi(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { if ($request->has('checklist')) $service->completePdi($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->all()); else $service->savePdi($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->all()); return back()->with('success', 'Hasil PDI disimpan.'); } catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function handover(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { $request->validate(['nama_penerima' => ['required', 'string', 'max:120'], 'nomor_identitas' => ['nullable', 'string', 'max:80']]); try { $service->handover($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->all()); return back()->with('success', 'Serah-terima selesai; kendaraan masuk My Garage pelanggan.'); } catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
}
