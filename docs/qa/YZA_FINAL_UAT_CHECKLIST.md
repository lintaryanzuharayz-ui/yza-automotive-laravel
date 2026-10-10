# YZA Final UAT Checklist

| ID | Module | Precondition | Steps | Expected Result | Status |
|---|---|---|---|---|---|
| UAT-01 | Registration | Guest | Register account | Membership is pending_payment | PASS automated |
| UAT-02 | Membership | Pending member | Open activation and pay sandbox fee | Valid payment changes status to active; no Points | PASS automated |
| UAT-03 | Membership | Existing gateway attempt | Replay webhook | Idempotent; no duplicate payment/activation | PASS automated |
| UAT-04 | Promo preview | Authenticated customer | Select valid promo | Server returns discount and final total | PASS automated |
| UAT-05 | Promo scope | Active promo | Use promo in wrong domain | Rejected by engine | PASS automated |
| UAT-06 | Promo integrity | Fixed discount greater than subtotal | Preview/apply | Discount is clamped; final is never negative | PASS Stage14 test |
| UAT-07 | Service | Service with items | Apply promo, pay, finalize | Invoice, transaction, redemption and Points agree | PASS automated |
| UAT-08 | Car wash | Booking in process | Finalize with promo | Final total matches transaction total | PASS automated |
| UAT-09 | Sparepart | Draft with stock | Preview then finalize | Preview does not mutate stock; finalization does | PASS automated |
| UAT-10 | Car sale | Available unit | Apply capped promo and pay | Total uses cap; unit becomes sold after completion | PASS automated |
| UAT-11 | Trade-in | Valid offer and dealer car | Apply promo and finalize | Promo affects customer payable only | PASS automated |
| UAT-12 | Invoice | Transaction with and without promo | Open invoice | Snapshot is shown; no-promo history still renders | PASS automated |
| UAT-13 | SPK | Admin | Save simulation and create promo | Associated promo starts nonaktif | PASS Stage14 test |
| UAT-14 | BERISIKO | Risky promo | Activate without/with confirmation | First rejected; second audited and allowed | PASS automated |
| UAT-15 | Authorization | Admin/petugas/customer accounts | Open another role’s routes | 403 or guest redirect | PASS automated |
| UAT-16 | UI smoke | Local server | Open landing, collection, login, admin URL | Pages render; admin redirects to login | PASS browser smoke |
| UAT-17 | Responsive | Local browser | Check supported viewport set | Manual viewport matrix remains to be performed | WARNING |
| UAT-18 | Concurrency | Two simultaneous clients | Consume quota/usage=1 | Only one completion allowed | WARNING: service locking covered; live two-client UAT not run |
