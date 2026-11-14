# Notification dropdown compact bugfix — 8 November 2026

## Root cause confirmed before changes

The shared Blade panel included an extra `ynp-pending` section headed “Perlu tindakan saat ini”. Its JavaScript rebuilt that section from `pending_actions`. Because this non-shrinking block shared a capped panel with the header, filters, footer, and a fixed 309px list, the list lost most of its usable height. A red `scrollbar-color` accentuated the resulting scrollbar. Single-line descriptions concealed useful context. The width itself was already 380 CSS pixels; Windows display scaling can make it appear larger in a screenshot.

Counters already came from separate backend definitions; mixing them visually in the dropdown made their meanings harder to distinguish. No change to the counting or event services was required for this bugfix.

## Operational data audit — read only

An isolated PHP process bootstrapped the existing application and issued `SET TRANSACTION READ ONLY` before querying state and independent notification counts. No authentication sessions, passwords, business records, or operational notification rows were changed.

For the legacy Admin account shown in the screenshot (internal account ID 1):

| Counter | Actual result | Source |
|---|---:|---|
| Bell unread | 3 | `NotificationStateService` eligible recipient unread |
| Notification Center unread | 3 | Independent `notifikasi` recipient + `is_read = false` query |
| Semua / total history | 6 | Recipient's total notification records |
| Orders awaiting verification | 1 | `pesanan_mobil_member.status = menunggu_verifikasi` |
| Preorders awaiting stock | 1 | `pre_order_mobil.status = menunggu_stok` |
| Spareparts at/below minimum | 15 | Active spareparts with `stok <= stok_minimum` |

The separate modern Super Admin account had 2 unread / 2 total, confirming the counts are recipient-specific. Neither account had duplicate unread groups with the same reference type, resource ID, and title. Existing source-level deduplication and read-history preservation were retained. The 15 count describes sparepart alert items, not unread messages or car quantity. The numbers were not forced equal.

## Changes

- Removed the operational summary markup and its initial panel payload field **from the dropdown only**. `pending_actions`, pending counts, domain events, dashboard data, and sidebar task badges remain available in their existing services.
- Removed only the JavaScript that rendered the deleted task section and its obsolete CSS. `clear()` now works without looking up the removed nodes.
- Kept the shared white staff/owner theme and dark member theme. Desktop width remains 380px; maximum height is now 480px and is capped by available viewport height. Right margins remain 16px desktop / 12px mobile, with navbar-based vertical positioning.
- Header, filters, and footer remain fixed within the panel; only the message list scrolls. The compact list reserves up to 324px and can shrink within short viewports.
- Tightened icon size to 32px, item padding, title/timestamp spacing, and inter-item gap. At most four latest messages are rendered. Titles wrap safely; descriptions use an intentional two-line excerpt with full text retained in the title attribute and detail page. This avoids broken horizontal overflow while keeping the panel compact.
- Replaced the red scrollbar with a thin neutral gray thumb, including a 4px WebKit scrollbar and matching Firefox behavior.
- Preserved the 200ms fade/8px slide entrance and 180ms hover transitions: background/border/shadow, 1px upward movement, and 3px chevron movement. Reduced-motion handling remains.
- Kept bell toggle, outside-click/Escape close, keyboard entry, source-generated open URLs, backend authorization, read synchronization, and no automatic reading timeout.
- Important remains disabled with its existing explanation because no priority classification exists. It was not wired to arbitrary messages.

## Files changed

1. `resources/views/components/notification-panel.blade.php`
2. `public/assets/js/notification-panel.js`
3. `public/assets/css/notification-panel.css`
4. `tests/Feature/PremiumNotificationPanelTest.php` — verifies the summary is absent for every active role variant.
5. `tests/Ui/notification-panel.test.cjs` — functional panel interactions.
6. This report and the bugfix test logs.

No event producer, permission, payment, invoice, promo, business transaction, migration, or operational database record was modified.

## Verification

- Focused notification/panel/isolation/reconciliation tests: **21 passed / 285 assertions**. Log: `docs/qa/notification-dropdown-bugfix-focused.txt`.
- Panel and shared synchronization JavaScript interaction tests: **12 passed**. They check four-item limit, 3 unread versus 6 total, ignored task summaries, open/close, no reading timeout, All/Unread filters, keyboard/outside close, same-origin detail URLs, safe rendering, read-state synchronization, account isolation, and duplicate suppression.
- Existing PHP tests cover click/read ownership, assignment restrictions, all active role themes, notification center counts, pending-task independence, and committed-event deduplication.
- Full Laravel regression on the isolated testing database: **617 passed / 1 failed / 3106 assertions**, 107.94 seconds. Log: `docs/qa/notification-dropdown-bugfix-regression.txt`.
- The only failure remains the previously documented `MemberVehiclePurchaseFlowTest` label assertion: expected “Informasi Pembeli”; existing order-detail view says “Data Pembeli”. That view/test was not changed here.
- Frontend build PASS (Vite, 58 modules). Changed JavaScript syntax PASS. `git diff --check` PASS, with unrelated pre-existing line-ending warnings.

## Visual/browser verification limits

Automated interaction tests above use a DOM/network harness and do not constitute real-browser visual or pointer-hover verification. Responsive constraints, independent scrolling, hover timings, and reduced-motion rules were audited in the shared stylesheet. Actual desktop/mobile appearance at browser zoom 100%, real pointer-hover rendering, and four-item scrolling still require an allowed live-browser observation. No mockup or test-data screenshot is supplied as proof of the user's actual page. Existing browser-security restrictions must not be bypassed.

No commit, push, or deployment was performed.
