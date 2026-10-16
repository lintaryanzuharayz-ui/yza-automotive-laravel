# YZA Global Login Failure Audit

Audit date: 11 October 2026

## Diagnostic matrix

| Check | `lintar12345` | `admin506` |
|---|---:|---:|
| Exists in CLI database | YES | YES |
| Exists in web database | YES | YES |
| User ID | 990 | 1 |
| Role | pelanggan | admin |
| Status | aktif | aktif |
| Password non-null | YES | YES |
| Hash format | Valid bcrypt | Valid bcrypt |
| Hash vs pre-PDI backup | DIFFERENT | DIFFERENT |
| Interactive plaintext check | NOT RUN; plaintext not supplied | NOT RUN; plaintext not supplied |
| Browser `Auth::attempt` result | NOT OBSERVED | NOT OBSERVED |

No password, role, membership, ownership, purchase, PDI, handover, or session data was changed during this audit. No password reset was performed.

## Database and configuration

- CLI connection: MySQL `127.0.0.1:3306`, database `yza_automotive_laravel`.
- Web request connection: MySQL, database `yza_automotive_laravel`.
- CLI and web databases: SAME.
- Test database: `yza_automotive_laravel_test`, forced by `phpunit.xml`.
- Development and test databases: ISOLATED.
- Application environment: `local`.
- Config cache: NOT CACHED after `optimize:clear`.
- Auth guard: `web` using the `session` driver.
- Auth provider: `users`, model `App\Models\User`.
- Session driver: `file`; storage is present and writable.
- `APP_KEY` historical comparison: UNKNOWN; `.env` is ignored and no previous key is tracked.

The temporary local-only web diagnostic endpoint was removed immediately after confirming the web database. It did not expose credentials and did not write data.

## Source audit

The login handler accepts username or email, requires `users.status = aktif`, verifies the raw stored hash, logs the user in, and regenerates the session. There is no role filter that would reject both admin and pelanggan.

The only existing-account password writer found in application code is `ProfileController::updatePassword()`. It verifies the current password and then stores a new bcrypt hash. Registration and admin customer/staff creation only create new users. No mass password update, password mutator, double-hash cast, My Garage, ownership, payment, PDI, or handover code writes `users.password`.

Both current hashes differ from the pre-PDI backup, and both records have a later `updated_at`. This proves the current hashes are not the backup hashes, but it does not prove whether the changes were intentional password changes or identify the actor/request. There is no matching production authentication/password audit entry in `laravel.log`.

## Root cause status

The database mismatch, test-database contamination, auth provider mismatch, inactive status, and global role filter hypotheses are disproven. The strongest evidence is **multiple account password values changed after the backup**, while the source contains no mass-update process. Definitive attribution remains blocked because plaintext credentials and request-level audit data are unavailable.

Related to My Garage redesign: **NO evidence**.

## Validation

- `php artisan optimize:clear`: PASS.
- `php artisan view:cache`: PASS.
- Full suite: **321 passed, 1 skipped, 1058 assertions**.
- Existing user IDs and relations were preserved.

## Final status

**BLOCKED for safe credential recovery.** Do not reset either account until the password-change event is identified or an approved targeted recovery procedure is provided. Safe next evidence is an interactive hidden-input check using the password the user believes is current, plus a timestamped browser/network trace or server audit trail for the password-change request.
