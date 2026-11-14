# YZA global notification badge synchronization

## Audit before implementation — 8 November 2026

Root causes confirmed in source:

| Existing badge | Actual meaning |
|---|---|
| Notification center / bell | Recipient's unread `notifikasi` records |
| Pesanan Mobil | Orders in `menunggu_verifikasi` |
| Pre-Order | Preorders in `menunggu_stok` |
| Payments | Incoming offline payments in `menunggu`, plus gateway `requires_review` |
| Booking Mobil / Test Drive | Bookings in `menunggu` |
| Trade-In | Trade-ins in `proses` |
| Inventori & Stok Mobil (incorrectly placed) | Active **spareparts** with `stok <= stok_minimum`, not cars or unread messages |
| Other sidebar links | No numeric badge currently defined |

The sidebar labels every number as notifications despite these different meanings. Polling updates the bell but not domain badges. Member navbar has no unread-category mapping. Member and admin sync duplicate counting logic. Polling deltas omit already-existing records marked read in another tab. Member's separate order-state timer can reload the entire page. Order creation does not currently generate a corresponding staff notification. Existing member order/status, service/approval/QC, admin booking, stock, and preorder-availability notification producers use the existing `notifikasi` table. No priority or read timestamp column exists (`is_read` is authoritative).

The definitions above describe the audited starting point. Implementation and verification follow. No operational database changes or browser-security bypass were performed.

The prior documented full run in this chat was 594 passed / 1 known failure. This checkout now contains additional local tests. A fresh pre-change full-suite baseline was not recorded in this turn; the full regression figures below are the actual post-change runs, not the historical 583-test baseline.

## Authoritative state and implementation

`NotificationStateService` is the shared source for authenticated notification queries, summary counts, category/menu mappings, and sync snapshots. Both existing sync controllers use it; the shared browser client uses the existing universal `notifications.sync` route, avoiding additional dashboard permissions for legacy staff. Existing admin routes remain compatible and retain their middleware.

The state includes authenticated account ID, unique eligible unread count, exclusive category counts, menu unread counts, separately named pending counts, total history count, latest ID, drained cursor, recent items, recent unread items, and read states for up to 40 currently displayed cards. The polling snapshot runs in a database transaction. Queries use existing recipient/read and reference indexes; no schema changes were needed. The existing schema uses `is_read`; no read timestamp or priority was fabricated.

Unread aggregation groups existing recipient-owned rows by reference/type/title and adds each row exactly once. The sum of categories, including `other`, equals the global eligible unread count. Mechanic service and assignment messages additionally require an active primary/assistant ledger entry with a non-future assignment time. Revoked/inaccessible messages remain in storage but cannot be delivered, opened, or marked read through these endpoints.

Pending counts retain their domain status definitions. Finance, stock, booking, and Sales queries are permission-gated; Sales orders are scoped to the assigned salesperson. Owner receives no pending operational action counts. The sparepart minimum-stock badge was moved from the car inventory link to **Sparepart & Inventori**, with an accurate label. Other sidebar menus without a defined count were not given arbitrary numbers. Numeric task counts and small unread dots are separate, with accessible labels explaining their meanings.

## Exclusive navigation mapping

| Notification reference/event | Member primary menu | Staff primary destination/indicator |
|---|---|---|
| Member vehicle order and payment/PDI status stored against that order | Pesanan Saya (`orders`) | Admin orders / assigned Sales orders |
| Order event `Serah terima selesai` | Kendaraan Saya (`garage`) | Order detail; no extra event record for another category |
| Preorder availability | Beli Mobil (`buy`) | Pre-Order |
| Garage-specific reference | Kendaraan Saya (`garage`) | No invented staff menu |
| Service, service booking, active service assignment, car wash, test drive, trade-in | Layanan Otomotif (`services`) | Existing relevant service/booking link |
| Showroom booking | Aktivitas Saya (`activity`) | Booking Mobil |
| Promo, points, membership references, if actual records exist | Benefit Member (`benefits`) | No automatic report or benefit events invented |
| Sparepart / stock receipt | No unrelated member dot | Sparepart & Inventori (`inventory`) |
| Payment / transaction staff reference | No unrelated member menu dot (`finance`) | Authorized payments link |
| Unknown reference | Notification Center (`other`), no menu spam | Notification Center |

The shared member navbar renders the same category markers in desktop labels and the mobile drawer. Dots are 7px, positioned within reserved label space, with unread accessibility text. They reflect unread records rather than order history, existing vehicles, pending tasks, or old read promotions. One reference receives one primary category.

## Client synchronization and read lifecycle

