# YZA Service Scheduling Phase 2A

## Scope

Phase 2A adds workshop-level scheduling only. Technician preference, technician calendars, service advisor assignment, and individual service bays are intentionally not implemented.

## Schema

- `service_operating_hours`: one row per ISO weekday, with open/close, break, and slot interval.
- `service_blackout_dates`: explicit closed dates and a future-safe shape for custom hours.
- `jenis_servis.duration_minutes`: non-null conservative service duration.
- `pengaturan.service_capacity_per_slot`: configurable workshop concurrency, default 3.

The migration is additive and reversible. Existing booking, work order, payment, and history rows are not changed or deleted. Existing service duration defaults are: Servis Berkala 120, Ganti Oli 60, Tune Up 120, Servis Rem 120, Servis AC 120, Kelistrikan 120 minutes. These are conservative operational defaults and are editable through the service master/settings workflow.

## Availability and overlap

`App\Services\ServiceSchedulingService` is the single source of truth for member availability, booking validation, and the admin timeline. It loads weekly hours and blackout data, hides same-day past slots, rejects break/out-of-window starts, and counts only `menunggu`, `diterima`, and `diproses` bookings. `ditolak`, `dibatalkan`, and completed bookings do not consume future capacity.

Each booking is compared as an interval `[start, start + duration)`, so a 120-minute booking at 08:00 consumes both the 08:00 and 09:00 capacity windows. Booking creation calls the engine again inside a database transaction and locks the weekday configuration row plus relevant booking rows before inserting. This serializes competing capacity checks and prevents the final capacity from being exceeded under normal MySQL transaction behavior.

## Admin

`/admin/servis/jadwal` provides a month calendar, service-specific daily timeline, operating-hours editor, capacity setting through the existing admin settings page, and blackout management. The calendar and timeline call the same engine; no slot counts are hardcoded in Blade.

## Compatibility and limitations

Legacy bookings remain readable even if they fall outside the new policy; they are not automatically rejected or deleted. A future admin warning can flag them. The current phase does not assign technicians or advisors and does not expose member booking details through the availability endpoint.

## Phase 2A.1 validation

The existing test database `yza_automotive_laravel_test` was verified and synchronized with the pending additive migration; no database was created or reset. Duration boundaries are interval-aware: 11:00 is valid for 60 minutes but invalid for 120 minutes, 10:00 is valid for 120 minutes, 16:00 is valid for 60 minutes but invalid for 120 minutes. The month contract is served by `ServiceSchedulingService::monthAvailability`, returning only aggregate day status and available slot count. The admin calendar continues to call the same service.

Booking storage revalidates availability inside its transaction, so a modified request or a slot that becomes full after the calendar was loaded is rejected. A true parallel-process concurrency test is environment-dependent; service-level locking is verified by the transaction path, while focused tests document the final-capacity behavior.
