# YZA Membership Access Model Audit

Audit scope: Laravel project `C:\xampp\htdocs\yza_automotive_laravel`. This is a static/source audit with read-only database inspection. No business-rule, authentication, payment, booking, Trade-In, schema, or data changes were made.

Audit date: 2026-10-07 (Asia/Jakarta)

## 1. Executive Summary

**Status: HIGH RISK for direct enforcement; SAFE WITH MIGRATION for a staged rollout.**

The codebase already contains a useful membership payment foundation, but it is still a hybrid model:

- Registration creates an `aktif` user and `aktif` customer immediately, creates a `pending_payment` membership, logs the user in, and sends them to activation.
- Login checks only `users.status = aktif`; it does not require an active membership.
- Most customer routes use only `auth` + `role:pelanggan`.
- Active-membership checks are applied selectively in dashboard, points, reminders, and Trade-In entry, not globally.
- Existing customer, booking, service, transaction, vehicle, and Trade-In history are relationally dependent on `pelanggan` and must not be deleted or detached.

Therefore, changing `pelanggan = active member` in one step would create authorization regressions, block legitimate pending-payment recovery, and risk historical-data access. The target model is viable after a compatibility layer, a centralized active-member middleware/policy, a revised registration state model, and route/test migration.

## 2. Current Architecture

Authentication uses Laravel's `web` session guard and the Eloquent `User` model (`config/auth.php`). `users` belongs to a `Role`; a user can have one `pelanggan` or `petugas` record. `RoleMiddleware` only compares `roleName()` and has no membership awareness (`app/Http/Middleware/RoleMiddleware.php`).

The current customer identity chain is:

```text
users → roles
users ← pelanggan → memberships
pelanggan → kendaraan_pelanggan, bookings, test_drive, booking_servis,
booking_cuci, servis, transaksi, pengajuan_mobil, trade_in, points
```

Membership is one-to-one with `pelanggan`, with actual schema statuses `pending_payment`, `active`, and `cancelled` (`2026_10_04_020000_create_memberships_table.php`). There is no `expired` column/status or validity date in the current schema.

## 3. Current User Flow

### New registration

```text
GET /register
→ RegisterRequest validation
→ validate region hierarchy
→ create users(status=aktif, role=pelanggan)
→ create pelanggan(status=aktif)
→ create points account
→ create memberships(status=pending_payment, activation_fee from settings)
→ audit registration
→ Auth::loginUsingId()
→ regenerate session
→ redirect /pelanggan/membership/aktivasi
→ create/reuse Midtrans membership payment
→ Midtrans notification or status sync
→ settlement/capture
→ membership.status = active
```

This is implemented in `RegistrationController::store`, `MembershipController`, and `MembershipPaymentService`.

### Current login

```text
POST /login
→ locate username/email where users.status=aktif
→ verify password compatibility
→ Auth::login()
→ session regenerate
→ redirect by role
```

There is no `membership.status = active` condition in `AuthController::store`.

## 4. Target User Flow

```text
Guest
→ Public Website
→ WhatsApp OR Membership Registration
→ provisional registration data
→ Midtrans payment
→ verified settlement/capture
→ active membership/account
→ login
→ member dashboard and digital services
```

Existing records should remain readable for administration and historical integrity. A non-active historical customer should become a compatibility/historical identity, not a new-service account.

## 5. Authentication Audit

### Findings

1. A user can be created and logged in before membership payment is complete.
2. A pending-payment customer can authenticate because `AuthController` filters only user status.
3. A cancelled membership customer can authenticate if the user remains active.
4. Login redirects by role only; a pending customer is redirected to the customer dashboard, where the dashboard may redirect to activation.
5. `RoleMiddleware` is not a membership gate and is unsafe as a global replacement for active-member authorization.
6. `MemberIdentityService::assertActive()` checks customer status, user status, and membership active status, but is called only by selected services/controllers.
7. Laravel session fixation protection is present through session regeneration after login; logout invalidates the session and regenerates the CSRF token.
8. Login throttling is present via `RateLimiter` (five attempts, 60-second hit window).

### Answers

