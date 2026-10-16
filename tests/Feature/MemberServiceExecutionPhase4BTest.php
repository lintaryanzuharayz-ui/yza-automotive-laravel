<?php

namespace Tests\Feature;

use App\Models\Servis;
use App\Models\User;
use Tests\TestCase;

class MemberServiceExecutionPhase4BTest extends TestCase
{
    public function test_owned_service_detail_contains_frozen_execution_integration(): void
    {
        $service = Servis::with('pelanggan')->firstOrFail();
        $member = User::where('id_user', $service->pelanggan?->id_user)->firstOrFail();

        $this->actingAs($member)->get(route('pelanggan.servis.show', $service))
            ->assertOk()
            ->assertSee('/tracking', false)
            ->assertSee('/approvals', false)
            ->assertSee('PERSETUJUAN PEKERJAAN TAMBAHAN', false)
            ->assertSee('decision', false)
            ->assertSee('Refresh Status', false);
    }

    public function test_other_member_cannot_view_service_execution_detail(): void
    {
        $service = Servis::firstOrFail();
        $other = User::whereHas('pelanggan', fn ($query) => $query->where('id_pelanggan', '!=', $service->id_pelanggan))->firstOrFail();

        $this->actingAs($other)->get(route('pelanggan.servis.show', $service))->assertForbidden();
    }
}