`notification-sync.js` replaces the two old inline notification polling loops. Its single sequential loop normally polls every 10 seconds, backs off on connection errors, pauses scheduling in hidden tabs, aborts/clears on page exit, and stops on 401/403/419 or account mismatch. Logout clears notification presentation; BroadcastChannel sends account-scoped invalidations, not notification contents. A bounded account-scoped list of recent toast IDs provides best-effort duplicate suppression across tabs.

The server-rendered cursor is the baseline, so old history is not replayed as incoming toasts. Incremental batches drain in ascending ID order, at most 20 per request, and advance only to the last delivered ID. Large bursts therefore cannot be skipped by advancing directly to the global maximum. Read changes to already-existing records are returned separately; visible center cards update even when no new message ID exists. The panel receives authoritative recent/all-unread lists instead of merging a stale read flag forever.

Read forms use the existing CSRF-protected routes and support JSON responses. A mark request aborts an overlapping poll; older snapshot responses are ignored. Counts/dots follow the returned server state without manual decrements. Closing a toast sends no read request. Navigation still uses the existing open/read lifecycle. The old member order and admin payment HTML-reload timers were removed from the shared layout so notification synchronization does not discard drafts. This does not change payment callbacks, amounts, approval rules, or business statuses; domain tables themselves are refreshed through ordinary navigation rather than whole-page timer reloads.

`NotificationDestinationService` resolves safe existing routes. Missing resources fall back to the appropriate personal notification center; owner stays on a read-only notification surface. Member resource ownership and mechanic Work Order authorization are checked; receiving a notification grants no new permission. Financial payloads introduced here contain only a generic verification notice, never full financial details. Sync responses use `Cache-Control: no-store, private`.

## Actual committed event wiring

`StaffNotificationService` observes existing Eloquent records and schedules delivery with `DB::afterCommit`. It rechecks the persisted record, recipient role/permissions, and assignment/state, then creates one canonical recipient/reference/title record under a source-row lock. Reprocessing the same event does not add another record. If the business transaction rolls back, its delivery callback is discarded. Delivery failures are reported without changing the underlying business operation.

| Real source | Recipients added by this change | Constraints |
|---|---|---|
| New member vehicle order; assigned Sales change | Admin/Super Admin and assigned authorized Sales | No customer contact details copied; owner excluded |
| New active primary/assistant ledger entry | Assigned active Mechanic | Active assignment time/role checked again before sending |
| New service booking | Service Advisor and modern Super Admin | Legacy admin producer retained, not duplicated |
| New showroom/test-drive booking | Authorized Operational Staff, including the legacy alias | Actual committed booking record required |
| Incoming manual payment in `menunggu` | Finance and authorized Admin/Super Admin | Explicitly pending verification, not funds received |
| Existing low-sparepart admin alert | Warehouse Staff | Same real stock alert; one active unread alert per recipient/reference/title |
| Actual QC record entering `in_progress` during creation workflow | Authorized QC staff | No PDI/handover powers or workflow changes |

Existing member order status, payment outcomes against orders, booking decisions, service progress/approval/QC completion, stock, and preorder availability producers remain intact. Their database notification writes are atomic with their existing transactions and cannot be observed by polling before commit. No new fake events were created to populate empty menus, and historical pending tasks were not backfilled into operational notification records.

## Role verification

“PASS” here is automated endpoint, isolation, or producer evidence, not live browser UAT. Twelve distinct active role names were exercised, including legacy `admin` and `petugas` aliases.

| Role | Bell Count | Menu Badge | Notification Center | Click Navigation | Status |
|---|---|---|---|---|---|
| Super Admin / legacy Admin | Recipient count PASS | Separate pending/unread; source mappings tested | Render PASS | Actual order/booking routing PASS | Automated PASS |
| Owner | Recipient count PASS; no operational pending counts | No mutating task badge added | Owner shell render PASS | Read-only fallback; no new powers | Automated PASS; report event source gap below |
| Sales | Recipient count PASS | Assigned orders only | Render PASS | Assigned-order destination guarded | Assigned-order producer PASS |
| Service Advisor | Recipient count PASS | Service-booking indicator | Render PASS | Existing service authorization retained | Booking producer PASS |
| Mechanic | Eligible recipient count PASS | Active assignment indicator | Render PASS | Future/revoked notification open/read denied | Assignment producer and isolation PASS |
| Warehouse Staff | Recipient count PASS | Sparepart minimum-stock count separate from unread | Render PASS | Existing inventory permission retained | Actual stock-adjustment alert PASS |
| Finance | Recipient count PASS | Pending verification separate from unread | Render PASS | Payments destination requires finance permission | Pending-payment producer PASS |
| Operational Staff / legacy Petugas | Recipient count PASS | Booking pending count and unread dot | Render PASS | Existing booking routes/permissions | Booking producer PASS |
| Quality Control | Recipient count PASS | Service/QC unread indicator | Render PASS | Existing QC/service authorization retained | QC-start producer PASS |
| Member | Recipient count PASS | Six exclusive desktop/drawer category hooks | Render/read-all PASS | Foreign notification ownership denied | Mapping/read/ownership PASS |

