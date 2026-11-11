# YZA Member Forced Logout Root Cause Audit

Audit date: 11 October 2026

## Scope

Member `lintar12345` was reported as logged out and subsequently rejected by the login form. This audit was read-only against the production account and did not reset credentials, create/delete users, reset the database, migrate fresh, or alter payment/PDI/handover/My Garage data.

## Findings

| Check | Result |
|---|---|
| User record exists | YES |
| User ID | 990 (preserved) |
| Username | `lintar12345` |
| Role | `pelanggan` |
| Account status | `aktif` |
| Member relation | PASS; pelanggan and membership relation present |
| Membership status | `active` |
| Password column | Present |
| Password hash format | Valid bcrypt metadata (`$2y$`, 60 characters); full hash not printed |
| `users.updated_at` | `2026-10-11 21:51:51` |
| Password writer in relevant workflow | None found |
| Commit `37df801` related to auth/password | NO; it changes only My Garage controller/view/CSS |
| Session storage | File driver; directory exists and is writable |
| `APP_URL` / browser host | `http://127.0.0.1:8000` matches the reported host |
| `APP_KEY` history | UNKNOWN; `.env` is ignored and no historical key is tracked |
| Actual user auth log evidence | None found for `lintar12345`, user ID 990, or an authentication exception at the reported time |

The `21:51:51` log entries are `testing`-channel entries produced by the test suite and refer to synthetic test records, not the production member. They do not identify a production password or status update.

## Authentication diagnosis

The application does not use `Auth::attempt()` directly; it resolves an active user by username or email and verifies the raw stored hash through `PasswordCompatibilityService`. The current account satisfies the active-user and membership checks.

Fixture diagnostics passed:

1. valid username login;
2. valid email login;
3. invalid password rejection;
4. session persistence after redirect;
5. authenticated access to `/pelanggan/kendaraan`;
6. My Garage access without credential mutation;
7. handover/ownership creation without credential mutation.

No evidence distinguishes the reported production event as a credential failure or a session failure. The plaintext password and browser request/session-cookie trace were not available, and the application log contains no matching production login event. Therefore the actual failure category remains **UNKNOWN / BLOCKED**, not “fixed” by assumption.

## Changes made

Added regression assertions to:

- `tests/Feature/AuthMemberLoginRegressionTest.php`
- `tests/Feature/MemberVehiclePurchaseFlowTest.php`

No production account, password, session configuration, ownership, vehicle, payment, invoice, PDI, or handover data was changed.

## Validation

- `php artisan optimize:clear`: PASS
- `php artisan view:cache`: PASS
- `php artisan test`: **321 passed, 1 skipped, 1058 assertions**

## Final status

**BLOCKED for definitive root-cause confirmation.** The source and fixture regression paths are healthy, but a definitive diagnosis of the real member's failed login requires one of the following safe, non-destructive evidence sources: the member's actual login attempt with the intended password, a timestamped production auth log entry, or a browser network trace showing whether the login POST succeeds and the subsequent session cookie is retained.
