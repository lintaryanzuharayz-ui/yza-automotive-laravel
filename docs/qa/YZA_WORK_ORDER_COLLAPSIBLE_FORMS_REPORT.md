# YZA Work Order Collapsible Forms Report

Tanggal: 12 Oktober 2026  
Target: `/admin/servis/{id}/pengerjaan` — Tahap 03

## 1. Root cause

Form jasa dan suku cadang dirender langsung di dalam card tanpa state tampilan. Akibatnya, form selalu mengambil ruang walaupun pengguna hanya ingin membaca tabel data yang sudah tersimpan.

## 2. File yang diubah

- `resources/views/operations/servis/wizard.blade.php`
- `app/Http/Controllers/OperationsServisController.php`

## 3. Implementasi state form

UI memakai state tunggal `none`, `jasa`, atau `sparepart`. Header tetap tampil, sedangkan form dibuka inline melalui tombol Tambah. Hanya satu form yang dapat terbuka. Form dengan old input atau validation error otomatis dibuka kembali.

## 4. Animasi

Panel menggunakan grid-row collapse, fade, dan pergeseran vertikal kecil selama sekitar 220–260 ms. `prefers-reduced-motion` dihormati. Saat JavaScript tidak tersedia, form tetap terlihat sebagai fallback.

## 5. Perilaku setelah sukses/error

- POST tetap memakai route, method, CSRF, dan handler existing.
- Sukses menggunakan redirect dan flash message baru; halaman kembali ke kondisi ringkas dan tabel menampilkan data terbaru.
- Error memakai `withInput()`, sehingga form terkait tetap terbuka dan nilai input dapat dipulihkan.
- Tombol Batal bertipe `button`, sehingga tidak mengirim form.

## 6. Ringkasan item dan biaya

Jumlah jasa/suku cadang serta total jasa/suku cadang diambil dari koleksi server dan nilai estimasi existing. Tidak ada angka hardcode dan tidak ada penghitungan approval kedua kali.

## 7–10. Validasi

- Blade view cache: PASS.
- `WorkOrderWizardPhaseTest`: PASS, 3 test; 1 test skipped karena fixture tidak tersedia.
- `ServiceExecutionPhase4ATest`: PASS, 7 test.
- `ServisFinalizationStage5CTest`: PASS, 21 test.
- `ServicePaymentStage5DTest`: PASS, 21 test.
- Total focused run: 52 passed, 1 skipped, 152 assertions.
- Regression coverage tetap memastikan approval, QC, finalisasi, pembayaran, dan stok berjalan melalui service existing.

## 11. Responsive/browser QA

CSS menyediakan wrapping header pada tablet, satu kolom pada mobile, horizontal scroll untuk tabel, dan tanpa transform/zoom halaman. Browser automation tersedia tetapi sesi yang terhubung hanya login member; halaman admin target mengarah ke login, sehingga screenshot admin desktop/tablet/mobile belum dapat diverifikasi langsung.

## 12. Masalah yang tersisa

Visual browser QA admin memerlukan sesi admin yang valid. Tidak ada masalah fungsional yang ditemukan pada test terisolasi.

## 13. Status

PARTIAL — implementasi dan automated test selesai; browser visual QA belum dapat dilakukan karena sesi admin tidak tersedia.
