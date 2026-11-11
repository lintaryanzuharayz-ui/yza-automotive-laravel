# Member origin-aware back navigation and centered footer

6 November 2026.

- Member detail now resolves the back target from `source` and a validated `return_to`. Member-home origins return to the vehicle collection on `/pelanggan/member` (or the dashboard alias); catalog origins preserve catalog filters and pagination. Missing context falls back to the member catalog. External, malformed, array-valued and unrelated return locations are rejected.
- Applies to real stock vehicle and showcase detail pages through their shared view. Public detail navigation remains unchanged.
- Footer uses equal flexible left/right columns around a centered navigation group. Old decorative pseudo-element and independent translate offset no longer displace the navigation. At 1100 px and below it stacks and centers the groups without overlap.
- Focused verification: **17 tests, 130 assertions passed** (`MemberVehicleBackLinkTest`, `VehicleConfiguratorTest`, `MemberFoundationStage10ATest`). PHP syntax, CSS parsing and whitespace checks passed.
- Browser checked a static render of the actual Blade views using isolated test fixtures: home-origin link points to `/pelanggan/member#yza-home-garage-title`; footer navigation center offset within 0.01 px horizontally at widths 320, 390, 768, 1024, 1280, 1440, 1920. No document overflow. Final desktop vertical offset within 0.5 px. No business actions or operational writes performed.
- [Footer geometry](footer-matrix.json), [back button proof](back-home.jpg), [centered footer proof](footer-centered.jpg).

Preview render helpers use the test database and transactions. The temporary preview server was stopped after inspection. No deploy, commit or push.