- Can a user register without membership? **Yes, in the sense that the user/customer row is committed before payment; the registration UI requires membership confirmation but not settlement.**
- Can a non-active customer log in? **Yes, if `users.status` is active.**
- Login requirement: active user row plus valid password; no active membership requirement.
- Role distinction: `roles.nama_role` values used by code include `admin`, `petugas`, `pelanggan`.
- Membership is checked at dashboard/selected feature boundaries, not at login.
- Immediate blocking at login would break pending-payment recovery, existing history access, and current tests unless a separate payment/recovery session flow is introduced.

## 6. Registration Audit

`RegisterRequest` requires name, phone, email, username, password, gender, birth date, four region codes, and region hierarchy resolution. It also has `current_step` and `membership_confirmed` rules. The form presents the activation fee from `PengaturanService::membershipFee()`; the service default is Rp249.000 if no setting exists.

The registration controller creates all identity records in one transaction, then logs in immediately. There is no provisional-registration table, no email/phone verification state, and no payment-before-account activation boundary. Failed Midtrans creation leaves an active user/customer with pending membership.

Target gap: the account should be disabled or non-loginable until verified settlement/capture, while preserving a secure payment-resumption identity. This requires a deliberate state design, not simply changing `users.status` after registration without recovery handling.

## 7. Membership Audit

`Membership::isActive()` is exactly `status === 'active'`. Current statuses are:

| Status | Current meaning | Active-member access? |
|---|---|---:|
| `pending_payment` | Registered, payment not verified | No |
| `active` | Payment verified/activated | Yes |
| `cancelled` | Cancelled membership | No |

There is no expiry implementation. `activated_at` exists, but no `starts_at`, `expires_at`, renewal period, or scheduled expiry logic was found. Expired/inactive requirements therefore need a future status/date strategy; do not invent an enum during this audit.

`MemberIdentityService::assertActive()` is the strongest existing authorization helper. It throws a runtime exception rather than returning a redirect, so it needs an HTTP-aware middleware/policy wrapper before being applied globally.

## 8. Midtrans Audit

The membership flow preserves important safeguards:

- Existing `created`/`pending` gateway attempt with a redirect URL is reused.
- New attempts use `YZA-MBR-{membership}-{random}` order IDs.
- Gross amount is sent from the membership activation fee.
- Notification signature is verified by `MidtransClient`.
- Gateway events are idempotent via `event_key`.
- Settlement and non-denied capture activate a `pending_payment` membership.
- Gross amount mismatch becomes `requires_review`.
- Finish callback can work with an empty `order_id` by falling back to the latest gateway record.
- Return/status sync uses the provider status API rather than trusting the browser.
- Active memberships are rejected from creating another activation attempt.

Risks/gaps:

- `MembershipPaymentService::processNotification()` does not guard against a later negative status reversing an already active membership; status lifecycle policy should be explicit before expiry/cancellation is added.
- Membership activation has no expiry/renewal handling.
- `pembayaran_gateway.id_transaksi` is initially non-null in the base migration and later changed nullable to support membership payments; this is correct for the current design but must be preserved.
- Payment notification is public by design, but signature/order/amount checks are the security boundary.

## 9. Member Center Audit

`/pelanggan/member` and `/pelanggan/dashboard` both call `DashboardController::pelanggan`, which delegates to `MemberDashboardService`. The service calls `assertActive()`. A pending/cancelled customer is redirected to activation by the controller before dashboard data is built.

The activation page distinguishes active, pending provider status, pending payment, failed/other provider state, and cancelled state in the view. The active state links to the dashboard. The route is still named/implemented as activation and should remain available as a payment recovery surface during migration.

## 10. Feature Access Matrix

