# Tahap 7 — Financial Integrity & Release Blocker Resolution

## Implemented

- `financial_adjustments` preserves the original payment and records refund/reversal requests.
- `financial_adjustment_events` records every state transition, actor, timestamp, reason, and evidence reference.
- Supported states: `requested`, `under_review`, `approved`, `processing`, `confirmed`, `rejected`, `failed`.
- Row locking and cumulative reservations prevent refund amounts from exceeding the original verified payment.
- Separation of duties prevents the requester from approving or confirming their own adjustment.
- Confirmation requires evidence of funds leaving the company.
- Owner remains read-only; Finance/Super Admin routes are protected by `finance.view` and `finance.verify`.
- Owner payment analytics now exposes gross verified, confirmed refunds, pending refunds, and net verified receipts.
- Confirmed adjustments are excluded conservatively from Sales Awards eligible sales.

## Midtrans

The existing notification path validates Midtrans signatures and deduplicates provider events with `event_key`. Provider statuses include refund/chargeback values, but an online refund execution API and verified production capability are not configured. The Financial Adjustment service therefore rejects payments linked to a gateway for offline refund creation; no real provider refund was sent.

## Verified in testing

- Full refund workflow: PASS.
- Partial and multiple partial reservations: PASS.
- Over-refund rejection: PASS.
- Unverified payment rejection: PASS.
- Separation of duties: PASS.
- Owner mutation rejection: PASS.
- Full regression: **404 PASS, 3 SKIPPED, 1,482 assertions**.
- Analytics profile (`php artisan yza:profile-analytics`, local): **21 queries, 92.26 ms, 28 MB peak memory** for the selected aggregate workload.

## Blocked

Awards finalization remains blocked by service guard because a complete authoritative refund/reversal policy is not yet verified for all payment channels, especially Midtrans provider refunds and post-handover partial refunds. No official award may be finalized until that policy and provider reconciliation are approved.
