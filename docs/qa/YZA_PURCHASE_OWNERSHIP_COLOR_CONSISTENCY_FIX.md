# YZA Purchase → Ownership Color Consistency Fix

## UAT record

- Order: `ORD-202610-91F1E6DE`
- Member: Lintar Yan Zuhara
- Authoritative purchased color: `Obsidian Black`
- Source: `pesanan_mobil_member.snapshot_warna`
- Handover: `serah_terima_pesanan_mobil.id_serah_terima = 1`
- Ownership: `kendaraan_pelanggan.id_kendaraan_pelanggan = 24348`

## Root cause

`VerifiedCarOwnershipService::syncVehicleFromCompletedSale()` copied `mobil.warna` when creating or refreshing ownership. The master record for the GR Supra contains the showroom default `Supra Red`, while the order snapshot contains the member's purchased configuration `Obsidian Black`. My Garage also selected the default gallery color instead of the ownership/order color.

## Fix

- Ownership color now comes from the handed-over member order snapshot, with the master vehicle color used only as a legacy fallback when no purchase snapshot exists.
- Existing ownership refreshes update the color from the same authoritative source and preserve customer, sale, order, handover, VIN, engine, status, and mileage relations.
- Gallery resolution now has an exact `forColor()` path. Purchased/owned surfaces do not silently fall back to another color when a purchased color exists.
- My Garage, vehicle detail, service booking, service history, service approval, and purchase detail now resolve the selected color before using a default.
- Handover remains idempotent and Verified YZA Ownership remains linked through the existing sale/order/handover IDs.

## Historical correction

After verifying the order, customer, sale, and handover relations, ownership `24348` was corrected from `Supra Red` to `Obsidian Black`. The development database was backed up before correction at:

`storage/app/yza_automotive_laravel_before_ownership_color_fix_20261012.sql`

The order historical record was not changed. No migration or database reset was executed.

## Expected consistency

The order, My Garage, vehicle detail, service booking context, and service history context now all use `Obsidian Black` and its exact gallery variant. Future black and red purchases of the same model resolve independently from each order snapshot.

## Verification

- Development database source audit: PASS
- Existing ownership correction: PASS
- PHP syntax and Blade cache: PASS
- Regression coverage added for purchased-color ownership and exact gallery resolution.
- Database schema changed: NO
- Migration executed: NO
- New database created: NO
