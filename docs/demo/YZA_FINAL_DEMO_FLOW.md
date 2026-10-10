# YZA Automotive — Final Demo Flow

## Prerequisites

Run C:\xampp\php\php.exe artisan yza:stage14-demo in the Laravel project. The command is idempotent and creates only Laravel demo data.

## Sequence

1. Open the landing page and inspect the premium vehicle carousel.
2. Register or use the demo pending member, then open Membership Activation.
3. Complete the sandbox membership payment and confirm the member becomes active.
4. Open Member Center and Promo Member; verify active promo visibility.
5. Create a service/car-wash/sparepart transaction and select a promo. Confirm the server preview and final amount.
6. Complete payment, open the invoice, and confirm the after-promo total and Points ledger.
7. Open Admin Membership and review active/pending states.
8. Open Admin > Simulasi Promo SPK, create and save a simulation.
9. Open the saved simulation and choose “Buat Draft Promo Nonaktif”.
10. Review the prefilled promo, then activate it. For BERISIKO, confirm the explicit override and audit event.
11. Open Promo Analytics and compare projection versus actual data.

## Demo identities

The idempotent demo command creates demo.active and demo.pending accounts with the password DemoStage14! for local demo use only. Do not reuse these credentials outside the local demo environment.

## Known boundaries

Midtrans remains Sandbox-only. Promo stacking, Points redemption, membership tiers, recurring membership, and automated refunds are intentionally out of scope.
