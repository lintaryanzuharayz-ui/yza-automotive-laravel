# YZA Member Landing QA

## Scope

Added the premium YZA Member section between the sport catalogue and the Jual Mobil / Trade-In section. Existing membership, registration, payment, promo, points, SPK, and transaction workflows were left unchanged.

## Verified behavior

- Guest: `BERGABUNG JADI MEMBER` links to the existing `register` route.
- Pending customer: `AKTIFKAN YZA MEMBER` links to the existing membership activation route.
- Active customer: `BUKA MEMBER CENTER` links to the existing Member Center route and shows `✓ YZA Member Active`.
- Cancelled customer: `LIHAT STATUS MEMBERSHIP` uses the existing activation/status flow; no reactivation logic was added.
- Admin and petugas: remain outside customer membership states and do not receive a customer Member Center CTA.
- Navbar: adds a restrained outline membership link with state-aware label and destination.

## Data and visual QA

- Activation price is read from `PengaturanService::membershipFee()` and is not hardcoded in the view.
- Marketing member card uses the fixed demo identifier `YZA-MBR-000001`; no real member identifier is exposed.
- Six concise benefits use only eligible/promo-safe business wording.
- Section uses the existing black, charcoal, white, and YZA red design language, with reduced-motion support.
- Mobile CSS stacks copy, price/CTA, member card, and benefits without introducing horizontal overflow.
- Browser smoke verified the landing section, CTA links, navbar, and zero horizontal overflow at the available desktop viewport.

## Tests

- `tests/Feature/YzaMemberLandingTest.php`: 5 tests, 31 assertions.
- Full regression after implementation: run `php artisan test --compact`; existing release-candidate tests remain covered.
