<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Pembayaran, PembayaranGateway};
use App\Services\{InventoryService, MidtransPaymentService, PaymentService};
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaymentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'offline');
        $status = trim((string) $request->input('status'));
        $q = trim((string) $request->input('q'));
        $from = $request->date('from');
        $to = $request->date('to');

        $online = PembayaranGateway::with(['transaksi.pelanggan', 'transaksi.pesananMobilMember', 'transaksi.penjualanMobil.mobil'])
            ->whereHas('transaksi', fn ($query) => $query->where('jenis_transaksi', 'penjualan_mobil'))
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('order_id', 'like', "%{$q}%")
                    ->orWhereHas('transaksi.pelanggan', fn ($customer) => $customer->where('nama_lengkap', 'like', "%{$q}%"));
            }))
            ->when($status !== '' && $tab === 'online', fn ($query) => $query->where('status_provider', $status))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')->paginate(12, ['*'], 'online_page')->withQueryString();

        $offline = Pembayaran::with(['transaksi.pelanggan', 'transaksi.pesananMobilMember', 'transaksi.penjualanMobil.mobil'])
            ->where('arah', 'masuk')->whereIn('metode', ['tunai', 'transfer'])
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('kode_pembayaran', 'like', "%{$q}%")
                    ->orWhereHas('transaksi.pelanggan', fn ($customer) => $customer->where('nama_lengkap', 'like', "%{$q}%"));
            }))
            ->when($status !== '' && $tab === 'offline', fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('tanggal', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('tanggal', '<=', $to))
            ->latest('tanggal')->paginate(12, ['*'], 'offline_page')->withQueryString();

        $stats = [
            'online_verified' => PembayaranGateway::whereIn('status_provider', ['settlement', 'capture'])->sum('gross_amount'),
            'offline_verified' => Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi')->sum('jumlah'),
            'waiting_payment' => \App\Models\Transaksi::where('jenis_transaksi', 'penjualan_mobil')->where('status', 'belum_bayar')->count(),
            'waiting_confirmation' => Pembayaran::where('arah', 'masuk')->where('status', 'menunggu')->count(),
            'paid_transactions' => \App\Models\Transaksi::where('status', 'lunas')->count(),
        ];

        return view('admin.pembayaran.index', compact('tab', 'status', 'q', 'online', 'offline', 'stats'));
    }

    public function show(Pembayaran $pembayaran): View
    {
        abort_unless($pembayaran->arah === 'masuk', 404);
        $pembayaran->load(['transaksi.pelanggan', 'transaksi.pesananMobilMember', 'transaksi.penjualanMobil.mobil', 'transaksi.pembayarans']);
        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    public function verify(Request $request, Pembayaran $pembayaran, PaymentService $service, InventoryService $inventory)
    {
        try {
            $request->validate(['receipt_reference' => ['nullable', 'string', 'max:100']]);
            $payment = $service->verify($pembayaran->id_pembayaran, (int) $inventory->actorPetugasId($request->user()->id_user));
            if ($request->filled('receipt_reference')) $payment->forceFill(['receipt_reference' => $request->string('receipt_reference')->toString()])->save();
            return back()->with('status', 'Dana diterima dan pembayaran berhasil diverifikasi.');
        } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function reject(Request $request, Pembayaran $pembayaran, PaymentService $service, InventoryService $inventory)
    {
        try {
            $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
            $service->reject($pembayaran->id_pembayaran, (int) $inventory->actorPetugasId($request->user()->id_user));
            $pembayaran->forceFill(['rejection_reason' => $data['rejection_reason'], 'verified_by' => (int) $inventory->actorPetugasId($request->user()->id_user), 'verified_at' => now()])->save();
            return back()->with('status', 'Pembayaran ditolak dan riwayatnya tetap tersimpan.');
        } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function sync(Request $request, PembayaranGateway $gateway, MidtransPaymentService $service)
    {
        try { $service->syncForAdmin($gateway); return back()->with('status', 'Status resmi Midtrans diperbarui.'); }
        catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }
}
