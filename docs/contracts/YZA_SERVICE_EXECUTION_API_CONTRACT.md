# YZA Service Execution API Contract

Phase 4A keeps the existing Work Order and billing contract. `detail_servis` and `servis_sparepart` remain the final billing sources; approval records authorize additional work and preserve the customer-facing price snapshot.

## Member endpoints

`GET /pelanggan/servis/{servis}/tracking` returns `service_id`, `service_code`, `current_stage`, the stable stage keys, `pending_approval_count`, `qc_status`, `payment_status`, and `ready_for_pickup`.

`GET /pelanggan/servis/{servis}/approvals` returns only approvals belonging to the authenticated member's Work Order. Each item includes its immutable description, quantity, unit price snapshot, and total snapshot.

`POST /pelanggan/servis/{servis}/approvals/{approval}/decision` accepts only `decision=approve|reject` and an optional note. Price, item, quantity, technician, QC, finalization, and payment fields are ignored or rejected. The endpoint is owner-scoped and decisions are idempotent for the same existing status.

## Approval lifecycle

`pending → approved` applies each item once to the existing Work Order draft. Labor becomes one `detail_servis` row; spare parts become one `servis_sparepart` row using the approval snapshot. Approval does not deduct stock. `pending → rejected` keeps the recommendation visible historically and applies nothing. A responded request cannot silently switch decision.

Prices are read from active server-side masters at request creation. Member payloads cannot change item, quantity, description, or price.

## Tracking and QC

Stable stage keys are: `booking_confirmed`, `check_in`, `inspection`, `customer_approval`, `in_progress`, `quality_control`, `payment`, `ready_for_pickup`, and `completed`.

Execution events are audit evidence only; existing Work Order status remains authoritative. QC is an additive record with structured checklist results `PASS`, `FAIL`, or `N/A`. A mandatory `FAIL` requires a note and prevents QC pass. Finalization requires all mandatory checks to pass when a QC record exists.

`ready_for_pickup` is true when the existing service status is `selesai` and the existing transaction status is `lunas`. Existing payment synchronization remains the source of the completion transition.

## Security and compatibility

Only Admin/Petugas operational routes create findings, approval requests, or QC changes. Members can only read and decide their own service approvals. Existing services without Phase 4 approval/QC records remain readable and preserve their historical finalization behavior.
