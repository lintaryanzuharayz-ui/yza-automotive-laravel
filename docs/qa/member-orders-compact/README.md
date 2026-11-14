# Compact member orders page

6 November 2026. Route: `/pelanggan/pesanan-mobil`.

The oversized title, hero spacing and large photographic columns were replaced with a compact composition: heading and three live summary tiles, a single desktop filter row, horizontal order cards, actual status/progress information and clear pagination counts. On phones, cards stack and the progress row scrolls locally.

Vehicle photos use `object-fit: contain` with no hover zoom and no fixed minimum image height. The complete source photograph fits inside its frame. Neutral bands may appear where a photo's aspect ratio differs from its frame; the image is not cropped to fill that space.

The existing controller remains unchanged. Customer scoping, real order counts, status/timeline/date presentation, search/status/sort queries, 5-item backend pagination, detail links and copy-number behavior retain their existing logic. Pagination now shows the actual visible range even for a single page. A filtered empty result is distinguished from an account with no orders. No invented order information is inserted into the production view.

## Verification

- Existing order/checkout regression: **25 tests, 143 assertions passed**. [Log](focused.log).
- Explicit preview fixture: **1 test, 14 assertions passed**. Uses the isolated test database with a rollback transaction and asserts database identity before seeding example rows. Checks the real rendered route and filters for cancelled, completed and awaiting-payment examples. [Log](render.log).
- Browser geometry verified at widths **320, 375, 390, 430, 768, 1024, 1280, 1440 and 1920**: no horizontal document overflow, search field stays inside its control, all vehicle image elements use contain and no scaling transform. [Matrix](viewport-matrix.json).
- Desktop and phone visually inspected. All three example photos loaded successfully; no browser console errors recorded. CSS parsing and whitespace checks passed. Native browser zoom was not tested.
- Browser screenshots are static renders of the actual view with **sample test orders**, not the user's operational account data. [Desktop](screenshots/orders-desktop.jpg) · [Phone](screenshots/orders-mobile.jpg).

Only the list Blade view and its dedicated stylesheet were changed. No payment, reservation, PDI, handover, inventory or ownership logic was modified. No operational writes, messages, orders, deploy, commit or push occurred. Temporary preview server stopped after QA.

Pre-change view and CSS snapshots are in [source-before](source-before/).
