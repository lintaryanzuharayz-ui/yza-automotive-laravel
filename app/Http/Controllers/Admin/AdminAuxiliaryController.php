<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{BookingServis, KendaraanPelanggan, Permission, Role, Transaksi, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AdminAuxiliaryController extends Controller
{
    public function transactions(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $type = trim((string) $request->query('type'));
        $status = trim((string) $request->query('status'));
        $from = trim((string) $request->query('from'));
        $to = trim((string) $request->query('to'));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 10;
        $transactions = Transaksi::with(['pelanggan', 'tradeIn', 'pembelianMobilBekas'])
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('kode_transaksi', 'like', "%{$q}%")
                    ->orWhere('jenis_transaksi', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', fn ($customer) => $customer->where('nama_lengkap', 'like', "%{$q}%"));
            }))
            ->when($type !== '', fn ($query) => $query->where('jenis_transaksi', $type))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($from !== '', fn ($query) => $query->whereDate('tanggal', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('tanggal', '<=', $to))
            ->latest('tanggal')->paginate($perPage)->withQueryString();

        $transactionStats = [
            'total' => Transaksi::count(),
            'sales' => Transaksi::where('jenis_transaksi', 'penjualan_mobil')->count(),
            'service' => Transaksi::where('jenis_transaksi', 'servis')->count(),
            'trade_in' => Transaksi::where('jenis_transaksi', 'trade_in')->count(),
            'paid' => Transaksi::where('status', 'lunas')->count(),
            'unpaid' => Transaksi::whereIn('status', ['belum_bayar', 'menunggu_pembayaran'])->count(),
        ];

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'q' => $q,
            'type' => $type,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'perPage' => $perPage,
            'invoiceMode' => $request->boolean('invoice_mode') || (bool) $request->route('invoice_mode'),
            'types' => Transaksi::query()->whereNotNull('jenis_transaksi')->distinct()->orderBy('jenis_transaksi')->pluck('jenis_transaksi'),
            'statuses' => Transaksi::query()->whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
            'transactionStats' => $transactionStats,
        ]);
    }

    public function garage(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $status = trim((string) $request->query('status'));
        $type = trim((string) $request->query('type'));
        $from = trim((string) $request->query('from'));
        $to = trim((string) $request->query('to'));
        $perPage = (int) $request->query('per_page', 5);
        $perPage = in_array($perPage, [5, 10, 20], true) ? $perPage : 5;

        $vehicles = KendaraanPelanggan::with(['pelanggan.membership', 'penjualan.mobil.foto', 'pesanan', 'serahTerima'])
            ->whereNotNull('id_serah_terima')
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('merk', 'like', "%{$q}%")->orWhere('model', 'like', "%{$q}%")
                    ->orWhere('tipe', 'like', "%{$q}%")
                    ->orWhere('nomor_polisi', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', fn ($customer) => $customer->where('nama_lengkap', 'like', "%{$q}%"));
            }))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($type !== '', fn ($query) => $query->where('tipe', $type))
            ->when($from !== '', fn ($query) => $query->whereHas('serahTerima', fn ($handover) => $handover->whereDate('diserahkan_at', '>=', $from)))
            ->when($to !== '', fn ($query) => $query->whereHas('serahTerima', fn ($handover) => $handover->whereDate('diserahkan_at', '<=', $to)))
            ->latest('created_at')->paginate($perPage)->withQueryString();

        $verified = fn () => KendaraanPelanggan::query()->whereNotNull('id_serah_terima');
        $today = now()->toDateString();
        $total = $verified()->count();
        $brands = $verified()->select('merk')->selectRaw('COUNT(*) as total')->groupBy('merk')->orderByDesc('total')->get();
        $brandDistribution = $brands->map(fn ($brand) => [
            'name' => $brand->merk,
            'total' => (int) $brand->total,
            'percentage' => $total > 0 ? round(((int) $brand->total / $total) * 100) : 0,
        ]);
        $metrics = [
            'total' => $total,
            'active' => $verified()->where('status', 'aktif')->count(),
            'readyService' => null,
            'memberOwners' => $verified()->whereHas('pelanggan.membership', fn ($membership) => $membership->where('status', 'active'))->distinct('id_pelanggan')->count('id_pelanggan'),
            'sportUnits' => null,
            'handoverToday' => $verified()->whereHas('serahTerima', fn ($handover) => $handover->whereDate('diserahkan_at', $today))->count(),
            'serviceToday' => BookingServis::whereDate('tanggal', $today)->whereNotIn('status', ['dibatalkan', 'ditolak'])->whereHas('kendaraan', fn ($vehicle) => $vehicle->whereNotNull('id_serah_terima'))->count(),
            'followUpOwners' => null,
            'completeDocuments' => $verified()->whereHas('serahTerima', fn ($handover) => $handover->where('dokumen_lengkap', true))->count(),
        ];

        return view('admin.garage.index', [
            'vehicles' => $vehicles,
            'q' => $q,
            'status' => $status,
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'perPage' => $perPage,
            'vehicleTypes' => $verified()->whereNotNull('tipe')->where('tipe', '<>', '')->distinct()->orderBy('tipe')->pluck('tipe'),
            'metrics' => $metrics,
            'brandDistribution' => $brandDistribution,
        ]);
    }

    public function roles(Request $request): View
    {
        $roleSearch = trim((string) $request->input('role_q', ''));
        $permissionSearch = trim((string) $request->input('permission_q', ''));
        $roleSort = in_array($request->input('role_sort'), ['nama_role', 'users_count'], true) ? $request->input('role_sort') : 'nama_role';
        $roles = Role::with(['permissions'])->withCount('users')
            ->when($roleSearch !== '', fn ($query) => $query->where('nama_role', 'like', "%{$roleSearch}%"))
            ->orderBy($roleSort, $roleSort === 'users_count' ? 'desc' : 'asc')->get();
        $permissions = Permission::withCount('roles')->when($permissionSearch !== '', fn ($query) => $query->where(fn ($search) => $search->where('kode', 'like', "%{$permissionSearch}%")->orWhere('nama', 'like', "%{$permissionSearch}%")))->orderBy('kode')->get();
        $mappedUsers = User::whereNotNull('id_role')->distinct('id_user')->count('id_user');
        $permissionUsed = Permission::whereHas('roles')->count();
        $roleDistribution = $roles->map(fn ($role) => ['name' => ucwords(str_replace('_', ' ', $role->nama_role)), 'total' => (int) $role->users_count]);
        $distributionTotal = max(1, (int) $roleDistribution->sum('total'));

        return view('admin.roles.index', compact('roles', 'permissions', 'roleSearch', 'permissionSearch', 'roleSort', 'mappedUsers', 'permissionUsed', 'roleDistribution', 'distributionTotal'));
    }

    public function auditLog(Request $request): View
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'level' => ['nullable', 'in:Info,Warning,Critical'],
            'module' => ['nullable', 'string', 'max:60'],
            'actor' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'in:5,10,25,50'],
        ]);
        $entries = collect($this->auditEntries())->map(fn (array $entry) => $this->decorateAuditEntry($entry));
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;
        $entries = $entries->filter(function (array $entry) use ($from, $to, $data): bool {
            $date = $entry['timestamp'];
            if ($from && $date->lt(Carbon::parse($from)->startOfDay())) return false;
            if ($to && $date->gt(Carbon::parse($to)->endOfDay())) return false;
            if (!empty($data['level']) && $entry['level'] !== $data['level']) return false;
            if (!empty($data['module']) && $entry['module'] !== $data['module']) return false;
            if (isset($data['actor']) && (int) $entry['actor_id'] !== (int) $data['actor']) return false;
            if (!empty($data['q']) && !str_contains(strtolower($entry['search']), strtolower($data['q']))) return false;
            return true;
        })->sortByDesc(fn (array $entry) => $entry['timestamp']->getTimestamp())->values();
        $allEntries = collect($this->auditEntries())->map(fn (array $entry) => $this->decorateAuditEntry($entry));
        $today = now('Asia/Jakarta')->toDateString();
        $recent = $allEntries->filter(fn (array $entry) => $entry['timestamp']->gte(now('Asia/Jakarta')->subDays(29)->startOfDay()));
        $actorIds = $allEntries->pluck('actor_id')->filter()->unique()->values();
        $actors = User::with(['petugas', 'role'])->whereIn('id_user', $actorIds)->get()->keyBy('id_user');
        $entries = $entries->map(function (array $entry) use ($actors): array {
            $actor = $actors->get($entry['actor_id']);
            $entry['actor_name'] = $actor?->petugas?->nama_lengkap ?: ($actor?->username ?: ($entry['actor_id'] ? 'User #'.$entry['actor_id'] : 'Sistem'));
            $entry['actor_role'] = $actor?->role?->nama_role ? ucwords(str_replace('_', ' ', $actor->role->nama_role)) : ($entry['actor_id'] ? 'User teridentifikasi' : 'Sistem');
            return $entry;
        });
        $perPage = (int) ($data['per_page'] ?? 10);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = new LengthAwarePaginator($entries->forPage($page, $perPage)->values(), $entries->count(), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
        return view('admin.audit-log.index', [
            'entries' => $rows,
            'filters' => $data,
            'levels' => ['Info', 'Warning', 'Critical'],
            'modules' => $allEntries->pluck('module')->filter()->unique()->sort()->values(),
            'actors' => $actors,
            'metrics' => [
                'total' => $allEntries->count(),
                'today' => $allEntries->filter(fn (array $entry) => $entry['timestamp']->toDateString() === $today)->count(),
                'critical' => $allEntries->where('level', 'Critical')->count(),
                'user_actions' => $allEntries->filter(fn (array $entry) => $entry['actor_id'] !== null && $entry['action'] !== '')->count(),
                'modules' => $allEntries->pluck('module')->filter()->unique()->count(),
                'structured' => $allEntries->filter(fn (array $entry) => $entry['action'] !== '' && $entry['module'] !== 'Lainnya')->count(),
                'active_users' => $allEntries->filter(fn (array $entry) => $entry['timestamp']->toDateString() === $today && $entry['actor_id'] !== null)->pluck('actor_id')->unique()->count(),
            ],
            'distribution' => $recent->groupBy('module')->map(fn ($items, $module) => ['module' => $module, 'count' => $items->count()])->sortByDesc('count')->values(),
            'recentActivity' => $allEntries->sortByDesc(fn (array $entry) => $entry['timestamp']->getTimestamp())->take(5)->values(),
            'allEntries' => $allEntries,
        ]);
    }

    public function auditLogExport(Request $request)
    {
        $data = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'level' => ['nullable', 'in:Info,Warning,Critical'], 'module' => ['nullable', 'string', 'max:60'], 'actor' => ['nullable', 'integer'], 'q' => ['nullable', 'string', 'max:120']]);
        $entries = collect($this->auditEntries())->map(fn (array $entry) => $this->decorateAuditEntry($entry))->filter(function (array $entry) use ($data): bool {
            if (!empty($data['from']) && $entry['timestamp']->lt(Carbon::parse($data['from'])->startOfDay())) return false;
            if (!empty($data['to']) && $entry['timestamp']->gt(Carbon::parse($data['to'])->endOfDay())) return false;
            if (!empty($data['level']) && $entry['level'] !== $data['level']) return false;
            if (!empty($data['module']) && $entry['module'] !== $data['module']) return false;
            if (isset($data['actor']) && (int) $entry['actor_id'] !== (int) $data['actor']) return false;
            return empty($data['q']) || str_contains(strtolower($entry['search']), strtolower($data['q']));
        })->sortByDesc(fn (array $entry) => $entry['timestamp']->getTimestamp())->take(1000)->values();
        $actorIds = $entries->pluck('actor_id')->filter()->unique();
        $actors = User::with('petugas')->whereIn('id_user', $actorIds)->get()->keyBy('id_user');
        $entries = $entries->map(function (array $entry) use ($actors): array { $actor = $actors->get($entry['actor_id']); $entry['actor_name'] = $actor?->petugas?->nama_lengkap ?: ($actor?->username ?: ($entry['actor_id'] ? 'User #'.$entry['actor_id'] : 'Sistem')); return $entry; });
        return response()->streamDownload(function () use ($entries): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Waktu', 'Aktor', 'Modul', 'Aksi', 'Level', 'Deskripsi', 'Referensi']);
            foreach ($entries as $entry) fputcsv($out, [$entry['timestamp']->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'), $entry['actor_name'], $entry['module'], $entry['action'], $entry['level'], $entry['description'], $entry['reference']]);
            fclose($out);
        }, 'yza-audit-log.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function auditEntries(): array
    {
        $files = array_values(array_filter(glob(storage_path('logs/laravel*.log')) ?: [], 'is_file'));
        rsort($files);
        $records = [];
        foreach ($files as $path) {
            $file = new \SplFileObject($path, 'r');
            $block = [];
            while (!$file->eof()) {
                $line = (string) $file->fgets();
                if ($line === '') continue;
                if (preg_match('/^\[(?<time>[^\]]+)\]\s+[^.]+\.(?<level>[A-Z]+):\s+(?<channel>[A-Za-z0-9_.-]+)/', trim($line))) {
                    if ($block) $this->appendAuditBlock($records, $block);
                    $block = [trim($line)];
                } elseif ($block) {
                    $block[] = trim($line);
                }
            }
            if ($block) $this->appendAuditBlock($records, $block);
        }
        return array_reverse($records);
    }

    private function appendAuditBlock(array &$records, array $block): void
    {
        $raw = implode(' ', $block);
        if (!preg_match('/^\[(?<time>[^\]]+)\]\s+[^.]+\.(?<level>[A-Z]+):\s+(?<channel>[A-Za-z0-9_.-]+)/', $block[0], $header)) return;
        if (!in_array($header['channel'], ['master.audit', 'mobil.audit', 'member_vehicle_purchase.audit'], true)) return;
        $jsonStart = strpos($raw, '{');
        $context = [];
        if ($jsonStart !== false) {
            $decoded = json_decode(substr($raw, $jsonStart), true);
            if (is_array($decoded)) $context = $decoded;
        }
        try { $timestamp = Carbon::parse($header['time'], 'Asia/Jakarta'); } catch (\Throwable) { return; }
        $records[] = ['timestamp' => $timestamp, 'source_level' => $header['level'], 'channel' => $header['channel'], 'context' => $context];
    }

    private function decorateAuditEntry(array $entry): array
    {
        $context = $entry['context'];
        $table = strtolower((string) ($context['table'] ?? ''));
        $channel = $entry['channel'];
        $module = $channel === 'member_vehicle_purchase.audit' ? 'Membership' : match (true) {
            str_contains($table, 'sparepart') => 'Sparepart', str_contains($table, 'mobil') || str_contains($table, 'kendaraan') => 'Inventory',
            str_contains($table, 'servis') || str_contains($table, 'booking_servis') => 'Servis', str_contains($table, 'paket_cuci') => 'Paket Cuci',
            str_contains($table, 'role') || str_contains($table, 'permission') || str_contains($table, 'petugas') => 'Role & Permission',
            str_contains($table, 'pengaturan') => 'Website', str_contains($table, 'pelanggan') || str_contains($table, 'membership') => 'Membership',
            str_contains($table, 'transaksi') || str_contains($table, 'penjualan') => 'Penjualan', default => 'Lainnya',
        };
        $level = match (strtoupper((string) $entry['source_level'])) { 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'Critical', 'WARNING', 'WARN' => 'Warning', default => 'Info' };
        $action = trim((string) ($context['action'] ?? 'event'));
        $description = trim((string) ($context['description'] ?? str_replace('_', ' ', $action)));
        $actorId = isset($context['actor_id']) && is_numeric($context['actor_id']) ? (int) $context['actor_id'] : null;
        $reference = $context['order_number'] ?? ($context['reference_id'] ?? ($context['order_id'] ?? '—'));
        return $entry + ['module' => $module, 'level' => $level, 'action' => $action, 'description' => $description, 'actor_id' => $actorId, 'reference' => (string) $reference, 'actor_name' => $actorId ? 'User #'.$actorId : 'Sistem', 'actor_role' => $actorId ? 'User teridentifikasi' : 'Sistem', 'search' => implode(' ', [$module, $action, $description, $reference, $entry['channel']])];
    }

    public function ordersWorkflow(Request $request, string $workflow): View
    {
        $status = match ($workflow) {
            'pdi' => 'proses',
            'pengambilan' => 'proses',
            'serah-terima' => 'siap_diambil',
            default => 'proses',
        };
        $request->merge(['status' => $status]);
        return app(MemberVehiclePurchaseController::class)->index($request);
    }
}
