<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberVehiclePreorder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberVehiclePreorderController extends Controller
{
    public function detail(Request $request, MemberVehiclePreorder $preorder, \App\Services\PreorderManagementService $service)
    {
        return response()->json($service->detail($preorder, $request->user()))->header('Cache-Control', 'no-store, private');
    }

    public function stock(Request $request, MemberVehiclePreorder $preorder, \App\Services\PreorderManagementService $service)
    {
        $stock = $service->stock($preorder, $request->user());
        if ($request->isMethod('post')) {
            $service->authorize($request->user(), true);
            app(\App\Services\PreorderInventoryNotificationService::class)->activity($preorder, 'stock_checked', [
                'message' => $stock['message'], 'eligible' => $stock['total']], $request->user()->id_user);
            $stock['history'] = $service->detail($preorder, $request->user())['history'];
        }
        return response()->json($stock)->header('Cache-Control', 'no-store, private');
    }

    public function action(Request $request, MemberVehiclePreorder $preorder, \App\Services\PreorderManagementService $service)
    {
        $service->authorize($request->user(), true);
        $data = $request->validate([
            'action' => ['required', \Illuminate\Validation\Rule::in(['notify', 'resend', 'cancel'])],
            'reason' => ['nullable', 'string', 'max:1000'],
            'expected_status' => ['required', \Illuminate\Validation\Rule::in(['menunggu_stok', 'stok_tersedia', 'notifikasi_terkirim', 'dilanjutkan_ke_checkout', 'dibatalkan'])],
            'confirmation' => ['required', \Illuminate\Validation\Rule::in(['PRE-'.str_pad((string) $preorder->getKey(), 5, '0', STR_PAD_LEFT)])],
        ], ['confirmation.*' => 'Ketik kode pre-order yang benar untuk mengonfirmasi.']);
        $updated = $service->act($preorder, $request->user(), $data['action'], $data['expected_status'], $data['reason'] ?? null);
        return response()->json(['message' => $data['action'] === 'cancel' ? 'Pre-order dibatalkan dan member diberi notifikasi.' : 'Member diberi notifikasi stok. Tidak ada unit yang direservasi.',
            'preorder' => $service->detail($updated, $request->user()), 'stats' => $this->stats()])->header('Cache-Control', 'no-store, private');
    }

    public function index(Request $request): View
    {
        $keyword = trim((string) $request->input('q'));
        $status = trim((string) $request->input('status'));
        $from = trim((string) $request->input('from'));
        $to = trim((string) $request->input('to'));
        $validStatuses = ['menunggu_stok', 'stok_tersedia', 'notifikasi_terkirim', 'dilanjutkan_ke_checkout', 'dibatalkan'];

        $query = MemberVehiclePreorder::with(['pelanggan', 'mobil.foto'])->latest('created_at');
        if ($request->integer('review') > 0) $query->whereKey($request->integer('review'));
        $query->when($keyword !== '', function ($builder) use ($keyword): void {
            $builder->where(function ($search) use ($keyword): void {
                $search->where('product_key', 'like', "%{$keyword}%")
                    ->orWhere('showcase_slug', 'like', "%{$keyword}%")
                    ->orWhere('nama_mobil', 'like', "%{$keyword}%")
                    ->orWhere('varian', 'like', "%{$keyword}%")
                    ->orWhere('warna', 'like', "%{$keyword}%")
                    ->orWhereHas('pelanggan', function ($customer) use ($keyword): void {
                        $customer->where('nama_lengkap', 'like', "%{$keyword}%")
                            ->orWhere('kode_pelanggan', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('mobil', fn ($car) => $car->where('kode_mobil', 'like', "%{$keyword}%"));
            });
        });
        $query->when(in_array($status, $validStatuses, true), fn ($builder) => $builder->where('status', $status));
        $query->when($from !== '', fn ($builder) => $builder->whereDate('created_at', '>=', $from));
        $query->when($to !== '', fn ($builder) => $builder->whereDate('created_at', '<=', $to));

        $preorders = $query->paginate(20)->withQueryString();
        $presentation = app(\App\Services\PreorderManagementService::class);
        $preorders->getCollection()->each(fn (MemberVehiclePreorder $preorder) => $preorder->setAttribute('presentation_image', $presentation->photo($preorder)));
        $stats = $this->stats();

        return view('admin.preorder.index', compact('preorders', 'stats', 'keyword', 'status', 'from', 'to'));
    }

    private function stats(): array
    {
        return [
            'total' => MemberVehiclePreorder::count(),
            'active' => MemberVehiclePreorder::active()->count(),
            'waiting' => MemberVehiclePreorder::active()->where('status', 'menunggu_stok')->count(),
            'follow_up' => MemberVehiclePreorder::active()->whereIn('status', ['stok_tersedia', 'notifikasi_terkirim'])->count(),
            'checkout' => MemberVehiclePreorder::where('status', 'dilanjutkan_ke_checkout')->count(),
        ];

    }
}
