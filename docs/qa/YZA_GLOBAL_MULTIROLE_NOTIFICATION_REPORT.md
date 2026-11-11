# YZA notification appearance and animation — 7 November 2026

## Scope and status

Implemented the user's request to restyle notifications to the supplied image and add animation. The attached master prompt supplies context; this change does not claim to implement every new business event described there. Existing local work was preserved. No operational database updates, account resets, credential changes, business permission changes, commits, or deployments were performed.

## Existing system audit

- The existing `notifikasi` model stores recipient `id_user`, title, message, type, reference, and read state. There is no second notification table or second polling service in this change.
- `NotifikasiController` and `Admin/NotifikasiController` provide authenticated sync, user-scoped unread counts, read/open actions, and notification-center views. Existing open actions mark messages read and choose their destination on the server.
- The member shell has a dropdown; staff shells expose their existing notification-center link. Owner uses the generic authenticated sync route.
- Existing browser polling establishes a baseline and checks incoming IDs. This is polling, not WebSocket delivery. Its existing scheduling, event recipients, routing, and authorization remain in place.
- Member shell's initial badge previously counted unread items among only five recent messages. It now counts all unread rows belonging to that user.
- Audited notification producers include `ServiceNotificationService`, `BookingServisService`, `ServisService`, `ServiceExecutionService`, `InventoryService`, and the member vehicle purchase flow. Existing examples include admin service-booking and stock notifications, plus member booking decisions, service progress/approval/QC results, and vehicle-order updates.

## Shared architecture and appearance

`components.notification-toasts` loads one renderer in the shared application layout for authenticated users. Both existing live-toast callbacks now call `YzaToast.show`. Existing member flash toasts are converted to the same renderer. Legacy empty live-stack hooks remain for compatibility.

The visual follows the reference's rounded white cards, dark red member welcome, red status dot, colored gradient icon tile, close button, red progress track, and seconds label. Inline vector icons cover welcome, profile, orders, success, booking, and warnings. Desktop width is 360px; narrow screens use available viewport width. Toasts sit at the top right below the usual topbar. The dark welcome uses an existing YZA car image; it is not the exact photographic asset from the supplied screenshot.

Entrance combines a short slide and scale, icons settle into place, the red dot pulses gently, and exit fades/slides. Reduced-motion preferences disable decorative animation.

## Welcome and role coverage

A successful Laravel login event sets a user-bound session marker. Rendering the welcome consumes it. Refreshes do not repeat it, and a different user's marker cannot display a welcome. No credentials are embedded in source or reports. Welcome titles and descriptions are mapped to actual role keys; legacy `admin` and `petugas` roles are supported.

The automated test renders the component for **12 distinct active role names** in the isolated test database. “Render PASS” below means component/welcome rendering and no replay were tested; it does not mean every possible role-specific business event was exercised.

| Role | Welcome | Notification Toast | Unread Count | Click Navigation | Authorization | Status |
|---|---|---|---|---|---|---|
| Super Admin | Render PASS | Shared renderer | Existing feed | Existing routes | Unchanged | Component verified |
| Legacy Admin | Render PASS | Existing feed tests PASS | Feed tests PASS | Booking link test PASS | Member denial test PASS | Component + existing focused tests |
| Owner | Render PASS | Shared renderer | Existing feed | Existing routes | Read-only permissions unchanged | Component verified |
| Sales | Render PASS | Shared renderer | Existing feed | Existing routes | Unchanged | Component verified |
| Service Advisor | Render PASS | Shared renderer | Existing feed | Existing routes | Unchanged | Component verified |
| Mechanic | Render PASS | Shared renderer | Existing feed | Existing routes | Assignment rules unchanged | Component verified |
| Warehouse Staff | Render PASS | Shared renderer | Existing feed | Existing routes | Unchanged | Component verified |
| Finance | Render PASS | Shared renderer | Existing feed | Existing routes | Unchanged | Component verified |
| Operational Staff | Render PASS | Shared renderer | Existing feed | Existing routes | Unchanged | Component verified |
| Legacy Petugas | Render PASS | Shared renderer | Existing feed | Existing routes | Unchanged | Component verified |
| Quality Control | Render PASS | Shared renderer | Existing feed | Existing routes | Unchanged | Component verified |
| Member / Pelanggan | Render PASS | Existing sync test PASS | Feed test PASS | Existing routes | Existing recipient scope | Component + existing focused tests |

