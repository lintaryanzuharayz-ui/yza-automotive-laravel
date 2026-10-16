# Midtrans Sandbox Settlement Reconciliation

Tanggal: 15 Oktober 2026

## Hasil

- Order YZA: `ORD-202610-B8358FB0`
- Order ID Midtrans yang dipakai inquiry: **cocok** — `YZA-3-DKBAKAO40M`
- Merchant ID: **terbaca dari respons provider dan konsisten dengan transaksi**; nilainya tidak dicatat di laporan.
- Payment type: `bank_transfer`.
- Nominal provider: `2350000000.00`.
- Transaction ID provider: **tersedia dan tersimpan**, tidak berupa token atau credential.
- Status provider: **settlement**.
- Status inquiry: HTTP transport berhasil, JSON `status_code=200`.
- JSON `status_message`: `Success, transaction is found`.
- Snap token: tidak dicatat.

## Rekonsiliasi attempt

Database UAT menunjukkan:

- Total payment attempt: **2**.
- Attempt lama: `failure`, tanpa token/redirect.
- Attempt aktif: **0**.
- Attempt settled: **1**.
- Gateway payment yang terhubung: **1**.
- Payment YZA: **1**, status `terverifikasi`.
- Total payment terverifikasi: `Rp2.350.000.000`.
- Status transaksi YZA: `lunas`.
- Event gateway settlement: **1** dengan status code provider `200`.

Tidak ada pembayaran ganda dan tidak ada perubahan harga atau status melalui SQL. Pemeriksaan database bersifat read-only.

## Penyebab ketidaksesuaian sebelumnya

Pemeriksaan segera setelah simulator menampilkan sukses memperoleh respons status provider dengan JSON `status_code=404` dan tanpa `transaction_status`. Setelah callback/provider processing selesai, event settlement masuk ke UAT pada `20:24:29`. Inquiry berikutnya menggunakan Order ID Midtrans yang sama memperoleh `status_code=200` dan `transaction_status=settlement`.

Kesimpulannya, ketidaksesuaian disebabkan oleh **keterlambatan sinkronisasi/status propagation Sandbox**, bukan bug pemetaan identifier. Order ID internal YZA dan Order ID Midtrans memang berbeda, tetapi aplikasi menggunakan Order ID Midtrans yang benar untuk status inquiry.

## Validasi identifier

- Identifier Snap dan GET Status: **cocok**.
- Prefix/encoding order ID: **cocok**, tidak ada karakter yang hilang.
- Server-to-server authentication: **berhasil**; Client Key tidak digunakan untuk inquiry backend.
- Transaction ID provider pada event settlement dan record gateway: **cocok**.
- Merchant ID: **terbaca**, tetapi dashboard merchant tidak diperiksa melalui browser Codex pada sesi ini; status dashboard: **BLOCKED / manual verification recommended**.
- VA: berhasil di-Inquire oleh simulator dan nominal cocok; nomor lengkap tidak dicatat di laporan.

## Source dan pengujian

Tidak ada perubahan source code yang diperlukan. Mekanisme existing berhasil melakukan idempotent settlement processing dan mencatat satu payment terverifikasi.

Tidak ada regression suite baru dijalankan karena tidak ada perubahan source. Baseline tetap **407 PASS, 0 FAIL, 0 SKIPPED, 1.488 assertions**.

## Status akhir

- Pembayaran provider benar-benar terverifikasi: **YES**.
- PDI: **BELUM DIJALANKAN**.
- Pickup: **BELUM DIJALANKAN**.
- Handover: **BELUM DIJALANKAN**.
- My Garage/ownership: **BELUM DIJALANKAN**.
- Blocker tersisa: verifikasi dashboard merchant manual bila diperlukan; tidak ada blocker pada settlement server-to-server UAT.