| Feature | Guest | Current Customer | Active Member | Target |
|---|---|---|---|---|
| Landing/catalog/detail | Yes | Yes | Yes | Public |
| Login | No | Yes if user active | Yes | Active member plus staff |
| Registration | Yes | Guest-only route | N/A | Membership registration |
| Member dashboard | No | Route reachable; pending redirects; legacy no-membership may pass | Yes | Active member only |
| Profile | No | Yes by role | Yes | Active member/history compatibility |
| Vehicles | No | Yes by role; no active gate | Yes | Active member only for new service use |
| Car booking | No | Yes by role; no active gate | Yes | Active member only |
| Test Drive | No | Yes by role; no active gate | Yes | Active member only |
| Service booking | No | Yes by role; no active gate | Yes | Active member only |
| Car Wash booking | No | Yes by role; no active gate | Yes | Active member only |
| Service history | No | Yes by role; ownership scoped | Yes | Active member/history policy |
| Trade-In entry | Redirects to register | Redirects to activation if inactive | Yes | WhatsApp for guest; active member online |
| Trade-In submission/history | No | History routes lack universal active gate; create checks active | Yes | Active member only for online actions; history retained |
| Points | No | `assertActive` | Yes | Active member only |
| Service reminders | No | `assertActive` | Yes | Active member only |
| Member promos | No | Route/controller does not call `assertActive`; view warns | Yes | Active member only or public promotional preview |
| Customer invoices | No | Ownership checks, not universal active gate | Yes | Active member for portal; historical compatibility read-only |
| WhatsApp consultation | Public footer/home/detail links | Yes | Yes | Public fallback |

## 11. Route Access Matrix

| Route group | Current middleware | Current access | Target access | Required change |
|---|---|---|---|---|
| `/`, `/mobil`, `/koleksi/*`, `/mobil/*` | None | Public | Public | Keep public |
| `/login`, `/register` | `guest` | Guests only | Membership registration/login | Revise post-registration state; retain payment recovery |
| `/pelanggan/dashboard`, `/pelanggan/member` | `auth`, `role:pelanggan` | Customer role; controller gate | Active member | Add centralized active-member middleware, with explicit recovery exception only where needed |
| `/pelanggan/membership/*` | `auth`, `role:pelanggan` | Pending/active customer | Pending/recovery and active status surface | Keep separate from service routes |
| `/pelanggan/kendaraan/*` | `auth`, `role:pelanggan` | Any customer role | Active member | Add active-member authorization; preserve admin/history access |
| `/pelanggan/booking-mobil/*` | `auth`, `role:pelanggan` | Any customer role | Active member | Add gate |
| `/pelanggan/test-drive/*` | `auth`, `role:pelanggan` | Any customer role | Active member | Add gate |
| `/pelanggan/booking-servis/*` | `auth`, `role:pelanggan` | Any customer role | Active member | Add gate |
| `/pelanggan/booking-cuci/*` | `auth`, `role:pelanggan` | Any customer role | Active member | Add gate |
| `/pelanggan/servis/*`, invoice routes | `auth`, `role:pelanggan` | Ownership scoped, membership not universal | Active member or historical read-only | Separate active action routes from history reads |
| `/pelanggan/pengajuan-mobil/*` | `auth`, `role:pelanggan` | Any customer role | Active member | Add gate; preserve existing submissions |
| `/pelanggan/trade-in/*` | `auth`, `role:pelanggan` | Create checks active; index/show do not | Active member | Gate online actions and decide history compatibility policy |
| `/pelanggan/poin`, `/pelanggan/pengingat-servis` | `auth`, `role:pelanggan` | Explicit `assertActive` | Active member | Retain, move to common middleware |
| `/pelanggan/promo*` | `auth`, `role:pelanggan` | Customer role; view warns if inactive | Active member or public marketing | Decide policy and enforce server-side |
| `/promo-preview*` | `auth` only | Any authenticated role | Internal/member-scoped as appropriate | Add role and active-member policy or rename to public calculator |
| `/admin/*` | `auth`, `role:admin` | Admin | Admin | Keep |
| `/petugas/*` | `auth`, `role:petugas` | Staff | Staff | Keep |
| Midtrans notification | None | Public provider callback | Public provider callback | Keep signature and idempotency checks |

## 12. Database Dependency Audit

The schema has strong foreign-key dependencies and history relationships:

- `pelanggan.id_user` is nullable, unique, and null-on-delete. This supports legacy/walk-in customers without accounts.
- `memberships.id_pelanggan` is unique and cascade-deletes with the customer.
- Bookings, vehicles, services, transactions, submissions, and Trade-In records reference customer IDs.
- Gateway payments reference either transactions or memberships; membership gateway payments require nullable `id_transaksi`.
- Transaction/payment history is not safe to migrate by deleting or merging customers.
- Existing admin-created/walk-in customers are a supported legacy shape, not an anomaly to remove.