## Tests and evidence

- Focused notification/domain/security/navigation checks: **49 passed / 698 assertions**, `docs/qa/notification-sync-focused.txt`.
- Final all-role center rendering/navigation checks after shared-layout cleanup: `docs/qa/notification-sync-final-render.txt` (23 passed / 254 assertions at that run).
- JavaScript behavioral tests: **7 passed**, `tests/Ui/notification-sync.test.cjs`, results in `docs/qa/notification-sync-client-tests.txt`. These exercise independent unread/pending values, partial reads, stale responses, sequential polling/cursor handling, duplicate toasts, visibility/auth failure, account changes, JSON read updates, and logout. They use a DOM/network harness and are **not** browser screenshots or visual PASS evidence.
- Full Laravel regression on `yza_automotive_laravel_test`: **616 passed, 1 failed / 3035 assertions**, 106.34 seconds, saved in `docs/qa/notification-sync-regression.txt`.
- The remaining failure is the pre-existing `MemberVehiclePurchaseFlowTest` assertion for “Informasi Pembeli”; the locally redesigned view says “Data Pembeli”. It was already documented before this task. Neither that order detail nor its expected label was changed here.
- One existing notification routing test now identifies its expected booking message by **recipient and reference type**, rather than mistakenly selecting another role's highest notification ID. Its intended deep-link and member-denial assertions remain.
- Frontend production build passed. Changed PHP/JavaScript syntax checks passed. `git diff --check` passed, with existing line-ending warnings in unrelated local files.
- No operational database writes, schema migrations, account/password changes, Git reset/clean, commit, push, or deployment were performed.

## Browser scope and remaining gaps

Direct Windows browser inspection was previously stopped because the tool could not establish the active URL with sufficient confidence. That restriction was not retried through another native-control path or bypassed. No new visual PASS, live two-account browser UAT, or screenshots are claimed for this synchronization task. Desktop/mobile marker placement is implemented in the shared navbar and rendered in feature tests; actual visual review and live focus/hover behavior remain manual UAT.

No stored owner-report publication event was found, so no fictional owner report/performance alert was added. A schema-level read timestamp/priority, durable notification outbox, gateway-review event producer, sparepart-request event producer, and every possible service/QC-failure/promo/points event are not introduced by this change. Existing records of supported references synchronize correctly; absent event producers remain gaps. Cross-tab toast suppression is best effort, not a distributed exactly-once guarantee. Histories remain stored and no task badge is converted into unread merely to make numbers equal.

## Changed files for this task

Services: `NotificationStateService`, `NotificationDestinationService`, `StaffNotificationService`, `AdminNotificationBadgeService`; provider observer registration; both notification controllers; shared layout; sidebar, member navbar, owner shell, panel, sync, and dot components; both center views; notification panel/toast/sync JavaScript; shared notification CSS; focused PHP and JavaScript tests; this report and test logs.

## YZA GLOBAL NOTIFICATION SYNC — FINAL RESULT

- Root cause: mixed badge meanings, sidebar not polled, missing category mapping and committed staff events.
- Global bell count: authoritative, unique eligible recipient unread records.
- Menu unread mapping: exclusive primary categories; unknown events stay in the center.
- Pending task badge: domain counts retained, permission-scoped, labeled separately.
- Admin synchronization: shared snapshot and browser loop.
- Owner synchronization: personal unread state, no operational mutation rights added.
- Member navbar red dot: shared desktop and drawer hooks driven by server categories.
- Multi-role isolation: all active roles tested; no cross-recipient payload mixing.
- Read/unread sync: JSON read forms, visible card state, badge/category refresh.
- Realtime/polling sync: existing authenticated HTTP polling, normally 10 seconds; not WebSockets.
- Duplicate prevention: canonical recipient/event records, source locks, cursor draining, and best-effort cross-tab toast IDs.
- Notification routing: existing backend routes, ownership/permission/assignment checks and safe missing-resource fallback.
- Focused tests: 49 PASS / 698 assertions; client harness 7 PASS.
- Full regression: 616 PASS / 1 known unrelated failure.
- Database operasional dimodifikasi: **Tidak**.
- Remaining blockers: direct browser UAT; known unrelated label test; event producer gaps listed above.
- Final status: functional synchronization implemented and automatically verified; no unverified visual or all-event completion claim.
