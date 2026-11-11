# Compact member vehicle detail

6 November 2026. User requested smaller, non-overlapping vehicle detail content, using Bugatti Chiron screenshots as the issue reference.

- Applies to the shared member vehicle detail template (`member-show.blade.php`), including stock and showcase vehicles. Public vehicle detail styles remain unchanged.
- Maximum page width 1440 px; heading capped at 40 px (28 px on phones), section headings 24/22 px. Prices, descriptions, buttons, panels, swatches, and thumbnails use compact sizes.
- Follow-up requested by user: brand mark, name, favorite, condition/availability badges and stock explanation now occupy one horizontal row. Redundant Brand/Automotive captions are hidden. The row scrolls locally on narrow screens instead of wrapping or widening the page. Previous geometry results for separate rows are historical.
- Latest follow-up: favorite is the last control at the right edge, after the badges. The redundant "Pre-order tersedia" stock note is omitted; the Pre-order Sekarang action remains. Empty-stock indicator uses red. Favorite toggle and no horizontal page overflow at 390 px verified. [Latest row proof](screenshots/favorite-right.jpg).
- Specifications use flexible columns, safe text wrapping, and two columns on narrow screens. Gallery is bounded on desktop and appears before vehicle information on a phone.
- Narrow header layout corrected within this page to prevent account controls overflowing at 390 px.
- Price, stock, purchase eligibility, pre-order flow, original image assets, color selection, routes and favorite behavior retain their original logic. No operational data, messages or orders were created during QA.

## Verification

- Existing focused tests: **11 tests, 117 assertions, passed** (`VehicleConfiguratorTest`, `VehicleAvailabilityStockTest`, `MemberVehicleCheckoutViewTest`). [Log](focused.log).
- Isolated render fixture: **1 test, 5 assertions, passed**. [Log](render.log). Explicitly checks the test database before rendering.
- Browser used static renders of the real authenticated view with test fixtures. Bugatti Chiron and Mercedes AMG tested at widths 320, 375, 390, 430, 768, 1024, 1280, 1440 and 1920 px: **18 geometry checks, no document overflow or brand/status collision**. [Matrix](viewport-matrix.json).
- Desktop/phone visually inspected. Gallery next changes counter from 01/04 to 02/04; color selection updates specifications; pre-order dialog opens/closes without submission. No browser console errors recorded.
- CSS syntax and whitespace checks passed. Native browser zoom was not tested.

[Desktop proof](screenshots/detail-desktop.jpg) · [Phone proof](screenshots/detail-mobile.jpg).

The original template is preserved in [source-before/member-show.blade.php](source-before/member-show.blade.php). The temporary preview server was stopped after QA.
