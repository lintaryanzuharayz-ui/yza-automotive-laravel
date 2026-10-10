# Final UAT — YZA Dealer Management System Laravel RC1

| ID | Skenario | Aktor | Expected | Status |
|---|---|---|---|---|
| UAT-01 | Login dashboard | Admin | Masuk ke dashboard admin | PASS |
| UAT-02 | Login operasional | Petugas | Masuk ke dashboard petugas | PASS |
| UAT-03 | Registrasi member | Guest | User, pelanggan, dan account Points atomik | PASS |
| UAT-04 | Kendaraan pelanggan | Member | Hanya kendaraan sendiri | PASS |
| UAT-05 | Booking/test drive | Member/Petugas | Booking dan proses sesuai role | PASS |
| UAT-06 | Penjualan mobil | Admin/Petugas | Unit dan transaksi terkunci | PASS |
| UAT-07 | Pembayaran mobil | Admin/Petugas | DP/lunas dan unit terjual | PASS |
| UAT-08 | Booking servis | Member/Petugas | Booking menjadi Work Order | PASS |
| UAT-09 | Work Order | Petugas | Pemeriksaan, jasa, spare part draft | PASS |
| UAT-10 | Finalisasi servis | Petugas | Stok, transaksi, history atomik | PASS |
| UAT-11 | Pembayaran servis | Admin/Petugas | Manual payment dan invoice | PASS |
| UAT-12 | Car Wash | Member/Petugas | Booking sampai settlement | PASS |
| UAT-13 | Penjualan spare part | Admin/Petugas | Inventory dan transaksi atomik | PASS |
| UAT-14 | Jual mobil bekas | Member | Pengajuan dan ownership aman | PASS |
| UAT-15 | Inspeksi/offer | Petugas | Tahap inspeksi dan penawaran | PASS |
| UAT-16 | Pembelian mobil bekas | Admin/Petugas | Outgoing transaction | PASS |
| UAT-17 | Outgoing payment | Admin/Petugas | Direction keluar, tanpa Points | PASS |
| UAT-18 | Trade-In | Member/Petugas | Finalisasi eksplisit dan Points once | PASS |
| UAT-19 | Member Center | Member | Ringkasan aktivitas dan ownership | PASS |
| UAT-20 | YZA Points | Member | Ledger, saldo, idempotency | PASS |
| UAT-21 | Promo | Admin/Member | Catalog-only, tanpa redemption | PASS |
| UAT-22 | Service Reminder | Member | Reminder read-only dan owner-scoped | PASS |
| UAT-23 | Midtrans | Member/Webhook | Fake Sandbox Snap, signature, bridge | PASS |
| UAT-24 | Invoice/receipt | Owner/Admin/Petugas | Snapshot dan authorization | PASS |
| UAT-25 | Security/IDOR | Semua role | Role, CSRF, ownership, overpayment blocked | PASS |

Live Midtrans Sandbox smoke test: SKIPPED karena credential/live fixture tidak tersedia. Automated HTTP fake: PASS.
