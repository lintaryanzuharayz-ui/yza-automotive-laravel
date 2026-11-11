<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Mobil, Notifikasi, Pembayaran, PesananMobilMember};
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminWorkspaceController extends Controller
{
    public function activity(Request $request): View
    {
        $q = trim((string) $request->input('q'));
        $type = trim((string) $request->input('type'));
        $status = trim((string) $request->input('status'));
        $from = trim((string) $request->input('from'));
        $to = trim((string) $request->input('to'));

        $canReadOrders = $request->user()->hasPermission('sales.view') || $request->user()->hasPermission('qc.manage');
        $canReadPayments = $request->user()->hasPermission('finance.view');
        $orders = $canReadOrders ? PesananMobilMember::with(['mobil', 'pdi'])
            ->when($from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')->get() : collect();
        $payments = $canReadPayments ? Pembayaran::with('transaksi.pesananMobilMember')
            ->where('arah', 'masuk')->where('status', 'menunggu')
            ->when($from !== '', fn ($query) => $query->whereDate('tanggal', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('tanggal', '<=', $to))
            ->latest('tanggal')->get() : collect();

        $tasks = collect();
        foreach ($orders as $order) {
            $base = ['date' => $order->created_at, 'resource_type' => 'pesanan_mobil_member', 'resource_id' => $order->id_pesanan,
                'assigned_staff_id' => $order->id_sales, 'deadline' => null,
                'search' => strtolower($order->nomor_pesanan.' '.$order->nama_pembeli.' '.$order->snapshot_merk.' '.$order->snapshot_model)];
            if ($order->status === 'menunggu_verifikasi') {
                $tasks->push($base + ['type' => 'Pesanan', 'status' => 'menunggu', 'priority' => 'Tinggi', 'title' => 'Verifikasi pesanan '.$order->nomor_pesanan, 'detail' => $order->nama_pembeli.' · '.$order->snapshot_merk.' '.$order->snapshot_model, 'url' => route('admin.pesanan-mobil.show', $order)]);
            } elseif ($order->status === 'lunas' && (!$order->pdi || $order->pdi->status !== 'lulus')) {
                $inspecting = $order->pdi?->status === 'sedang_diperiksa';
                $tasks->push($base + ['type' => 'PDI', 'status' => $inspecting ? 'diproses' : 'tindakan', 'priority' => 'Tinggi', 'title' => ($inspecting ? 'PDI sedang diperiksa untuk ' : 'PDI perlu diproses untuk ').$order->nomor_pesanan, 'detail' => $order->snapshot_merk.' '.$order->snapshot_model, 'url' => route('admin.pesanan-mobil.show', $order)]);
            } elseif (in_array($order->status, ['pdi_gagal', 'pdi_lulus', 'siap_diambil'], true)) {
                $tasks->push($base + ['type' => $order->status === 'pdi_gagal' ? 'PDI' : 'Pengambilan', 'status' => 'tindakan', 'priority' => 'Sedang',
                    'title' => match ($order->status) { 'pdi_gagal' => 'PDI memerlukan tindak lanjut', 'pdi_lulus' => 'Jadwal pengambilan perlu disiapkan', default => 'Pengambilan perlu dikonfirmasi' },
                    'detail' => $order->nomor_pesanan.' · '.$order->nama_pembeli, 'url' => route('admin.pesanan-mobil.show', $order)]);
            } elseif (in_array($order->status, ['menunggu_pembayaran', 'proses', 'lunas'], true)) {
                $tasks->push($base + ['type' => 'Pesanan', 'status' => 'diproses', 'priority' => 'Sedang', 'title' => 'Pesanan sedang diproses', 'detail' => $order->nomor_pesanan.' · '.$order->nama_pembeli, 'url' => route('admin.pesanan-mobil.show', $order)]);
            } elseif ($order->status === 'diserahkan') {
                $tasks->push($base + ['type' => 'Pesanan', 'status' => 'selesai', 'priority' => 'Normal', 'title' => 'Kendaraan telah diserahkan', 'detail' => $order->nomor_pesanan.' · '.$order->nama_pembeli, 'url' => route('admin.pesanan-mobil.show', $order)]);
            }
        }
        foreach ($payments as $payment) {
            $tasks->push(['date' => $payment->tanggal, 'resource_type' => 'pembayaran', 'resource_id' => $payment->id_pembayaran,
                'assigned_staff_id' => null, 'deadline' => null, 'type' => 'Pembayaran', 'status' => 'menunggu', 'priority' => 'Tinggi',
                'title' => 'Verifikasi pembayaran '.$payment->kode_pembayaran, 'detail' => 'Rp '.number_format((float) $payment->jumlah, 0, ',', '.'),
                'search' => strtolower($payment->kode_pembayaran.' '.$payment->transaksi?->kode_transaksi), 'url' => route('admin.payments.show', $payment)]);
        }

        // One classification per source record. KPIs summarize the same date-scoped pool,
        // before search/type/status filters; notifications are a separate event stream.
        $stats = collect(['menunggu', 'diproses', 'tindakan', 'selesai'])
            ->mapWithKeys(fn ($key) => [$key => $tasks->where('status', $key)->count()])->all();
        $tasks = $tasks->filter(function (array $item) use ($q, $type, $status): bool {
            return ($q === '' || str_contains($item['search'], strtolower($q))) && ($type === '' || $item['type'] === $type) && ($status === '' || $item['status'] === $status);
        })->sortByDesc('date')->values();

        $history = Notifikasi::where('id_user', $request->user()->id_user)
            ->when($from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')->limit(12)->get();

        return view('admin.activity.index', compact('tasks', 'history', 'stats', 'q', 'type', 'status', 'from', 'to'));
    }

    public function catalog(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.mobil.index', array_merge(['tab' => 'katalog'], $request->all()));
    }
}
