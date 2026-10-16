# YZA Admin Service Work Order Wizard

Tanggal: 12 Oktober 2026

## Routes

The existing Work Order route remains the entry point:

- `/admin/servis/{servis}` → smart redirect to the authoritative current stage.
- `/admin/servis/{servis}/pemeriksaan`
- `/admin/servis/{servis}/persetujuan`
- `/admin/servis/{servis}/pengerjaan`
- `/admin/servis/{servis}/quality-control`
- `/admin/servis/{servis}/finalisasi`

The same wizard routes are available under `/petugas/servis/{servis}/...` so existing Petugas access is preserved.

## Stage gate rules

`App\Services\WorkOrderStageService` is the single source of truth for current stage and access checks.

- `pemeriksaan`: Work Order status is `pemeriksaan`.
- `persetujuan`: inspection is complete and the approval review is available.
- `pengerjaan`: no pending customer approval remains.
- `quality-control`: service is active, approvals are resolved, and QC is allowed by the existing Work Order flow.
- `finalisasi`: no pending approval and QC is passed when a QC record exists; legacy services without QC remain compatible with existing finalization rules.

The root route redirects to `currentStage()`. A manually entered future-stage URL is redirected back to the valid current stage with a clear flash message.

## Review and read-only behavior

Completed stages remain reviewable through the shared stepper. Existing backend rules remain authoritative: finalized Work Orders cannot be edited through the existing services, approved snapshots remain immutable, and QC updates are rejected after QC passes.

Each page renders only its active stage content. The shared header contains vehicle, Work Order, customer, kilometer, status, and a five-step navigation. Locked steps are not links and include an explanatory tooltip.

## Legacy compatibility

No new database state or status enum was introduced. Existing services without Phase 4 approval/QC records remain readable and retain their existing finalization behavior. Member views and member APIs were not changed.

## Files

- `app/Services/WorkOrderStageService.php`
- `app/Http/Controllers/OperationsServisController.php`
- `routes/web.php`
- `resources/views/operations/servis/wizard.blade.php`
- `tests/Feature/WorkOrderWizardPhaseTest.php`

## Validation

- No migration executed.
- No database/schema change.
- `php artisan view:cache` passed.
- PHP lint passed for the new service and controller.
- Focused wizard and service regression tests are required before UAT.
