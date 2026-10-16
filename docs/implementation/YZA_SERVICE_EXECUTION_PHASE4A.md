# YZA Service Execution Phase 4A

## Scope

Phase 4A extends the existing Work Order flow with additional findings, customer authorization, execution timeline evidence, and pre-finalization Quality Control. It does not replace the existing Work Order, transaction, payment, invoice, inventory, or service-history architecture.

## Implemented data

- `service_findings` stores operational findings.
- `service_approval_requests` stores the customer authorization lifecycle.
- `service_approval_request_items` stores server-generated labor/spare-part descriptions and immutable price snapshots, plus apply-once references.
- `service_execution_events` stores timeline/audit evidence without becoming a second status source.
- `service_quality_controls` and `service_quality_control_items` store QC status, checklist snapshots, notes, and staff/timestamp evidence.

## Business rules

Additional labor and spare parts are not billable merely because they were discovered. Admin/Petugas creates a pending approval request; the member approves or rejects. Approved items are applied exactly once to existing `detail_servis`/`servis_sparepart`. Rejected items never enter billing and never reduce stock. Stock is still consumed only by the existing finalization transaction.

Finalization now rejects unresolved pending approvals and rejects a Phase 4 QC record unless it is `passed`. Legacy services with no QC record remain compatible with the existing finalization path. Payment continues to use the existing `transaksi` and `pembayaran` flow. A service is operationally ready for pickup only when service status is `selesai` and payment is `lunas`.

## Security

Member endpoints verify the Work Order's customer owner. Member input is limited to approve/reject and an optional note. Operational writes use the existing Admin/Petugas route separation. No member view or member-specific JavaScript/CSS was changed in Phase 4A.

## Migration and verification

Migration `2026_10_12_030000_add_yza_service_execution_phase4a.php` is additive and was applied normally to `yza_automotive_laravel` and `yza_automotive_laravel_test`. A development backup was created before migration at `storage/app/backups/yza_automotive_laravel-before-execution-20261012.sql`.

Focused Phase 4A tests cover approval snapshot/apply-once behavior, rejected work, pending finalization gate, QC failure/pass gate, tracking, member ownership, and privacy. Existing scheduling, personnel, booking, Work Order, inventory, finalization, payment, invoice, and history regression suites remain in scope.