No database changes were made. No migration should be written until the compatibility state model is approved.

## 13. Existing Data Compatibility

Read-only inspection of the configured local database reported:

| Entity | Count |
|---|---:|
| users | 8 |
| pelanggan | 8 |
| memberships | 8 |
| membership gateway payments | 2 |
| gateway events | 1 |
| kendaraan_pelanggan | 1 |
| booking_mobil | 2 |
| test_drive | 1 |
| booking_servis | 1 |
| booking_cuci | 1 |
| servis | 2 |
| transaksi | 7 |
| pengajuan_mobil | 2 |
| trade_in | 1 |

Membership rows observed: five `active`, three `pending_payment`; no `cancelled` row was present in the inspected data. All eight customers had a membership row. Three users were staff (`admin`/`petugas`) and five users had the customer role; three customer records had no linked user, consistent with legacy/walk-in compatibility. Existing service/booking/transaction/customer-owned rows were attached to active memberships in this snapshot.

These counts are an observation of the configured local database at audit time, not a migration instruction. Production counts must be re-run in a controlled read-only report before rollout.

## 14. WhatsApp Fallback Audit

WhatsApp is already used in:

- `resources/views/components/public-footer.blade.php`
- `resources/views/public/home.blade.php` appointment form
- `resources/views/mobil/show.blade.php` vehicle-specific inquiry links

The current public links use the number `6287784304417` directly in views. There is a `PengaturanService` setting for `no_hp_perusahaan`, but the inspected WhatsApp links do not consistently use it. Context-aware vehicle messaging exists on the vehicle detail page; general service-specific fallback messages are not centralized.

Required future improvement: one WhatsApp URL/message helper backed by the configured company number, with messages for vehicle, Test Drive, Service, Car Wash, Trade-In, and general consultation. This is a SHOULD/MUST boundary depending on whether public online forms are removed.

## 15. Security Risks

High-priority risks for the target model:

1. `role:pelanggan` is not equivalent to active membership. Direct URLs can reach multiple customer features while membership is pending/cancelled.
2. `DashboardController` checks non-active membership only when a membership row exists; a legacy customer with no membership can bypass that condition.
3. Several customer controllers enforce ownership but not active membership. Ownership is good IDOR protection, but it does not satisfy the new business rule.
4. `PromoMemberController` and preview routes have weaker active-member enforcement than points/reminders.
5. Trade-In index/show and pengajuan routes need a deliberate distinction between historical read access and creating new actions.
6. Blocking at login without a payment recovery mechanism can strand pending customers and create support/security workarounds.
7. UI hiding alone is insufficient; every new member-only endpoint needs backend authorization.

Positive controls found: login throttling, session regeneration, role middleware, ownership checks in many detail routes, Midtrans signature verification, server-side amount verification, event idempotency, row locks in payment flows, and no trust in browser payment status.

## 16. Regression Risks

- Existing registration tests use an older minimal payload and now fail validation because the current form/request requires address, gender, birth date, region codes, and membership step data.
- Pending-payment recovery may break if login is blocked before the user can reach membership activation.
- Historical invoices, service records, vehicles, bookings, and Trade-In records must remain readable to authorized staff and possibly to the owner under a history-only policy.
- Existing admin-created customers with no user or old customers without membership must not be silently deleted or auto-promoted.
- Public Trade-In currently sends guests toward registration; target behavior requires a WhatsApp fallback or membership registration choice.
- Removing customer-side routes without updating UI, tests, and saved deep links will create dead links.

## 17. Test Impact

Full project test command run: `php artisan test --no-coverage`.

Result:

- **282 passed**
- **3 failed**
- **0 skipped reported**
- **285 total**
- **854 assertions**
- Duration: approximately 39 seconds

The three failures are in `tests/Feature/IdentityStage3CTest.php`:

1. valid registration
2. registration role injection
3. password change setup after registration

All fail because the tests submit the old minimal registration payload; the current `RegisterRequest` rejects missing required region/gender/birth-date fields, so the expected `/login` redirect is not reached. No tests were changed or deleted during this audit.

