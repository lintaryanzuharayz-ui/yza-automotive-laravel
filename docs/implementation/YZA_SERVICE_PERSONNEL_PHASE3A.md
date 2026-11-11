# YZA Service Personnel Phase 3A

## Architecture

Existing `petugas` remains the staff identity source. `service_staff_profiles` adds capability flags, bookable state, and experience without duplicating identity/contact/photo data. `service_specializations` plus its pivot keeps specialization normalized. `technician_working_hours` and `technician_time_off` provide the personnel availability source of truth.

`booking_servis.id_preferred_petugas` is nullable and represents member preference only. Existing `servis.id_petugas` remains the final technician assignment. `servis.id_service_advisor` is a separate nullable final advisor assignment.

## Eligibility

`ServicePersonnelAvailabilityService` checks active staff, capability, bookable flag, complete interval coverage, time off, and one-active-overlapping-Work-Order rule. `ServiceSchedulingService` remains authoritative for workshop hours, blackout, duration, and workshop capacity; personnel availability is an additional assignment layer.

## Admin

`/admin/servis/personel` manages capabilities, normalized specializations, working hours, and time off. `/admin/servis/jadwal` shows personnel status for the selected date. Booking detail distinguishes preference from final assignment, and Work Order detail shows technician and advisor separately.

## Migration and compatibility

Migration `2026_10_12_020000_add_yza_service_personnel_phase3a` is additive and reversible. Existing staff receive neutral profiles and weekday schedule rows; no fictitious capability or preference is backfilled. Existing bookings, Work Orders, finalization, payment, and history remain intact.

## Concurrency and limitations

Final technician assignment revalidates eligibility inside the Work Order transaction. The service-level check and database transaction prevent normal overlapping assignment races; a true parallel-process stress test is not claimed by this phase. Member UI changes, customer additional-work approval, QC, live tracking, notifications, and staff ratings are out of scope.
