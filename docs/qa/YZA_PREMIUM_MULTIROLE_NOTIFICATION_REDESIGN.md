# YZA premium notification dropdown redesign

Date: 7 November 2026. Scope: the user's supplied dropdown reference, with a dark member theme and the same structure in white for staff and owner.

## Audit and root cause

The member layout previously rendered a 460px white dropdown anchored to its bell wrapper. Its filters mixed full-page links with an inactive Important button. Staff and owner bells were direct links to their notification centers. Consequently, roles had different interactions and the member menu did not match the supplied dark reference. The legacy member dashboard has another older popover hook.

The existing `Notifikasi` table, recipient ownership, controllers, read/open routes, notification-center history, and existing polling remain the source of truth. No second notification table, recipient pipeline, polling interval, or business event was introduced.

## Implementation

- One `components.notification-panel` partial is included in the shared application layout. Member role `pelanggan` receives the dark theme; all other roles receive white. It queries only the authenticated recipient's records.
- Existing member, staff, owner, and legacy member bell hooks open the same component. The old member dropdown markup was removed from the main layout; legacy popover styling is suppressed. Existing hrefs remain as a navigation fallback if JavaScript is unavailable.
- Header, three filter positions, maximum four latest items, category icons, unread indicators, timestamps, chevrons, and notification-center footer follow the reference. Dark red geometry and a restrained red edge glow are exclusive to the member variant. White cards use navy text and pale neutral/red item backgrounds.
- Width is 380px, maximum height 490px. On narrow viewports, width uses the available containing-block width minus 24px, avoiding the vertical-scrollbar overflow caused by `100vw`. The panel is fixed, 16px from the content viewport's right edge on desktop and 12px on mobile. Its top is measured from the actual enclosing navbar/bell and updated on resize/scroll.
- Only the list scrolls. Its reserved height keeps the panel steady when switching between All and Unread. Header, filters, and footer remain available.
- Opening fades/slides down 8px over 200ms; closing fades/slides up 6px over 160ms. Item hover moves up 1px, changes border/background/shadow, highlights its icon, and moves the chevron 3px. Reduced-motion preferences disable decorative transitions.
- Bell controls expose `aria-controls`, `aria-expanded`, and keyboard focus. Bell toggle, outside click, Escape, and navigation close the panel. Arrow Down can enter the filter controls. Profile menus close when opening the panel. Focus returns to the bell when closing with Escape.
- Initial payload and badges use actual user data. All/Unread fetch at most four records through the existing authenticated sync endpoint using `panel=1`. This mode ignores the polling cursor, so the latest complete dropdown can be loaded even after a polling baseline. Existing polling responses also feed the shared panel without an additional polling loop. Superseded filter requests are aborted.
- The schema has no priority/important field or established importance classification. **Important remains visibly disabled**, with an explanatory title; no priority values were invented.
- Links use existing server-generated open routes. Rendering/filtering do not mark records read. Clicking uses existing ownership-protected open/read behavior. Payload strings use `textContent` and navigation URLs must share the current origin.
- Existing incoming/welcome toasts remain. While the dropdown is visible, toast presentation and countdown pause; queued messages resume after closing it. This prevents overlap with the dropdown.
- Owner and other staff notification-center pages now retain the appropriate shared staff/owner shell. Staff bell icon stroke/color is explicitly readable on a white navbar.

## Roles

Automated rendering covered 12 distinct active role names in the isolated test database: `pelanggan`, `admin`, `super_admin`, `owner`, `sales`, `service_advisor`, `mechanic`, `warehouse_staff`, `finance`, `operational_staff`, `quality_control`, and legacy `petugas`. Member alone receives dark styling. This verifies role-based component rendering; it is not a claim that every business event or every real account was manually exercised.

## Validation

| Check | Result |
|---|---|
| Focused panel, existing notification, toast, authorization, workspace navigation, order-image tests | 36 passed / 546 assertions |
| UI export using transaction-isolated test fixtures | 1 passed / 10 assertions |
| Production frontend build | PASS |
| Changed JavaScript syntax checks | PASS |
| Changed PHP controller syntax checks | PASS |
| `git diff --check` | PASS; unrelated pre-existing line-ending warnings remain |
| Full regression, `yza_automotive_laravel_test` | **594 passed, 1 failed / 2826 assertions**, 123.13s |

The remaining regression failure is the already-known `MemberVehiclePurchaseFlowTest` assertion expecting “Informasi Pembeli”; the existing local order-detail redesign says “Data Pembeli”. That order-detail view and its test were not changed for this redesign.

During focused navigation checks, a compatibility issue from the preceding order-image change was found: an auxiliary controller called the order index directly with one argument. The index now resolves `VehicleGalleryService` internally, preserving that caller's existing signature. Navigation and image tests pass after this correction.

### Browser evidence

Browser review used actual Blade pages exported from the isolated test database, with temporary notification records rolled back afterward. Preview fixtures replace the on-demand sync destination with a local JSON export; live application endpoints were exercised separately by feature tests. No operational accounts, passwords, or production records were changed.

- Member dark, admin white, and owner white panels were visibly checked and screenshots saved.
- Member filter switched from four items to one unread item without navigation. Escape reset the bell's expanded state. The disabled Important filter was visible.
- Hover was exercised using a real pointer over the first message: browser DOM reported `:hover = true`, changed background, and an in-progress negative Y translation; the chevron/icon treatment was visible in the screenshot. Animation was not declared verified solely from CSS.
- Eighteen same-origin iframe viewports exercised both member and backoffice themes at **320, 375, 390, 430, 768, 1024, 1366, 1440, and 1920px**, each 650px high. Every panel reported open, entirely inside its frame, with the footer inside panel bounds. No CSS zoom was introduced. Results are saved in `premium-notification/responsive-results.json`.
- Small-screen preview shows that the list scrolls while its footer stays visible. All nine widths have geometry evidence; full pixel-level review was not performed separately at every width.

Evidence in `docs/qa/premium-notification/`: `member-dark.png`, `admin-white.png`, `owner-white.png`, `mobile-320.png`, `responsive-results.json`, and `regression.txt`. HTML/JSON files in that directory are local QA previews, not new application routes or permanent database notifications. `tests/Ui/PremiumNotificationPreviewTest.php` can regenerate the preview pages.

## Changed source files

- `resources/views/components/notification-panel.blade.php`
- `public/assets/css/notification-panel.css`
- `public/assets/js/notification-panel.js`
- `resources/views/layouts/app.blade.php`
- `public/assets/js/notification-toasts.js`
- `app/Http/Controllers/NotifikasiController.php`
- `app/Http/Controllers/Admin/NotifikasiController.php`
- `app/Http/Controllers/Admin/MemberVehiclePurchaseController.php` (signature compatibility correction)
- `tests/Feature/PremiumNotificationPanelTest.php`
- `tests/Ui/PremiumNotificationPreviewTest.php`
- This report and the associated QA artifacts.

## Remaining verification limits

Live multi-account browser sign-in, resource deletion fallback, every role-specific business destination, cross-tab deduplication, and reduced-motion OS setting changes were not manually exercised in this task. Existing authorization and business permissions were retained. Important requires a real backend classification before it can be enabled. The single unrelated regression failure above remains visible. No commits, push, deployment, business workflow changes, or operational database writes were performed.
