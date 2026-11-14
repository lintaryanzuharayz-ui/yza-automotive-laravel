<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePengaturanRequest;
use App\Models\Pengaturan;
use App\Services\{MasterAuditService, PengaturanService};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(PengaturanService $service): View
    {
        $items = Pengaturan::whereIn('kunci_pengaturan', $service::KEYS)->orderBy('id_pengaturan')->get()->keyBy('kunci_pengaturan');
        $value = fn (string $key, ?string $fallback = null) => $items->get($key)?->nilai ?? $fallback;
        $groups = [
            'profile' => ['nama_perusahaan', 'nama_singkat', 'nama_sistem'],
            'contact' => ['alamat_perusahaan', 'no_hp_perusahaan', 'email_perusahaan'],
            'regional' => ['mata_uang', 'stok_notifikasi_default', 'timezone'],
            'business' => ['membership_activation_fee', 'service_capacity_per_slot'],
        ];
        $complete = collect($groups)->mapWithKeys(fn (array $keys, string $group) => [$group => collect($keys)->every(fn (string $key) => filled($value($key)))]);
        $configured = $complete->filter()->count();
        return view('admin.pengaturan.index', compact('items', 'groups', 'complete', 'configured') + ['value' => $value, 'totalSettings' => count($service::KEYS), 'timezoneOptions' => ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'UTC']]);
    }

    public function update(UpdatePengaturanRequest $request, PengaturanService $service, MasterAuditService $audit): RedirectResponse
    {
        $key = $request->settingKey();
        abort_unless(in_array($key, $service::KEYS, true), 404);
        $item = Pengaturan::where('kunci_pengaturan', $key)->firstOrFail();
        $item->update(['nilai' => $request->validated()['nilai'] ?? null]);
        $audit->record('update', 'pengaturan', $item->id_pengaturan, 'Mengubah pengaturan '.$key);
        return back()->with('success', 'Pengaturan diperbarui.');
    }

    public function updateMany(Request $request, PengaturanService $service, MasterAuditService $audit): RedirectResponse
    {
        $data = $request->validate(['settings' => ['required', 'array'], 'settings.*' => ['nullable', 'string', 'max:10000']]);
        $changed = 0;
        DB::transaction(function () use ($data, $service, $audit, &$changed): void {
            foreach ($data['settings'] as $key => $rawValue) {
                if (!in_array($key, $service::KEYS, true) || !$service->validateValue($key, $rawValue)) continue;
                $item = Pengaturan::where('kunci_pengaturan', $key)->first();
                if (!$item || $item->nilai === $rawValue) continue;
                $item->update(['nilai' => $rawValue]);
                $audit->record('update', 'pengaturan', $item->id_pengaturan, 'Mengubah pengaturan '.$key);
                $changed++;
            }
        });
        return back()->with('success', $changed ? $changed.' pengaturan berhasil disimpan.' : 'Tidak ada perubahan pengaturan.');
    }
}
