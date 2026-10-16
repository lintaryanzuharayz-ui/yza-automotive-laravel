# YZA Member Auth Permanent Recovery

Date: 12 October 2026

## Root cause

The reported Chrome username `lintar506` did not match the real member account. The verified account is `lintar12345`, belonging to Lintar Yan Zuhara, with role `pelanggan` and active status.

The member password is a valid bcrypt hash. The current hash was compared with the historical member row in `storage/app/backups/yza_automotive_laravel_before_pdi_20261011_204305.sql`; they match. No password value is documented here.

## Password repair

A development database backup was created before the repair attempt. The target was resolved by username and verified against the member relation. Because the active hash already matched the known working historical hash, no password field mutation was required. No other user or member fields were changed.

## Recurring mutation source

`database/seeders/IsolatedDemoDatabaseSeeder.php` used `User::updateOrCreate()` with a password in the update attributes for demo admin, staff, and member records. Re-running it could overwrite an existing demo user's password. The calls now use `User::firstOrCreate()`, so the password is assigned only when the user is newly created. The seeder remains fail-closed to `yza_automotive_laravel_demo`.

The legitimate existing-user password writer remains `ProfileController::updatePassword()`, which requires the current password and an explicit password-change request.

## Test database safety

`Tests\TestCase` now refuses to run when the testing environment resolves to any database other than `yza_automotive_laravel_test`. No automatic database creation or migration fallback was added.

## Navigation and host consistency

Guest `YZA Member` navigation now goes to `route('login')`. Authenticated active members go to `route('pelanggan.member')`. `Buat Member` remains on `route('register')`.

Application fallback URLs and PHPUnit's local URL were standardized to `http://127.0.0.1:8000`. Runtime session settings remain host-only, HTTP-compatible, and SameSite=Lax.

## Validation

- No migration was executed.
- No database was created or dropped.
- No admin password was changed.
- No member relation, membership, vehicle, order, service, or payment data was changed.
- Auth and navigation tests were added/updated for the isolated test database.
- Targeted auth/navigation validation: 5 passed, 23 assertions.
- Full suite: two unrelated pre-existing UI expectation failures remain in `AdminDashboardRegressionTest` and `MemberFoundationStage10ATest`; the auth/navigation tests pass.