Tests that directly cover the future policy include member landing, membership/Midtrans, booking ownership, Trade-In, points, reminders, and final system authorization tests. New tests are still required for pending/cancelled direct access across every customer route.

## 18. Required Changes

### MUST

- Introduce one backend active-member middleware/policy based on existing `Membership::isActive()` and `MemberIdentityService` semantics.
- Apply it to all new-service/member routes, not just the dashboard.
- Separate payment activation/recovery routes from active-member service routes.
- Decide and implement the login state for pending payment: blocked login with secure recovery token, or limited recovery session that cannot access member services.
- Preserve historical customer, booking, vehicle, service, Trade-In, and transaction rows.
- Add route/controller tests for pending, cancelled, missing-membership, inactive-user, active-member, guest, and cross-owner cases.
- Centralize WhatsApp number and context-message generation before converting public service CTAs.

### SHOULD

- Add explicit membership lifecycle fields/statuses only after business approval for expiry/renewal semantics.
- Split history read authorization from new-service authorization.
- Make Member Center the active-member landing page and keep activation as a recovery/status page.
- Add an audit command/report for orphan and compatibility records.
- Update stale identity tests to submit the current registration contract.

### OPTIONAL

- Add email/phone verification before payment.
- Add a dedicated provisional-registration table if payment-before-account creation is required.
- Add a public WhatsApp consultation component with service-specific presets.

## 19. Migration Strategy

Recommended safe strategy:

1. **Compatibility inventory:** snapshot counts and orphan checks for users, customers, memberships, and all customer-owned history.
2. **Policy first:** implement a read-only `active member` authorization policy/middleware without changing current data.
3. **Route classification:** mark each route as public, active-member, member-recovery, historical-owner, or staff/admin.
4. **Registration transition:** keep current identity creation temporarily but mark new accounts as non-service/non-loginable until settlement, or introduce a provisional registration record. Do not remove the existing recovery path until tested.
5. **Payment activation:** activate the account/member service entitlement only after verified settlement/capture. Keep webhook idempotency and server-side status sync.
6. **Historical compatibility:** retain legacy `pelanggan` and all foreign keys. For missing memberships, classify as legacy/history-only rather than auto-deleting or blindly activating.
7. **Public fallback:** route guest service intents to WhatsApp; route membership intent to registration/payment.
8. **Progressive enforcement:** enable active-member middleware by feature group, beginning with new booking/action routes, then portal reads where policy is approved.
9. **Test and QA:** run authorization, payment, ownership, history, and regression suites before each enforcement phase.

## 20. Recommended Implementation Order

```text
Phase 1  Freeze schema/data changes and add audit/compatibility queries
Phase 2  Define membership access policy and lifecycle semantics
Phase 3  Add active-member middleware plus recovery/history exceptions
Phase 4  Protect booking, vehicle, Trade-In action, and other new-service routes
Phase 5  Revise registration/login/payment recovery state handling
Phase 6  Convert guest service CTAs to centralized WhatsApp fallbacks
Phase 7  Make Member Center the active-member home and preserve activation recovery
Phase 8  Update/add tests and run full regression suite
Phase 9  Controlled data migration/backfill only if required by approved policy
Phase 10 Final security, payment, route, and UI QA
```

## 21. Files That Will Need Modification

Likely files, subject to implementation design:

- `routes/web.php` — classify routes and apply active-member/recovery middleware.
- `app/Http/Middleware/RoleMiddleware.php` or a new membership middleware — backend authorization boundary.
- `app/Services/MemberIdentityService.php` — centralize/standardize active and history checks.
- `app/Http/Controllers/AuthController.php` — login behavior for pending/cancelled/legacy identities.
- `app/Http/Controllers/RegistrationController.php` and `app/Http/Requests/RegisterRequest.php` — provisional/active registration contract.
- `app/Http/Controllers/MembershipController.php` — recovery/activation status handling.
- `app/Services/MembershipPaymentService.php` and `app/Services/MidtransPaymentService.php` — preserve idempotency while aligning account activation state.
- `app/Http/Controllers/DashboardController.php` and `app/Services/MemberDashboardService.php` — active-member center behavior.
- All `app/Http/Controllers/Pelanggan/*` service/action controllers — route-level active-member enforcement and history exceptions.
- `app/Models/User.php`, `Pelanggan.php`, `Membership.php` — only if the approved lifecycle requires new relationships/scopes.
- `resources/views/components/public-navbar.blade.php`, `public-footer.blade.php`, public/home/detail views — CTA and WhatsApp fallback routing.
- `config/services.php`, `config/yza.php`, `app/Services/PengaturanService.php` — centralized WhatsApp/source-of-truth settings if needed.
- `tests/Feature/*` membership, identity, booking, Trade-In, payment, landing, and final QA tests — new policy coverage and updated registration fixtures.

