# Membership Access Model Implementation

Implementation date: 2026-10-07 (Asia/Jakarta)

Scope: staged membership access enforcement based on `docs/audit/MEMBERSHIP_ACCESS_MODEL_AUDIT.md`.

## 1. Executive Summary

The first safe migration phase is implemented without schema changes or destructive data operations. Customer portal routes now use a centralized active-membership middleware. Pending customers remain authenticated where necessary for payment recovery, but are redirected away from member services. Midtrans flow remains server-verified and existing customer/history records are preserved.

## 2. Previous Model

Registration created an active user/customer, created a pending membership, logged the user in, and redirected to activation. Login accepted any active customer user regardless of membership status. Most customer routes used only `auth` and `role:pelanggan`.

## 3. New Access Model

```text
Guest
→ Public website / catalog / vehicle detail
→ WhatsApp consultation or Jadi Member

Authenticated pending/cancelled customer
→ Membership/payment recovery only
→ No member portal or new service actions

Active membership
→ Member dashboard and protected customer services
```

The current database status source of truth is unchanged: only `memberships.status = active` grants member access. `pending_payment` and `cancelled` do not grant member access.

## 4. Authentication Behavior

`AuthController` still authenticates by active user status and password, preserving payment recovery. After successful authentication:

- Active customer membership → intended URL/dashboard.
- Pending/cancelled customer membership → membership activation/payment page.
- Customer user without a membership record → logout and a support error rather than portal access.
- Admin and petugas behavior remains role-based and unchanged.

Session regeneration and login throttling remain enabled.

## 5. Membership Authorization

`app/Services/MemberAccessService.php` is the source of truth. It currently evaluates the actual schema and returns active access only for:

```php
$user->roleName() === 'pelanggan'
$user->status === 'aktif'
$user->pelanggan->membership->status === 'active'
```

`app/Http/Middleware/EnsureActiveMembership.php` enforces this server-side. Membership activation/pay/sync/finish routes remain available as recovery routes; all other routes in the customer private group require active membership.

## 6. Registration Flow

The existing low-risk flow remains physically intact:

```text
create user/customer
→ create pending membership
→ authenticate/session
→ membership payment
→ server verification
→ active membership
```

The business access boundary is now enforced: pending membership does not grant portal access. No provisional table or migration was introduced.

## 7. Payment Flow

Midtrans behavior was preserved:

- pending/created gateway attempts are reused where valid;
- membership amount comes from `PengaturanService::membershipFee()`;
- settlement or valid capture activates membership server-side;
- notification signature and amount verification remain in place;
- gateway events remain idempotent;
- finish/status sync remains available;
- active memberships are not charged again by the membership attempt service.

## 8. Route Protection

Added middleware alias:

```text
member → App\Http\Middleware\EnsureActiveMembership
```

Protected customer areas include dashboard/member center, profile, vehicles, car booking, Test Drive, service booking, Car Wash, Trade-In, submissions, service/history, invoices, points, reminders, and member promos through the customer private route group. The final route inspection reports **57 routes** carrying the `member` middleware alias. Membership activation/payment recovery routes are explicitly exempted by route name.

Ownership checks in controllers remain in place; membership middleware does not replace IDOR/ownership validation.

## 9. Public/WhatsApp Flow

Added `yza_whatsapp_url()` in `app/Support/helpers.php`, loaded by `bootstrap/app.php`. It reads `no_hp_perusahaan` through `PengaturanService`, normalizes the number, and URL-encodes optional context messages.

Updated public footer and vehicle detail links to use the helper. Vehicle detail messages are vehicle-specific. The Trade-In public gate now offers both “Jadi YZA Member” and “Konsultasi via WhatsApp”. The landing appointment form continues to build a context-aware WhatsApp message using the centralized base URL.

## 10. Member Center States

- Active: member middleware allows dashboard/member center.
- Pending: activation page remains available; member routes redirect to activation.
- Cancelled: activation/status page remains available; member routes redirect to activation.
- Expired: no expiry field/status exists in the current schema, so no fabricated expiry logic was added. A future expiry/renewal design must extend the policy and tests before rollout.

## 11. Existing Data Compatibility

No users, customers, vehicles, bookings, services, transactions, Trade-In rows, memberships, or payments were deleted or updated by this implementation. No Native database was accessed for mutation. Existing legacy customer rows without a user or membership remain preserved; a customer without membership cannot receive new portal access through this policy.

## 12. Security Controls

- backend middleware protects direct URLs and POST/PUT/PATCH customer routes;
- pending customers cannot submit booking through protected routes;
- admin/petugas routes are not affected by the member middleware;
- existing ownership checks remain active;
- CSRF protection remains active;
- Midtrans webhook signature, amount, order, and idempotency checks remain active;
- password/session regeneration and login throttling remain active;
- no client-side membership state is trusted for authorization.

## 13. Files Modified

- `app/Services/MemberAccessService.php` — centralized active membership decision.
- `app/Http/Middleware/EnsureActiveMembership.php` — member portal gate and payment-recovery exception.
- `bootstrap/app.php` — middleware alias and helper loading.
- `routes/web.php` — member middleware applied to customer-private routes.
- `app/Http/Controllers/AuthController.php` — pending/cancelled login continuation behavior.
- `app/Support/helpers.php` — centralized WhatsApp URL helper.
- `resources/views/components/public-footer.blade.php` — helper-based WhatsApp links.
- `resources/views/components/public-navbar.blade.php` — guest/pending/member wording.
- `resources/views/components/trade-in-membership-modal.blade.php` — member or WhatsApp options.
- `resources/views/public/home.blade.php` — centralized appointment WhatsApp URL.
- `resources/views/mobil/show.blade.php` — context-aware vehicle WhatsApp links.
- `tests/Feature/IdentityStage3CTest.php` — current registration contract and pending access regression coverage.

## 14. Database Changes

None. No migration was created or executed. Existing membership statuses and foreign keys remain unchanged.

## 15. Test Changes

Updated identity test payloads to include the current required registration fields and expected pending activation redirect. Added a test proving pending customers can recover payment but cannot access dashboard or submit a booking.

## 16. QA Result

Targeted identity suite after implementation: 12 passed, 51 assertions.

Final full suite: **286 passed, 870 assertions, 0 failed**, after `optimize:clear`, Blade view cache compilation, and route inspection.

## 17. Known Limitations

- Membership expiry/renewal is not implemented because the current schema has no validity dates or expiry status.
- Some public service content is informational/appointment-based rather than a dedicated service CTA component; the existing appointment form continues to route to WhatsApp.
- Historical read-only versus active-member read access is currently conservative: customer-private routes require active membership, while staff/admin history access remains available.
- Existing inactive/legacy customer policy should be revisited when a formal history-only portal requirement is approved.

## 18. Rollback Notes

Rollback is code-only: remove the `member` alias/application, revert the new access service/middleware, restore login redirect behavior, and restore helper-based links if needed. No database rollback is required because no schema/data mutation was performed.
