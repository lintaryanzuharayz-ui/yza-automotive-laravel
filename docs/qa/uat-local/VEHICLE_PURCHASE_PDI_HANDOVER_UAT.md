# Vehicle Purchase UAT — PDI to My Garage

Tanggal: 15 Oktober 2026  
Environment: UAT `http://127.0.0.1:8001`  
Database: `yza_automotive_laravel_uat`

## Hasil ringkas

| Tahap | Hasil | Bukti |
|---|---|---|
| Pembayaran Midtrans Sandbox | PASS | Provider `settlement`; transaksi YZA `lunas`; satu payment terverifikasi; nominal Rp2.350.000.000 |
| PDI melalui UI admin | PASS | PDI dimulai dan diselesaikan melalui detail pesanan; hasil `lulus/passed`; seluruh 36 item checklist diisi PASS |
| Pickup | PASS | Jadwal 16 Oktober 2026 10:00, YZA Automotive Showroom tersimpan melalui UI |
| Handover | PASS | Serah-terima diselesaikan melalui UI; status pesanan `diserahkan` |
| My Garage member | PASS | Member Center menampilkan Toyota GR Supra 2024, Supra Red, `Verified Ownership`; detail kendaraan #2 dapat dibuka |
| Owner Dashboard | PASS | Dashboard menampilkan 2 penjualan final; rekap memuat transaksi UAT Rp2.350.000.000, pembayaran terverifikasi, serah-terima selesai |
| Sales attribution | PASS | Rekap menampilkan `Digital / belum ditugaskan`; `id_sales` pesanan UAT tetap NULL |

## Verifikasi database UAT (read-only)

- Order `ORD-202610-B8358FB0`: `diserahkan`, `status_pengambilan=diserahkan`.
- Tepat satu ownership `kendaraan_pelanggan` untuk order tersebut; kendaraan milik member yang login.
- Tepat satu payment terverifikasi sebesar Rp2.350.000.000.
- Dua gateway attempts tetap tercatat: satu `failure` lama dan satu `settlement`; tidak ada attempt aktif tambahan.
- Tepat satu row PDI dengan `status=lulus`, `result=passed`.
- Inventory aggregate berubah melalui history resmi: `sold_units` 0 → 1, `stock_total` tetap 5; tidak ada mutasi manual SQL.
- `mobil.status=tersedia` adalah status aggregate katalog; unit terjual dicatat pada `sold_units` dan history inventory.

## Temuan dan perbaikan

Backend PDI, pickup, dan handover sudah tersedia, tetapi detail pesanan admin belum mengekspos kontrol workflow tersebut pada UI. Akibatnya UAT browser berhenti walaupun route/service resmi ada. Perbaikan hanya menambahkan panel workflow pada modal detail yang memakai route dan service existing: mulai PDI, checklist, jadwal pickup, dan serah-terima. Tidak ada perubahan aturan pembayaran, ownership, stok, atau database.

File yang disentuh untuk perbaikan ini:

- `app/Http/Controllers/Admin/MemberVehiclePurchaseController.php`
- `resources/views/admin/pesanan-mobil/index.blade.php`

## Regression setelah perubahan

- Focused: **22 PASS, 0 FAIL, 85 assertions** (`MemberVehiclePurchaseFlowTest`, `MidtransSandboxStage11ATest`).
- Full suite: **407 PASS, 0 FAIL, 0 SKIPPED, 1.488 assertions**.
- `git diff --check` pada file perbaikan: **PASS**.

## Batasan bukti

- Screenshot browser tersedia sebagai capture inline pada sesi UAT ini; tidak ada file screenshot baru yang dibuat atau diklaim tersimpan.
- Isolasi terhadap akun member lain tidak dimutasi dan tidak diuji dengan akun kedua pada sesi ini; automated regression tetap mencakup ownership scoping/IDOR.
- Dashboard merchant Midtrans tidak dibuka; settlement sudah dikonfirmasi melalui status inquiry server-to-server dan event gateway UAT.