## Timer, queue, navigation, and lifecycle

- Welcome lasts 4 seconds; notifications last 7 seconds. The reference image's illustrative durations are replaced with the actual remaining duration.
- At most two cards occupy the stack. Others wait in FIFO order and begin their timer when mounted.
- A single animation loop calculates progress and remaining seconds from elapsed time. Hover, keyboard focus, and a hidden document pause the countdown.
- Close removes a card with its exit animation and frees its queue slot. Dismissal does not send a read request or delete the notification.
- Cards with an existing destination include “Lihat detail”. Payload copy uses `textContent`; only fixed icon markup uses SVG innerHTML. Cross-origin and non-HTTP navigation targets are rejected.
- Page exit cancels animation frames, close timers, event handlers, and queued cards. A restored page may receive subsequent messages without replaying its welcome.
- Per-document notification IDs prevent duplicate cards in the shared renderer. Cross-tab deduplication is not introduced.

## Validation and evidence

- `NotificationToastPresentationTest` + `AdminRealtimeNotificationsTest`: **8 passed, 59 assertions**, repeated after final layout cleanup.
- Full regression using `phpunit.xml` and `yza_automotive_laravel_test`: **589 passed, 1 failed, 2729 assertions** (131.62 seconds).
- The one failure is `MemberVehiclePurchaseFlowTest::test_member_order_detail_renders_premium_dashboard_for_owned_order`: it expects “Informasi Pembeli”; the locally modified order detail currently renders “Data Pembeli”. That view was already modified before this task and was not changed for notifications. The failure is recorded rather than changing the existing order redesign or weakening its test.
- Production build: PASS. JavaScript syntax check: PASS. PHP provider syntax check: PASS. `git diff --check`: PASS, with pre-existing line-ending warnings in other local files.
- Browser preview: verified dark/member and white/staff welcomes, green payment, yellow booking, automatic disappearance, close, two-card limit, and queued third-card appearance. A focused close button kept its popup visible beyond the normal 7 seconds while another popup expired; the mouse-over card also paused.
- Mobile preview: rendered the production component inside a 316px iframe; the cards fit the narrow viewport. The screenshot captures the entrance animation in progress.
- Screenshots: `notification-toast-welcome.png`, `notification-toast-mobile.png`. Interactive examples: `notification-toast-preview.html`, `notification-toast-mobile-preview.html` (serve repository root to use their relative asset links). These samples do not create database notifications.

## Files changed for this task

- `app/Providers/AppServiceProvider.php` — user-bound welcome marker after login.
- `resources/views/layouts/app.blade.php` — shared component, live-toast callbacks, accurate initial member unread count, removal of obsolete local toast variables.
- `resources/views/components/notification-toasts.blade.php` — role greetings and shared host.
- `public/assets/css/notification-toasts.css` — appearance, responsive rules, and motion.
- `public/assets/js/notification-toasts.js` — renderer, queue, timer, focus/hover handling, and cleanup.
- `tests/Feature/NotificationToastPresentationTest.php` — role coverage, session replay, account isolation, login event, and guest exclusion.
- This report and the preview/screenshot artifacts listed above.

## Remaining limits

Live login UAT with every real account and every business event was not performed. The role render test uses existing test users without passwords. The full master prompt's new recipient/event matrix, cross-tab coordination, all-role dropdown expansion, custom member-home polling coverage, WebSockets, and routing fallback improvements remain outside this visual change. No fictional events were added. Existing notification polling and order-state polling behavior is not represented as newly implemented functionality. Exact photographic and font matching cannot be certified from the screenshot alone.
