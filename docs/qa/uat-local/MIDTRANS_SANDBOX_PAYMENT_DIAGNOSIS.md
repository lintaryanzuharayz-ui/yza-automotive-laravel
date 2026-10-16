# Midtrans Sandbox Payment Diagnosis

Tanggal: 15 Oktober 2026

## Kesimpulan

Pembuatan pembayaran mencapai endpoint Snap Sandbox, tetapi ditolak provider dengan **HTTP 401 Unauthorized**. Kegagalan bukan berasal dari nominal kendaraan, order ID duplikat, validasi payload aplikasi, atau TLS pada Laravel.

Status tetap **BLOCKED — WAITING FOR VALID MIDTRANS SANDBOX CREDENTIALS**.

## Bukti alur

- Tombol browser memanggil route payment Midtrans yang sudah ada.
- Controller meneruskan ke `MidtransPaymentService::createAttempt()`.
- Service mengirim request melalui `MidtransClient`.
- Laravel log mencatat request UAT pada `15 Oktober 2026 19:20:18` dengan respons provider `401` / `Unauthorized`.
- Pesan pelanggan disanitasi menjadi `Midtrans Sandbox gagal membuat pembayaran.`
- Tidak ada Snap token atau redirect URL yang diterima.
- Tidak ada pembayaran yang menjadi lunas dan tidak ada status order yang diubah manual.

## Konfigurasi yang diperiksa

- UAT environment: `APP_ENV=uat`.
- UAT database: `yza_automotive_laravel_uat`.
- `MIDTRANS_IS_PRODUCTION=false`.
- Endpoint Snap: `https://app.sandbox.midtrans.com/snap/v1/transactions`.
- Endpoint status: `https://api.sandbox.midtrans.com/v2`.
- Server key dan client key UAT terisi, tetapi memakai prefix placeholder `uat-sandbox...` dan belum terbukti sebagai key yang diterbitkan Midtrans Sandbox.
- Tidak ada config cache Laravel (`bootstrap/cache/config.php`) yang aktif saat pemeriksaan.
- TLS verification tidak dinonaktifkan di `MidtransClient`.

Credential tidak dicetak atau disimpan di laporan.

## Payload dan order

- Order internal: `ORD-202610-B8358FB0`.
- Kendaraan: Toyota GR Supra 2024.
- Gross amount dihitung server-side sebagai integer `2350000000`.
- Customer details berasal dari member pemilik order.
- Payment attempt menggunakan order ID provider unik berbentuk `YZA-...`.
- Karena provider menolak autentikasi lebih dahulu, batas nominal atau konflik order ID belum dapat dinilai sebagai penyebab.

## Kode dan exception

Tidak ada perubahan source yang diperlukan setelah bukti HTTP 401 ditemukan. Kode existing sudah:

- memaksa endpoint Sandbox;
- menolak mode production;
- memakai Basic Auth dengan server key;
- menyimpan attempt sebagai failure;
- tidak membocorkan respons provider ke pelanggan;
- tidak melakukan retry berulang otomatis.

File yang diaudit:

- `app/Services/MidtransClient.php`
- `app/Services/MidtransPaymentService.php`
- `app/Http/Controllers/MidtransController.php`
- `config/midtrans.php`
- `routes/web.php`

Exception internal `RuntimeException` yang muncul ke controller adalah `Midtrans Sandbox gagal membuat pembayaran.`; detail HTTP provider hanya dicatat secara sanitasi pada log aplikasi.

## Validasi regression

- Focused Midtrans dan vehicle purchase tests: **22 PASS, 0 FAIL, 85 assertions**.
- Tidak ada full regression baru karena tidak ada source code yang diperbaiki pada diagnosis ini. Baseline terakhir tetap **407 PASS, 0 FAIL, 0 SKIPPED, 1.488 assertions**.

## Tindakan pemilik akun Midtrans

1. Masuk ke Midtrans Dashboard menggunakan akun Sandbox, bukan Production.
2. Ambil Server Key dan Client Key dari environment Sandbox yang sama.
3. Masukkan key tersebut hanya ke `.env.uat`; jangan mengubah `.env` operasional dan jangan memasukkan credential ke repository.
4. Pastikan `MIDTRANS_IS_PRODUCTION=false`.
5. Restart proses server UAT agar environment baru terbaca.
6. Ulangi satu kali create payment untuk order existing setelah memastikan attempt failure tidak memiliki token.
7. Keberhasilan baru diakui jika Snap token dan redirect URL diterima; token saja bukan bukti lunas.

## Status akhir

- Snap token: **BLOCKED / tidak dibuat**.
- Halaman pembayaran Sandbox: **BLOCKED / redirect URL tidak tersedia**.
- Pembayaran provider terverifikasi: **NO**.
- PDI, pickup, handover, dan My Garage: **tetap ditahan**.
- Database native/operasional: tidak dimutasi.
