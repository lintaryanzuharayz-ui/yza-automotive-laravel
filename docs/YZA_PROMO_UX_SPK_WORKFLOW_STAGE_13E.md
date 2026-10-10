# YZA Promo UX & SPK Workflow — Stage 13E

## Promo selector

Promo preview is exposed through authenticated Laravel routes and uses `PromoEngineService` on the server. The reusable Blade selector presents one promo at a time, scope, minimum spend, expiry, selected state, and a server-returned subtotal/discount/final total summary. The browser never calculates or submits financial authority.

The member center shows active promo cards and handles pending/cancelled membership with an activation message. Supported scopes remain isolated by the existing engine.

## SPK-to-promo orchestration

Admin promo creation accepts optional SPK projection fields and an explicit `approved_spk_simulation_id`. The create form can be prefilled through query parameters from a future saved simulation action, while the promo remains inactive by default for that flow. Promo detail shows the SPK score/recommendation alongside actual analytics.

Activating a promo associated with a `BERISIKO` recommendation requires an explicit checkbox confirmation and records a Master Audit event. Normal activation also uses the existing audit service.

## Invoice presentation

Service, car wash, and spare-part invoices show subtotal, snapshot promo code/name when present, discount, final total, payment balance, and payment history. Empty promo rows are omitted. Existing transactions without snapshots remain renderable.

## Security

Preview routes require authentication and a customer account. The server revalidates scope, dates, membership, minimum spend, quota, usage, and discount caps. One transaction remains limited to one promo; payment and redemption rules from Stage 13D are unchanged.

## Verification

Migration `2026_10_04_028000_add_spk_association_to_promo.php` was applied only to the Laravel database. Cache clearing completed. Full regression: **272 tests passed, 804 assertions**.

## Stage 13E.1 completion

The selector is embedded at finalization in service, car wash, sparepart, car sale, and trade-in workflows. It posts only promo_id; the authoritative amount is still recalculated by the existing promo engine during transaction creation. The preview route is authenticated for both customer and operations workflows.

Saved SPK simulations now have an admin list, create form, persisted result, detail view, and a canonical “Buat Draft Promo Nonaktif” action. That action pre-fills the existing promo form with the simulation ID, score, recommendation, and projections. The draft remains nonaktif until review and activation; risky activation still requires explicit confirmation and audit logging.

Car-sale and trade-in invoice views now show promo snapshot/name and the final transaction discount while remaining compatible with transactions that have no promo.

Migration 2026_10_04_029000_create_promo_simulations.php was applied only to the Laravel database. Full regression remains 272 tests passed, 804 assertions.
