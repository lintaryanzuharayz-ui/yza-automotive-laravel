# Checkout reference refinement

6 November 2026. Target: member vehicle checkout (`/pelanggan/pesanan-mobil/create/{mobil}`). Follows the user's first reference image.

- Compact title and three-step indicator. Vehicle card on the left; buyer form above order summary on the right.
- Buyer fields form two desktop columns and one phone column. Existing authenticated customer values, `old()` input values, required fields, CSRF token, POST destination, selected-color field, and required agreement checkbox remain intact. Agreement is not automatically checked.
- Summary shows vehicle, current color, real stock badge, authoritative displayed price and the submit button on a horizontal desktop row. The submit button remains associated with `checkout-form` through its `form` attribute.
- Main photograph, thumbnails and summary image use contain to show complete source images. Corrected an old 390 px image-height rule that previously overflowed its aspect-ratio frame. Existing gallery/color behavior retained.
- Replaced USER/DOC and specification letter placeholders with SVG icons. Added palette to the existing local icon component.
- Responsive styles are scoped to checkout. No controller, payment, pricing, inventory, order creation or ownership logic changed.

## Checks

- Existing checkout/order tests: **25 tests, 143 assertions passed**. [Log](focused.log).
- Isolated preview fixture: **1 test, 8 assertions passed**, with test database identity assertion and rolled-back transactions. [Log](render.log).
- Browser geometry at **320, 375, 390, 430, 768, 1024, 1280, 1440, 1672, 1920** px: no horizontal document overflow; image remains inside frame with contain; submit button remains inside summary. [Matrix](viewport-matrix.json).
- Desktop/phone visually inspected. Next-photo counter changes to 02/04; summary image follows the gallery; selecting Pearl White updates both specification sections. Submit button's form association verified. Agreement remains required and unchecked. **No order was submitted.**
- CSS parsing, whitespace checks and browser console checks passed. Native browser zoom was not tested.
- [Desktop proof](screenshots/checkout-desktop.jpg) · [Phone proof](screenshots/checkout-mobile.jpg).

Screenshots use the real Blade page with sample member data in an isolated render. Production continues to prefill the signed-in customer's own information. No operational writes, messages, deployment, commit or push. Temporary preview server stopped after QA. Pre-change template and adjustment CSS preserved in [source-before](source-before/).
