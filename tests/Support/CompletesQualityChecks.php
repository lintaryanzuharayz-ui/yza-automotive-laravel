<?php

namespace Tests\Support;

use App\Models\{Servis, User};
use App\Services\{MemberVehiclePurchaseService, ServiceExecutionService};
use Illuminate\Support\Facades\Auth;

trait CompletesQualityChecks
{
    private function passQc(Servis $service): void
    {
        $prior = Auth::user();
        $inspector = User::whereHas('role', fn ($q) => $q->where('nama_role', 'petugas'))->firstOrFail();
        Auth::setUser($inspector);
        try {
            $execution = app(ServiceExecutionService::class);
            $qc = $execution->startQualityControl($service->id_servis, $inspector->petugas->id_petugas);
            $items = $qc->items->mapWithKeys(fn ($item) => [$item->getKey() => ['result' => 'PASS']])->all();
            $execution->updateQualityControl($service->id_servis, $items, 'QA inspection', $inspector->petugas->id_petugas);
        } finally {
            if ($prior) Auth::setUser($prior); else Auth::forgetUser();
        }
    }

    private function pdiInput(string $result = 'pass'): array
    {
        $checklist = [];
        foreach (app(MemberVehiclePurchaseService::class)->checklistDefinition() as $section => $keys) {
            foreach ($keys as $key) $checklist[$section][$key] = ['result' => $result, 'notes' => $result === 'issue' ? 'Repair required' : ''];
        }
        return ['checklist' => $checklist];
    }
}
