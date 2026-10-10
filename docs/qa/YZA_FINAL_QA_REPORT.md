# YZA Final QA Report — Stage 14

## Release candidate result

READY FOR LOCAL DEMO / RELEASE CANDIDATE

Critical anomalies: 0  
High anomalies: 0  
Medium/low warnings: documented below

## Automated verification

- Existing baseline: 272 tests, 804 assertions.
- Stage 14 additions: 5 focused tests, 17 assertions.
- Final full regression: 277 tests passed, 821 assertions.
- artisan migrate:status: all 30 Laravel migrations applied.
- artisan view:cache: passed.
- artisan optimize:clear: passed.
- Stage 14 demo command: idempotent and verified.

## New Stage 14 coverage

The added tests cover integer percentage rounding, fixed-discount clamping, saved SPK persistence, nonactive draft prefill, authenticated preview access, preview read-only behavior, role protection, and deterministic simulation output.

Important workflows still rely on existing domain tests for payment, membership, Midtrans, service, car wash, sparepart, car sale, trade-in, Points, and authorization.

## Browser smoke

Executed with the local browser:

- landing page: rendered
- McLaren collection page: rendered with five colors and four gallery views
- login page: rendered
- unauthenticated admin SPK URL: redirected to login

Authenticated browser UAT, console log capture, and the complete responsive viewport matrix were not executed.

## Demo data

yza:stage14-demo was run successfully. Verified Laravel data includes:

- 4 active members and 1 pending member
- active promo scopes for service, car wash, sparepart, car sale, and trade-in
- 2 saved SPK simulations
- one AMAN / LAYAK and one BERISIKO associated promo

## Known limitations

Midtrans Sandbox only; no production gateway, promo stacking, Points redemption, membership tiers, recurring membership, or automated refunds/chargebacks.

## Backup

Laravel backup created at:

storage/app/qa_backups/yza_automotive_laravel_stage14_rc_20261004_123915.sql

Native database remains read-only and untouched.
