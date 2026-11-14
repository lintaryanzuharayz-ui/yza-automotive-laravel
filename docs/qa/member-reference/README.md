# Member homepage reference implementation

6 November 2026. Scope: `/pelanggan/member` and its dashboard alias, rendered by `member-center-empty.blade.php`.

The third image supplied by the user is the visual reference. This implementation follows its dark showroom, white supercar, red ceiling ring, italic welcome heading, right-hand membership card, overlapping statistics, and compact photographic service cards. It is a recreation, not a pixel-identical copy of the reference bitmap.

## Implementation and checks

- Six new generated backgrounds, JPEG encoded for the website, approximately 1.32 MB combined. Existing images were not overwritten.
- Styles scoped to `.yza-home-reference`; shared header adjustments apply only while this homepage is present.
- Live customer identity, member ID, membership state, dates, counts, points, existing service routes and ownership locks remain data-driven. The previously removed verified-vehicle note remains absent. Public landing navigation is unchanged by this task.
- Removed the old JavaScript that stripped gradient layers from every background; it prevented readable overlays on the new backgrounds.
- Follow-up: hero now has one **Beli Mobil** button targeting the member vehicle catalog. The **Lihat Benefit Member** button was removed at the user's request. Earlier screenshots show the original two-button version.
- Existing regression checks: **12 tests, 60 assertions, passed**, covering member rendering/login and verified ownership. See [focused.log](focused.log).
- Explicit visual fixture render: **1 test, 5 assertions, passed**. See [preview-render.log](preview-render.log).
- Frontend build passed; see [build.log](build.log). CSS syntax and whitespace checks passed.
- Browser geometry: no horizontal document overflow at 320, 375, 390, 430, 768, 1024, 1280, 1440, 1672 and 1920 px. See [viewport-matrix.json](viewport-matrix.json). Desktop and phone layouts inspected visually; native browser zoom was not tested.
- Screenshots: [desktop](screenshots/member-desktop.jpg), [phone](screenshots/member-mobile.jpg), [phone services](screenshots/services-mobile.jpg).

Browser screenshots use an isolated static render of the real Blade view with example data matching the reference scenario (1 vehicle, 4 bookings, 48 points, 5 promos). They do not assert that the production account has those values. A second static render checks locked services. The render helper uses the test database inside a rolled-back transaction; no operational data was changed. No external messages, booking submissions, deployment, commit or push occurred.

The local preview server is temporary and is stopped after QA. The actual application reads the new assets from its regular public asset folder.

## Generated assets and final prompt set

Mode: built-in `image_gen`; no API/CLI fallback. Hero and card prompts used the user's third image as a composition reference. Service images used textual scene prompts based on its four cards. All output assets were inspected before integration. Original generated PNGs remain in the Codex generated-image folder; the application uses these JPEG files:

| Asset | Saved application path |
| --- | --- |
| Showroom hero | `public/assets/images/member/member-reference-hero.jpg` |
| Digital member card | `public/assets/images/member/member-reference-card.jpg` |
| Booking Servis | `public/assets/images/member/reference-service-bengkel.jpg` |
| Test Drive | `public/assets/images/member/reference-service-test-drive.jpg` |
| Car Wash | `public/assets/images/member/reference-service-car-wash.jpg` |
| Trade-In | `public/assets/images/member/reference-service-trade-in.jpg` |

Final generation specifications:

1. **Hero:** Production website background only. Recreate the reference hero's luxurious dark automotive showroom: black glass wall panels, subtle distant city, suspended horizontal oval red neon ring, glossy wet black floor reflecting red lights, silver-white angular supercar in three-quarter front view facing right, cool headlights, black wheels and red calipers. Very wide 3:1 composition, center-third vehicle and ring, left 35% dark for live heading, right 30% dark for live membership card. Black/silver/red palette, realistic advertising photography. No text, logos, watermark, UI, navigation, statistics, buttons or membership card.
2. **Member card:** Background only, wide 1.7:1. Jet black to burgundy showroom haze, subtle red floor reflections. Rightmost 40% close rear three-quarter glossy black angular supercar with red taillights, cropped at right edge. Left 65% nearly plain black for live identity text. No diagonal stripes, perforated patterns, logos, text, numbers, border, UI or watermark.
3. **Booking Servis:** Luxury automotive service workshop photographic website card background, wide 2.2:1 landscape. Reference lower-left service card: mechanic in black uniform with thin red accents working at open engine bay of dark sports car, black and red modern garage. Mechanic and hood on right half; left half dark quiet space for live white text. Realistic premium photography, restrained red rim lights. No words, logos, UI or watermark.
4. **Test Drive:** Luxury automotive test-drive photographic card background, wide 2.2:1. Red angular supercar speeding on mountain forest road in three-quarter front view, car on right half, motion-blurred road and trees. Left half dark blurred road for live white text. Realistic automotive photography, sophisticated red/black palette. No words, logos, UI or watermark.
5. **Car Wash:** Luxury car-wash photographic card background, wide 2.2:1. White low supercar with white foam and falling water on right half; glossy wet black floor, narrow red ceiling lights, dark premium indoor wash bay. Left half dark quiet space for live white text. Realistic premium photography, black/white/red palette. No words, logos, UI or watermark.
6. **Trade-In:** Luxury showroom photographic card background, wide 2.2:1. Rear three-quarter white premium coupe mid-right and red sports coupe far-right, glass/dark-metal showroom, subtle red ceiling lights and glossy floor. Left half dark quiet space for live white text. Restrained realistic black/red/white photography. No words, logos, UI or watermark.

The complete pre-change Blade source is preserved in [source-before/member-center-empty.blade.php](source-before/member-center-empty.blade.php) for a targeted rollback.
