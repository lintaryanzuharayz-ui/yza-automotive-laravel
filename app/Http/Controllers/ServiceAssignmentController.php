<?php

namespace App\Http\Controllers;

use App\Models\Servis;
use App\Services\ServiceAssignmentService;
use Illuminate\Http\Request;
use Throwable;

class ServiceAssignmentController extends Controller
{
    public function update(Request $request, Servis $servis, ServiceAssignmentService $assignments)
    {
        abort_unless(in_array($request->user()->roleKey(), ['super_admin','service_advisor','owner'], true), 403);
        try {
            $data = $request->validate(['id_petugas'=>'required|integer|exists:petugas,id_petugas','assistant_ids'=>'array','assistant_ids.*'=>'nullable|integer|exists:petugas,id_petugas','reason'=>'required|string|max:500']);
            $actor = $request->user()->petugas?->id_petugas;
            $assignments->assignPrimary($servis, (int) $data['id_petugas'], $actor, $data['reason']);
            $assignments->setAssistants($servis, $data['assistant_ids'] ?? [], $actor, $data['reason']);
            return back()->with('success', 'Penugasan Work Order diperbarui dan riwayat perubahan tersimpan.');
        } catch (Throwable $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }
}
