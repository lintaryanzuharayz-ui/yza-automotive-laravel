# YZA Automotive — Final Business Rule UAT

Tanggal: 15 Oktober 2026  
Lingkungan: `http://127.0.0.1:8001`  
Database terverifikasi: `yza_automotive_laravel_uat`

## Ringkasan

- Full regression terbaru: **411 PASS, 0 FAIL, 0 SKIPPED, 1497 assertions**.
- Log: `docs/qa/uat-local/regression-final-business-rule.log`.
- Database operasional port 8000 dan database PHP Native tidak disentuh.
- Browser evidence tersedia sebagai hasil inline dari browser automation; tidak ada screenshot lokal yang diklaim karena file screenshot tidak diekspor.

## 1. Kronologi pickup dan handover

Audit read-only terhadap order existing menemukan ketidaksesuaian nyata:

- Pickup dijadwalkan `2026-10-16 10:00:00` WIB.
- Handover tercatat `2026-10-15 20:51:13` WIB.
- Aplikasi dan database menggunakan `Asia/Jakarta`; browser UAT juga menampilkan WIB.
- Order sudah dibayar, PDI lulus, jadwal terkonfirmasi, dan status siap diambil sebelum handover.

Akar masalahnya adalah service handover hanya memeriksa keberadaan jadwal dan status, bukan apakah waktu pickup sudah tiba. Perbaikan server-side menolak handover sebelum jadwal dan menolak pembuatan jadwal di masa lalu, dengan pesan validasi yang dikembalikan ke UI. Record existing tidak diubah.

Automated guard baru lulus pada `MemberVehiclePurchaseFlowTest`; focused purchase tests lulus. Jalur override/reschedule berwenang tetap dicatat melalui audit event yang tersedia. Dengan guard baru, kronologi baru dinyatakan valid hanya ketika waktu server sudah mencapai jadwal pickup.

## 2. UAT servis dengan sparepart

Browser menjalankan Work Order baru dan tidak mengubah `SRV-202610-ECD72CB1`:

| Tahap | Hasil |
|---|---|
| Booking member dan preferensi Rian Hidayat | PASS — `BSV-202610-10BA185B` |
| Work Order dan inspeksi | PASS — `SRV-202610-C3FAD8CB` |
| Fitment valid untuk Toyota GR Supra 2024 | PASS — Filter Oli, qty 1 |
| Item tidak cocok | PASS secara automated/server validation; tidak dipakai pada WO |
| Stok dan estimasi | PASS — Rp65.000; stok belum berkurang sebelum finalisasi |
| Approval member | PASS melalui `/pelanggan/servis/2/approval` |
| Assignment mekanik | PASS — Rian Hidayat; assignment history tersimpan |
| Pengerjaan dan QC | PASS — semua checklist QC LULUS |
| Finalisasi | PASS — transaksi `TRX-202610-ACD726B7` |
| Pembayaran UAT | PASS — tunai synthetic, terverifikasi melalui UI |
| Riwayat Member Center | PASS — status selesai, mekanik utama, tanggal selesai, item sparepart dan nominal tampil |

Verifikasi read-only setelah finalisasi:

- Satu baris `servis_sparepart`, qty 1, subtotal Rp65.000.
- Satu pembayaran terverifikasi Rp65.000; tidak ada pembayaran duplikat.
- Stok sparepart turun tepat satu kali, dari 3 menjadi 2.
- Assignment memiliki histori lama dan assignment primary aktif ke Rian Hidayat.
- Database efektif tetap `yza_automotive_laravel_uat`.

## 3. Bug yang ditemukan dan diperbaiki

1. Guard waktu handover/pickup belum ada — diperbaiki di `MemberVehiclePurchaseService` dan dites.
2. Form approval menampilkan dropdown sparepart kosong karena controller mengirim `collect()` kosong — diperbaiki di `OperationsServisController`; test render ditambahkan.
3. Approval sparepart menggandakan baris ketika draft sparepart sudah dicatat sebelum approval — diperbaiki agar approval mengikat baris draft yang sama secara idempotent; test ditambahkan.
4. FormRequest fitment memakai nama role legacy `admin`, sehingga Super Admin UAT mendapat 403 — diperbaiki menggunakan `roleKey()`; test Super Admin ditambahkan.

## 4. Kontrol admin dan permission

- UI assignment, approval, QC, finalisasi, dan pembayaran berhasil diuji pada jalur Service Advisor/teknisi.
- Authorization server-side, owner scoping, status gate, CSRF, approval gate, QC gate, dan pencegahan duplikasi dicakup oleh focused tests.
- Mekanik `mekanik506` login berhasil tetapi tidak memiliki WO assignment pada fixture; teknisi synthetic yang benar-benar menerima assignment adalah `petugasdemo` / Rian Hidayat. Ini bukan bypass permission.
- Automated role login/boundary suite mencakup sembilan role: Super Admin, Owner, Sales, Service Advisor, Mekanik, Gudang, Finance, Operasional, dan QC/PDI.
- Browser CRUD lengkap untuk sembilan role belum diverifikasi seluruhnya pada sesi ini.

## 5. Export dan zoom

- PDF/XLSX Owner dan native browser zoom 67% Chrome/Edge belum dijalankan langsung pada sesi ini: **MANUAL UAT REQUIRED**.
- Jangan menyamakan automated route/download checks dengan inspeksi visual isi file.

## 6. Blocker production

- Callback Midtrans HTTPS staging dan refund provider tetap blocker integrasi terpisah; belum dinyatakan PASS.
- Release status: **READY FOR LOCAL/UAT EVIDENCE, NOT READY FOR PRODUCTION** sampai HTTPS callback, refund policy/provider capability, export inspection, dan native zoom UAT diverifikasi.
