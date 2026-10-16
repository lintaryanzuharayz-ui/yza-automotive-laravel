# YZA Member Dedicated Service Approval Page

## Scope

Member approval is presented on a focused page at `GET /pelanggan/servis/{servis}/approval`, named `pelanggan.servis.approval`. The existing service detail page remains available for full tracking and service information.

## Page flow

The dedicated page contains the member navbar, back link and breadcrumb, service hero with the real vehicle image and service data, execution timeline, and approval action card. Payment, diagnosis, general service items, reminders, invoice, and history remain on the general detail page.

## Approval behavior

Approval items use the immutable backend snapshot values for quantity, unit price, subtotal, and total. Each pending request has its own approve/reject actions and uses the existing decision endpoint. Resolved requests display a success or rejected state with a link back to service detail. Empty approval data displays a clean no-pending state.

## Links and security

Approval notifications deep-link to the named dedicated route. The controller keeps member authentication and customer ownership validation. No approval lifecycle, inventory, QC, payment, or database schema logic was rebuilt.

## Responsive behavior

The desktop layout uses the premium hero, horizontal nine-stage timeline, and a single focused approval card. The timeline scrolls horizontally on narrower screens; item pricing stacks and action buttons become full width on mobile.

## Validation

- `php artisan optimize:clear`
- `php artisan view:cache`
- Focused service execution/member approval tests
- Relevant route list check
- Scoped `git diff --check`