## 22. Database Changes Required

**Required: TO BE CONFIRMED after policy design.** No migration should be created during the audit.

The current schema can support a staged policy using existing `users.status`, `pelanggan.status`, and `memberships.status`, but it does not model expiry/renewal or a separate provisional registration. A database migration is likely required if the approved design needs:

- provisional registrations/payment intents independent of login accounts;
- membership `starts_at`/`expires_at` and renewal records;
- explicit historical-only/customer compatibility state;
- audit fields for activation/revocation reason.

Any migration must be additive/backward-compatible, preserve all foreign keys, and avoid deleting or overwriting native/reference data.

## 23. Test Plan

Add or update tests for:

1. Guest can view catalog/detail and is sent to WhatsApp for each service intent.
2. Registration creates provisional state and does not grant member-service access before settlement.
3. Pending customer can securely resume payment but cannot access bookings, vehicles, Trade-In actions, or portal services.
4. Valid settlement/capture activates membership and the account entitlement exactly once.
5. Invalid signature, unknown order, amount mismatch, duplicate webhook, deny, cancel, and expire remain safe.
6. Active member can access all approved digital services.
7. Cancelled/expired member cannot create new services.
8. Historical member can access only approved history views, if that policy is chosen.
9. Legacy customer without user/membership is preserved and cannot bypass the new service policy.
10. Every customer endpoint rejects another customer's record (IDOR matrix).
11. Admin/petugas operations remain available and isolated.
12. Registration fixtures include the current required fields and membership confirmation.
13. WhatsApp number/message helper uses configuration and escapes vehicle/service context safely.

## 24. Final Recommendation

`Account = Active YZA Member` is **architecturally viable but not safe to enforce immediately on the current codebase**.

The recommended decision is **SAFE WITH MIGRATION**: retain existing customer identities and history, introduce a centralized active-member authorization policy, preserve a narrowly scoped payment-recovery path, then progressively enforce member-only access. Do not equate the current `role:pelanggan` middleware with active membership, and do not delete or detach non-active historical customer data.

```text
YZA MEMBERSHIP ACCESS MODEL AUDIT
================================

Current Model:
Customer account is created/activated before membership payment; login is user-status based; feature gates are inconsistent.

Target Model:
Guest = Public/WhatsApp
Account = Active Member

Audit Status:
HIGH RISK for immediate enforcement / SAFE WITH MIGRATION for staged rollout

Critical Findings:
1. Registration logs in a pending-payment customer.
2. Login does not check active membership.
3. Many customer routes have role/ownership checks but no active-member gate.

Routes Requiring Change:
Customer booking, vehicle, Trade-In action, submission, service, history, promo, invoice, and dashboard groups.

Database Migration Required:
TO BE CONFIRMED; likely additive only if provisional/expiry/history states are introduced.

Existing Data Migration:
Preserve all users, customers, memberships, bookings, transactions, vehicles, services, and Trade-In records; classify legacy records before enforcement.

Security Risk:
HIGH for immediate policy switch; MEDIUM after centralized middleware and route coverage.

Regression Risk:
HIGH during transition; manageable with staged rollout and compatibility tests.

Test Result:
282 passed, 3 failed, 0 skipped reported; failures are stale registration fixture expectations.

Report:
docs/audit/MEMBERSHIP_ACCESS_MODEL_AUDIT.md

IMPLEMENTATION:
NOT STARTED
AUDIT ONLY
```
