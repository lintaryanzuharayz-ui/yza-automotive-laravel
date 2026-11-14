# YZA Sparepart Fitment Master & Work Order Filtering

## Scope

This implementation reuses the existing `sparepart_fitments` table and the existing Sparepart admin module. It does not create a new database, migration, or fitment table.

## Existing schema used

The existing `SparepartFitment` model uses `sparepart_fitments` with the existing fields for `id_sparepart`, vehicle brand/model, year range, variant, engine code, reference/source, notes, verification status, verifier, and verification timestamp. `Sparepart::fitments()` is the single model relation used by the UI and domain service.

## Compatibility semantics

`SparepartFitmentService::statusFor()` is the authoritative evaluator:

- `verified_fit` → `KOMPATIBEL TERVERIFIKASI`
- no matching verified fitment → `KOMPATIBILITAS BELUM TERVERIFIKASI`
- `verified_not_fit` → `TIDAK KOMPATIBEL`

An absent fitment row is never inferred as compatible from brand, model text, category, service type, part name, stock, or inventory availability.

For vehicles with a complete operational identity, new approval and Work Order sparepart usage is accepted only after a verified fitment is found; explicit incompatibility and unverified compatibility are rejected by the backend service. Existing legacy vehicles without an engine identity retain the prior compatibility exception so historical regression behavior is not broken; the UI still presents compatibility honestly.

## Admin fitment master

Admin manages fitments from **Sparepart & Inventori → Detail Sparepart → Kompatibilitas Kendaraan**. The existing form stores only fields supported by the current schema, records verification actor/time for verified states, and rejects equivalent duplicate fitments at service/controller level. The sparepart index also supports **Memiliki Fitment** and **Belum Memiliki Fitment** filters.

Fitment viewing and mutation remain admin-only. Member access is not broadened. Fitment deletion follows the existing application behavior; no historical approval, service item, stock, payment, or transaction record is rewritten.

## Work Order search-first flow

The Work Order stage no longer preloads the complete sparepart catalog into a large select. The page shows a compact vehicle compatibility context and a search picker. Search requests return only matching active spareparts, eager-load the requested rows' fitments, calculate the current vehicle state, and order verified matches before unverified and incompatible results.

The picker displays stock, server-side price, and one of the three semantic badges. Only verified-compatible items with positive stock expose **Pilih**. Unverified items show **Verifikasi Dulu** and incompatible items are disabled. Admin can open the existing fitment master from an unverified result.

The approval and pengerjaan forms still submit through their existing endpoints. Selecting a result only fills the original form field; it does not alter stock or price. Backend services re-check active status, compatibility, stock, and server pricing, so forged requests cannot bypass the UI rule.

## Safety guarantees

- No fitment operation changes stock.
- Search and draft selection do not change stock history.
- Existing approval decisions, snapshots, quantities, service history, payment, and finalization data are preserved.
- Pricing remains controlled by the existing server-side snapshot/master flow.
- No production fitment rows are backfilled or fabricated.

## Verification

Validated with Blade compilation, route inspection, focused fitment rendering, Work Order execution, approval, stock, and service-sparepart regression tests. Manual browser UAT for `/admin/servis/13822/persetujuan` remains recommended with the existing authenticated admin session.

