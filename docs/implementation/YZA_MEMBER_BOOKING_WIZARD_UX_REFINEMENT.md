# YZA Member Booking Wizard UX Refinement

## Scope

Refinement applies only to the member route `/pelanggan/booking-servis/create`. Existing ownership, service scheduling, technician preference, authentication, payment, and booking persistence flows are preserved.

## Layout

- The page uses `min(96vw, 1700px)` so the hero, stepper, main form, and summary align to one wide grid.
- Desktop uses a wide main column and a sticky summary column.
- Calendar and time slots share one row on wide screens and stack on smaller screens.
- Mobile uses one column with no horizontal overflow.

## Wizard behavior

1. Step 01 is current on first load.
2. The Step 01 button remains disabled until vehicle, service, valid date, and valid slot are selected.
3. Clicking `Lanjut ke Detail Servis` activates Step 02 and collapses the schedule picker into a compact summary.
4. Step 02 keeps complaint, notes, mileage display, and technician preference in a dedicated panel.
5. Clicking `Lanjut ke Konfirmasi` activates Step 03 and collapses the detail panel.
6. Step 03 is the only step with the final submit CTA.
7. `Ubah Jadwal` and `Ubah Detail` restore the corresponding step without clearing valid selections.

## Date and slot semantics

- Past dates are returned as `past` by month availability and are disabled in the calendar.
- Today only exposes future operating slots; already-passed slots are unavailable.
- Available, almost-full, full, unavailable, selected, and past states have distinct semantic classes, labels, and accessibility attributes.
- Slot capacity labels use the backend `capacity_remaining` value.
- Server-side scheduling validation remains authoritative at booking submission.

## Validation and QA

- `php artisan optimize:clear`
- `php artisan view:cache`
- `php artisan test tests/Feature/BookingServisStage4CTest.php`
- `php artisan test tests/Feature/ServiceSchedulingPhase2A1Test.php`
- `git diff --check` scoped to changed files

No migration, schema change, database reset, or architecture change is part of this refinement.
