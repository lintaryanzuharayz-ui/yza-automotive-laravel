# YZA Automotive — Local UAT Evidence

Tanggal eksekusi: 15 Oktober 2026

## Lingkungan

- URL UAT: `http://127.0.0.1:8001`
- Database: `yza_automotive_laravel_uat`
- Database operasional `yza_automotive_laravel`: tidak digunakan untuk mutasi UAT.
- Database PHP Native: tidak disentuh.
- Fixture UAT sintetis: 9 akun staff, 1 member, kendaraan demo, katalog, stok, dan booking servis.

## Bukti file

- [owner-rekap-penjualan-uat.pdf](owner-rekap-penjualan-uat.pdf) — berhasil diunduh melalui browser UAT; PDF berisi periode 01 Jan–15 Oct 2026 dan transaksi `TRX-DEMO-SALE-SUPRA-01` sebesar Rp2.200.000.000.
- [owner-rekap-penjualan-uat.xlsx](owner-rekap-penjualan-uat.xlsx) — berhasil diunduh melalui browser UAT; workbook dan sheet `Rekap Penjualan` dapat dibaca, dengan periode dan nominal yang sama.

## Browser evidence yang benar-benar dijalankan

- Smoke login/dashboard 9 role pada UAT: PASS — Super Admin, Owner, Sales, Service Advisor, Mekanik, Gudang, Finance, Operasional, dan QC/PDI masing-masing diarahkan ke dashboard role yang benar.
- Member login dan Member Center: PASS.
- Member melihat 1 kendaraan terverifikasi: PASS.
- Booking Servis: PASS sampai booking diajukan.
- Pilihan tanggal/waktu dan preferensi mekanik `Rian Hidayat (Teknisi Demo)`: PASS.
- Nomor booking yang dihasilkan: `BSV-202610-96C3C181`.
- Status hasil: `Menunggu Konfirmasi YZA`.
- Screenshot visual dashboard member dan halaman booking berhasil diambil dari browser UAT selama sesi, tetapi backend browser automation tidak menyediakan penyimpanan screenshot ke path lokal; screenshot tersebut menjadi evidence inline sesi, bukan file yang diklaim tersimpan.

## Belum diklaim PASS

- 9 role CRUD penuh melalui browser: belum selesai; login/dashboard saja telah dibuktikan sebelumnya.
- Pembelian kendaraan sampai payment, PDI, handover, dan My Garage melalui browser: belum selesai.
- Booking servis sampai Service Advisor, assignment, pemeriksaan, QC, pembayaran, finalisasi, dan riwayat: belum selesai.
- Native zoom 67%: manual Chrome/Edge required.
- Callback HTTPS/refund Midtrans provider: blocked sampai staging HTTPS dan credential sandbox/provider tersedia.

## Pembaruan UAT 15 Oktober 2026

- Service Advisor menerima booking `BSV-202610-96C3C181` melalui UI: PASS.
- Pemeriksaan awal kendaraan, pembuatan Work Order `SRV-202610-ECD72CB1`, dan pencatatan jasa Rp150.000 melalui UI: PASS.
- Penugasan utama melalui UI: PASS — Rian Hidayat (Teknisi Demo) dipilih sesuai preferensi pelanggan. Histori assignment lama tetap tersimpan dan ditutup, assignment baru tercatat pada `service_work_order_assignments`.
- Teknisi yang ditugaskan membuka Work Order melalui dashboard teknisi: PASS. Assistant tidak diuji karena fixture UAT hanya menyediakan satu teknisi eligible.
- Teknisi menginisiasi QC melalui UI, melengkapi 6 checklist wajib dengan hasil PASS, dan menyimpan catatan QC: PASS. Tahap Finalisasi & Pembayaran terbuka setelah QC lulus.
- Work Order `SRV-202610-ECD72CB1` difinalisasi melalui UI resmi: PASS. Transaksi `TRX-202610-B1BF99A3` dibuat dengan total Rp150.000.
- Pembayaran sintetis tunai Rp150.000 dicatat melalui UI dan diverifikasi: PASS. Status transaksi menjadi `lunas`; status Work Order menjadi `selesai` dengan tanggal selesai tercatat.
- Riwayat Member dikonfirmasi melalui authenticated HTTP evidence: Work Order, mekanik utama Rian Hidayat, status selesai, dan nominal tampil sesuai data database. Browser member session terpisah belum tersedia pada sesi ini.
- Ditemukan dan diperbaiki bug validasi: opsi assistant “Tidak ada” mengirim string kosong yang ditolak sebagai integer. Validasi assistant sekarang menerima nilai nullable, tanpa melonggarkan validasi teknisi utama.
- Akses Advisor ke detail booking `/petugas/booking-servis/{id}` sebelumnya 403; middleware route diperbaiki dan retest UI berhasil.
- Focused regression assignment + wizard: PASS, 5 tests / 32 assertions.
- Full regression sebelumnya (339 PASS, 68 FAIL) terbukti memakai database operasional karena `artisan test --env=testing` tidak mengoverride konfigurasi proses secara konsisten pada Windows; hasil itu dinyatakan invalid. Recovery dijalankan dengan environment proses eksplisit ke `yza_automotive_laravel_test` dan lulus **407 PASS, 0 FAIL, 0 SKIPPED, 1488 assertions**. Rincian ada di [REGRESSION_RECOVERY_REPORT.md](REGRESSION_RECOVERY_REPORT.md).

## Pembaruan sequential session UAT — 15 Oktober 2026

- Logout mekanik melalui UI dan login guest: PASS.
- Login member melalui form browser: PASS.
- Riwayat servis `SRV-202610-ECD72CB1` pada Member Center: PASS; Rian Hidayat, tanpa pendamping, transaksi `TRX-202610-B1BF99A3`, Rp150.000, Lunas.
- Checkout Toyota GR Supra dan pembuatan order `ORD-202610-B8358FB0`: PASS.
- Persetujuan order oleh Super Admin: PASS.
- Pembayaran Midtrans Sandbox: BLOCKED oleh pesan aplikasi `Midtrans Sandbox gagal membuat pembayaran.`
- PDI, pickup, handover, dan My Garage: belum dilanjutkan karena pembayaran belum tervalidasi.
- Perbaikan source: tombol logout POST/CSRF ditambahkan ke shell petugas/mekanik; tidak ada pelemahan middleware.
- Focused regression setelah perubahan: 11 PASS / 57 assertions. Full regression: 407 PASS / 0 FAIL / 0 SKIPPED / 1.488 assertions.

## Sisa blocker E2E

- Pembayaran provider Midtrans Sandbox belum berhasil dibuat; perlu konfigurasi sandbox/endpoint provider yang valid sebelum PDI, handover, dan My Garage dapat diuji.
- E2E pembelian kendaraan berhenti setelah persetujuan order, sebelum pembayaran.
- Screenshot browser belum tersimpan sebagai file lokal; evidence sesi tersedia inline.
- Negative CSRF UAT: POST tanpa token, token invalid, dan token lintas session menghasilkan 419; token sah dengan kredensial invalid menghasilkan 302 tanpa perubahan data bisnis. Detail ada di [SERVICE_UAT_CONTINUATION_REPORT.md](SERVICE_UAT_CONTINUATION_REPORT.md).
- Verifikasi Member Service dan status browser purchase E2E dirangkum di [MEMBER_SERVICE_PURCHASE_E2E_REPORT.md](MEMBER_SERVICE_PURCHASE_E2E_REPORT.md). Sesi browser telah dipulihkan berurutan; blocker tersisa adalah pembayaran Midtrans Sandbox.
