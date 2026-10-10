# YZA Points + Point Ledger — Tahap 10B

## Status

Tahap 10B menambahkan YZA Points sebagai fitur Laravel baru. Database Native tetap menjadi reference read-only dan tidak dibuat schema parity untuk Points.

Tidak dibuat role member. Member tetap berarti role sistem pelanggan, user aktif, dan relasi pelanggan valid.

## Product Rules

Rate terpusat pada config/yza.php:

- Rp10.000 transaksi eligible = 1 YZA Point.
- Formula memakai pembagian integer terhadap nominal final transaksi.
- Rp9.999 = 0, Rp10.000 = 1, Rp99.999 = 9, dan Rp100.000 = 10.

Tidak ada tier, expiry, redemption, voucher, promo, reward, atau payment gateway.

## Eligible Transactions

Allowlist:

- servis
- cuci_mobil
- penjualan_sparepart
- penjualan_mobil
- trade_in

Syarat earning:

1. transaksi berstatus lunas;
2. pelanggan adalah YZA Member aktif;
3. domain terkait sudah completed;
4. nominal final berasal dari transaksi.total;
5. earning belum pernah tercatat.

Pembelian mobil bekas, transaksi outgoing, payout dealer, transaksi batal, pending, dan DP tidak eligible.

Completion predicate:

- servis: servis selesai;
- car wash: booking_cuci selesai;
- spare part: penjualan_sparepart selesai;
- penjualan mobil: penjualan_mobil selesai dan mobil terkait terjual;
- trade-in: trade_in selesai.

Trade-In sengaja tidak menghasilkan poin ketika hanya payment menjadi lunas. TradeInService memanggil award setelah finalisasi mengubah trade-in menjadi selesai.

## Schema

### poin_member

Menyimpan satu saldo berjalan per pelanggan:

- id_poin_member
- id_pelanggan unique dan foreign key
- saldo unsigned integer
- total_diperoleh unsigned integer
- timestamps

### riwayat_poin

Ledger audit immutable untuk setiap perubahan:

- id_riwayat_poin
- id_pelanggan foreign key
- id_transaksi nullable foreign key
- jenis perolehan atau pembalikan
- perubahan_poin unsigned integer
- saldo_setelah unsigned integer
- idempotency_key unique
- keterangan
- timestamps

Tidak ada update saldo tanpa ledger. Pada earning, account dikunci, idempotency diperiksa, ledger dibuat, lalu saldo dan lifetime total diperbarui dalam boundary transaction yang sama.

## Service and Integration

YzaPointService menjadi satu-satunya service award. Ia memeriksa status, allowlist, Member eligibility, completion predicate, nominal integer, idempotency, lock account, dan ledger.

PaymentService memanggil service setelah sync completion untuk servis, car wash, spare part, dan penjualan mobil. Trade-In dapat dipanggil dari payment ketika belum selesai dan akan skip; TradeInService memanggil ulang setelah finalisasi.

Nested service transaction mengikuti boundary transaction Laravel yang sudah digunakan oleh completion flow. Jika award gagal, completion dan payment berada dalam transaction yang sama sehingga rollback tetap konsisten.

Registrasi pelanggan membuat user, pelanggan, dan poin_member secara atomik. Legacy pelanggan tanpa user tidak otomatis menjadi member dan tidak mendapat backfill.

## Idempotency and Concurrency

Key earning berbentuk earn:transaction:{id}. Constraint unique, pengecekan server-side, lock transaksi, dan lock account mencegah double award, replay, dan concurrent award.

Backfill memanggil service yang sama sehingga aman diulang.

## Backfill

Command:

- yza:points-backfill --dry-run
- yza:points-backfill

Dry-run terakhir menemukan 2 active members, 6 transaksi eligible, estimasi 27.038 poin. Backfill aktual menghasilkan 27.038 poin. Eksekusi kedua menghasilkan 0 poin baru.

Backfill hanya bekerja pada database Laravel dan hanya menambah account/ledger Points; baris transaksi dan pembayaran historis tidak diubah.

## Member Center and History

Member Center menampilkan saldo, total diperoleh, rate, dan link Riwayat Poin. Halaman /pelanggan/poin menggunakan pagination dan menampilkan tanggal, aktivitas, kode transaksi, jenis transaksi, poin, dan saldo setelah.

Idempotency key, internal ID, serta data sensitif tidak diekspos. Ownership selalu diturunkan dari authenticated user ke pelanggan.

## Reversal Decision

Audit flow saat ini tidak menemukan public workflow refund atau reversal setelah transaksi lunas. Jenis pembalikan disiapkan pada schema, tetapi belum ada endpoint atau workflow reversal pada Tahap 10B.

## Deferred Scope

Deferred: redemption, pembayaran dengan poin, voucher, promo, tier, expiry, rewards, Payment Center baru, Midtrans, Snap, webhook, QRIS, dan VA.

## Verification

Test khusus Tahap 10B mencakup schema, rate, account uniqueness, backfill idempotency, outgoing exclusion, award idempotency, Member Center, history ownership, authorization, dan legacy non-member handling.

Regression wajib mencakup seluruh payment, trade-in, used-car payout, inventory, invoice, dan workflow sebelumnya.
