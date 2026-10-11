<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberVehiclePurchaseRequest;
use App\Models\{Mobil, PesananMobilMember};
use App\Services\MemberVehiclePurchaseService;
use App\Services\{VehicleAvailabilityService, VehicleGalleryService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;
use App\Services\PaymentService;

class MemberVehiclePurchaseController extends Controller
{
    public function index(Request $request, MemberVehiclePurchaseService $service, VehicleGalleryService $gallery): View
    {
        $customer = $service->customerFor($request->user());
        $supportedStatuses = ['menunggu_verifikasi', 'menunggu_pembayaran', 'lunas', 'pdi_lulus', 'pdi_gagal', 'siap_diambil', 'diserahkan', 'ditolak', 'dibatalkan'];
        $base = PesananMobilMember::query()->where('id_pelanggan', $customer->id_pelanggan);
        $query = (clone $base)->with(['mobil.warnaPilihan.galeri', 'pdi', 'transaksi.pembayarans', 'serahTerima']);
        $keyword = trim((string) $request->query('q', ''));
        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('nomor_pesanan', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_merk', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_model', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_tipe', 'like', "%{$keyword}%");
            });
        }
        $status = (string) $request->query('status', '');
        if (in_array($status, $supportedStatuses, true)) $query->where('status', $status);
        $sort = (string) $request->query('sort', 'newest');
        match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'action' => $query->orderByRaw("CASE WHEN status IN ('menunggu_pembayaran','siap_diambil') THEN 0 WHEN status = 'menunggu_verifikasi' THEN 1 WHEN status IN ('ditolak','dibatalkan') THEN 3 ELSE 2 END")->orderByDesc('created_at'),
            'completed' => $query->orderByRaw("CASE WHEN status = 'diserahkan' THEN 0 ELSE 1 END")->orderByDesc('created_at'),
            default => $query->latest('created_at'),
        };
        $orders = $query->paginate(5)->withQueryString();
        $orders->getCollection()->each(fn (PesananMobilMember $order) => $this->decorateOrder($order, $gallery));
        $total = (clone $base)->count();
        $action = (clone $base)->whereIn('status', ['menunggu_pembayaran', 'siap_diambil'])->count();
        $completed = (clone $base)->where('status', 'diserahkan')->count();
        return view('pelanggan.pesanan-mobil.index', compact('orders', 'total', 'action', 'completed', 'keyword', 'status', 'sort'));
    }

    private function decorateOrder(PesananMobilMember $order, VehicleGalleryService $gallery): void
    {
        $galleryData = $order->mobil ? $gallery->for($order->mobil) : [];
        $selected = collect($galleryData)->first(fn (array $item) => strcasecmp((string) ($item['label'] ?? ''), (string) $order->snapshot_warna) === 0) ?: collect($galleryData)->first();
        $images = $selected['images'] ?? [];
        $payment = $order->transaksi?->pembayarans?->sortByDesc(fn ($item) => $item->verified_at ?: $item->tanggal)->first();
        $stage = match ($order->status) {
            'menunggu_verifikasi' => 2,
            'menunggu_pembayaran' => 3,
            'lunas' => 4,
            'pdi_lulus' => 5,
            'siap_diambil' => 6,
            'diserahkan' => 7,
            default => 2,
        };
        $order->setAttribute('presentation_gallery', $images);
        $order->setAttribute('presentation_image', $images[0]['src'] ?? asset('assets/images/hero/yza-hero-reference-v3.png'));
        $order->setAttribute('presentation_photo_count', count($images));
        $order->setAttribute('presentation_color', $order->snapshot_warna ?: $order->mobil?->warna ?: 'Warna pilihan');
        $order->setAttribute('presentation_status', $this->statusMeta($order));
        $order->setAttribute('presentation_timeline', $this->timeline($order, $stage, $payment));
    }

    private function statusMeta(PesananMobilMember $order): array
    {
        return match ($order->status) {
            'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi Admin', 'tone' => 'warning', 'description' => 'Pesanan Anda sedang diperiksa oleh tim YZA Automotive.'],
            'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'tone' => 'warning', 'description' => 'Pesanan telah disetujui. Silakan lanjutkan pembayaran melalui detail pesanan.'],
            'lunas' => ['label' => 'Pembayaran Terverifikasi', 'tone' => 'success', 'description' => 'Pembayaran Anda telah diterima dan kendaraan masuk ke tahap persiapan.'],
            'pdi_lulus' => ['label' => 'PDI Selesai', 'tone' => 'success', 'description' => 'Pemeriksaan kendaraan selesai. Kami menyiapkan jadwal serah terima Anda.'],
            'siap_diambil' => ['label' => 'Menunggu Serah Terima', 'tone' => 'success', 'description' => 'Kendaraan siap memasuki proses serah terima sesuai jadwal.'],
            'diserahkan' => ['label' => 'Pesanan Selesai', 'tone' => 'success', 'description' => 'Kendaraan telah diserahkan dan tercatat di koleksi kendaraan Anda.'],
            'pdi_gagal' => ['label' => 'PDI Memerlukan Tindak Lanjut', 'tone' => 'danger', 'description' => 'Tim kami sedang menindaklanjuti hasil pemeriksaan kendaraan.'],
            'ditolak' => ['label' => 'Pesanan Ditolak', 'tone' => 'danger', 'description' => 'Pesanan ini tidak dapat dilanjutkan. Silakan hubungi admin bila memerlukan bantuan.'],
            'dibatalkan' => ['label' => 'Pesanan Dibatalkan', 'tone' => 'danger', 'description' => 'Pesanan ini sudah dibatalkan dan tidak lagi diproses.'],
            default => ['label' => ucwords(str_replace('_', ' ', $order->status)), 'tone' => 'neutral', 'description' => 'Status pesanan sedang diperbarui.'],
        };
    }

    private function timeline(PesananMobilMember $order, int $stage, $payment): array
    {
        $time = fn ($value) => $value?->format('d M Y H:i');
        $paymentTime = $payment?->status === 'terverifikasi' ? ($payment->verified_at ?: $payment->tanggal) : null;
        $steps = [
            ['label' => 'Checkout', 'date' => $time($order->created_at)],
            ['label' => 'Verifikasi Admin', 'date' => $time($order->diputuskan_at)],
            ['label' => 'Pembayaran', 'date' => $time($paymentTime)],
            ['label' => 'PDI', 'date' => $time($order->pdi?->diperiksa_at)],
            ['label' => 'Serah Terima', 'date' => $time($order->serahTerima?->diserahkan_at ?: $order->jadwal_pengambilan)],
            ['label' => 'Selesai', 'date' => $time($order->diserahkan_at)],
        ];
        return collect($steps)->map(function (array $item, int $index) use ($stage, $order): array {
            $number = $index + 1;
            $state = $order->status === 'ditolak' || $order->status === 'dibatalkan' ? ($index === 0 ? 'done' : 'cancelled') : ($number < $stage ? 'done' : ($number === $stage ? 'current' : 'upcoming'));
            return $item + ['state' => $state, 'icon' => $state === 'done' ? '✓' : ($state === 'current' ? '●' : '○')];
        })->all();
    }
    public function create(Request $request, Mobil $mobil, MemberVehiclePurchaseService $service, VehicleAvailabilityService $availability, VehicleGalleryService $gallery): View
    {
        $stock = $availability->for($mobil);
        abort_unless($stock['can_purchase'], 404);
        $customer = $service->customerFor(request()->user());
        $galleryData = $gallery->for($mobil);
        $defaultColorKey = $gallery->defaultColor($mobil);
        $requestedColor = strtolower(trim((string) $request->query('warna', '')));
        $selectedColorKey = array_key_exists($requestedColor, $galleryData) ? $requestedColor : $defaultColorKey;
        $defaultGallery = $galleryData[$selectedColorKey] ?? reset($galleryData);
        $primaryImage = $defaultGallery['images'][0]['src'] ?? asset('assets/images/hero/yza-hero-reference-v3.png');

        return view('pelanggan.pesanan-mobil.create', compact(
            'mobil', 'customer', 'stock', 'galleryData', 'defaultColorKey', 'selectedColorKey', 'defaultGallery', 'primaryImage'
        ));
    }
    public function store(StoreMemberVehiclePurchaseRequest $request, Mobil $mobil, MemberVehiclePurchaseService $service): RedirectResponse { try { $order = $service->create($service->customerFor($request->user()), $mobil, $request->validated()); return redirect()->route('pelanggan.pesanan-mobil.show', $order)->with('success', 'Pesanan diterima dan menunggu verifikasi admin.'); } catch (Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); } }
    public function show(PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, VehicleGalleryService $gallery): View { $customer = $service->customerFor(request()->user()); abort_unless((int) $pesanan_mobil->id_pelanggan === (int) $customer->id_pelanggan, 404); $pesanan_mobil->load(['mobil', 'transaksi.pembayarans', 'penjualan', 'pdi', 'serahTerima']); $galleryData = $pesanan_mobil->mobil ? $gallery->for($pesanan_mobil->mobil) : []; $selectedGallery = collect($galleryData)->first(fn (array $item) => strcasecmp((string) ($item['label'] ?? ''), (string) $pesanan_mobil->snapshot_warna) === 0) ?: (reset($galleryData) ?: []); $vehicleImage = $selectedGallery['images'][0]['src'] ?? asset('assets/images/hero/yza-hero-reference-v3.png'); return view('pelanggan.pesanan-mobil.show', compact('pesanan_mobil', 'galleryData', 'selectedGallery', 'vehicleImage'))->with('order', $pesanan_mobil); }
    public function offlinePayment(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, PaymentService $payments): RedirectResponse
    {
        try {
            $customer = $service->customerFor($request->user());
            abort_unless((int) $pesanan_mobil->id_pelanggan === (int) $customer->id_pelanggan, 404);
            $data = $request->validate(['metode' => ['required', 'in:tunai,transfer'], 'bukti_transfer' => ['required_if:metode,transfer', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'], 'catatan' => ['nullable', 'string', 'max:1000']]);
            abort_unless($pesanan_mobil->status === 'menunggu_pembayaran' && $pesanan_mobil->transaksi, 422, 'Pembayaran belum dibuka oleh admin.');
            $proof = $request->file('bukti_transfer')?->store('pembayaran', 'private');
            $payment = $payments->create($pesanan_mobil->transaksi->id_transaksi, 0, (string) $pesanan_mobil->transaksi->total, 'transfer', $proof, $data['catatan'] ?? null);
            if ($data['metode'] === 'tunai') $payment->forceFill(['metode' => 'tunai'])->save();
            return back()->with('status', 'Pengajuan pembayaran offline tersimpan dan menunggu verifikasi admin.');
        } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }
}
