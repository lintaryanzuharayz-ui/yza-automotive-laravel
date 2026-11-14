# YZA Member Booking Page Freeze Fix

Date: 12 October 2026

## Root cause

The booking page attached a `MutationObserver` to the entire booking root. `syncSideTotal()` then assigned `textContent` to the side total on every mutation, including the mutation caused by that same assignment. This created an unbounded observer/mutation loop that monopolized the browser main thread. The initial vehicle summary was therefore not painted reliably, even though the vehicle select was server-preselected.

## Fix

- Removed the recursive `MutationObserver` from the member booking page.
- Changed side-total synchronization to an explicit, value-guarded function call from summary updates.
- Added an explicit `data-initial-vehicle` value and set the select before the first summary update.
- Added `data-date` to calendar buttons so technician preference reset follows date changes correctly.
- Preserved the existing availability requests, abort sequencing, ownership validation, scheduling backend, and technician preference flow.

No browser-specific logic was added. `from=garage` remains a return-navigation flag only.

## Validation

- `php artisan optimize:clear`: PASS
- `php artisan view:cache`: PASS
- Booking, scheduling, and personnel tests: 38 passed, 169 assertions.
- Regression coverage confirms query-string vehicle preselection and absence of the recursive observer.
- No migration, schema change, database creation, authentication change, or member-data change was performed.

Manual authenticated Chrome/Edge verification remains dependent on an active browser session; the available automation surface did not expose the user's existing Edge window. The server-rendered route and focused regression suite pass.
