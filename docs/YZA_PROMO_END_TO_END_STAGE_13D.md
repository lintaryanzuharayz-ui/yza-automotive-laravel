# YZA Promo End-to-End — Stage 13D

## Architecture

The existing `PromoEngineService`, `PaymentService`, `InventoryService`, `YzaPointService`, and Midtrans services remain the single operational services. A transaction is the financial source of truth; promo data is stored as an immutable snapshot on `transaksi`.

## Financial flow

The server receives only a promo identifier. It recalculates eligibility, scope, dates, membership status, minimum transaction, quota, usage-per-member, percentage caps, integer-Rupiah rounding, and the non-negative final total. The transaction stores the original subtotal, discount, final total, promo code/name/type/value snapshot, and promo relation.

Supported finalization adapters cover service, car wash, spare-part sale, car sale, and the existing trade-in payable transaction. Inventory mutation remains inside `InventoryService` and is not performed by promo code.

## Redemption and payment

Promo selection changes the server-side final total but does not consume a completed redemption. `PromoRedemption` is finalized only when the transaction reaches `lunas`; the promo row is locked while quota and member usage are checked. Manual payment and Midtrans both use `transaksi.total`, so the after-discount amount is the amount due. Membership activation remains outside transaction points.

## Points and invoices

Existing idempotent point awarding runs after domain completion and uses the transaction final total. Existing invoices remain backward-compatible for historical transactions without promo columns; promo snapshots and totals are available through the transaction relation.

## Analytics and projection

`PromoAnalyticsService` reads completed redemption snapshots and reports redemption count, unique members, original value, discount, after-promo revenue, estimated cost/margin, variance against saved projection values, and deterministic performance status. Missing cost data is reported as `COST DATA INCOMPLETE`; it is never fabricated. Admin pages are available at `admin/promo-analytics` and per-promo detail routes.

## Security and compatibility

Frontend discount/subtotal values are not trusted. Scope, active period, active membership, ownership, quota, and usage checks are server-side. One transaction supports at most one promo. Historical transactions preserve their snapshots if an admin edits future promo behavior. Native database data is not used or modified.

## Verification

Migrations were applied to the Laravel database. Full regression: **272 tests passed, 804 assertions**. The existing baseline remains green.

## Known follow-up

The existing invoice templates still render the canonical subtotal/discount/total fields and are backward-compatible. A later UI polish can add a dedicated promo label block to every invoice template without changing financial behavior.
