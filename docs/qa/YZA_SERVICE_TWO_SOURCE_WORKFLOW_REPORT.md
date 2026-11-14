# YZA Service Two-Source Workflow Report

Tanggal audit: 12 Oktober 2026  
Target audit: Work Order `/admin/servis/13822`

## 1. Hasil audit sistem lama

Alur existing sudah memakai satu Work Order yang berasal dari satu `booking_servis`. Booking menyimpan kendaraan, jenis servis, keluhan, catatan, jadwal, dan status; `ServisService::createFromBooking()` mengubah booking diterima menjadi Work Order tahap pemeriksaan.

Pemeriksaan dapat membuat `service_findings`, lalu `ServiceExecutionService::createApprovalRequest()` membuat snapshot jasa/sparepart dan harga. Keputusan member diverifikasi berdasarkan kepemilikan pelanggan. Approval yang disetujui saat ini menerapkan item ke detail jasa atau draft sparepart melalui `applied_detail_id` dan `applied_sparepart_id`. Stok belum berkurang sampai finalisasi.

`WorkOrderStageService` menjaga urutan pemeriksaan → persetujuan → pengerjaan → QC → finalisasi. `WorkOrderEstimateService` menghitung detail jasa dan draft sparepart, sedangkan `InventoryService` hanya mengurangi stok dalam transaksi finalisasi.

## 2. Model dua sumber pekerjaan

Sumber dapat ditelusuri dari data yang sudah ada tanpa membuat tabel duplikat:

- `CUSTOMER_REQUEST`: detail cocok dengan jenis servis pada booking.
- `TECHNICIAN_RECOMMENDATION`: item execution terhubung melalui `applied_detail_id` atau `applied_sparepart_id` ke item approval yang disetujui.
- `LEGACY`: row lama yang tidak memiliki relasi sumber; ditampilkan sebagai `Legacy — Perlu Klarifikasi`, bukan otomatis dianggap disetujui.

Resolver baru berada di `WorkOrderSourceService` dan tidak mengubah histori lama.

## 3. Perubahan database

Tidak ada migration baru dan tidak ada perubahan pada database native. Relasi approval/execution existing dipakai kembali.

Audit memakai database testing/demo terisolasi. Database native `yza_automotive` tidak ditulis.

## 4–6. Booking, pemeriksaan, dan approval

Tidak ada rewrite booking atau approval. Tahap pengerjaan kini menampilkan panel `Permintaan Awal Pelanggan`, panel keputusan rekomendasi teknisi yang memisahkan disetujui/ditolak/menunggu, serta referensi booking dan catatan customer. Snapshot item dan harga tetap berasal dari approval existing.

## 7. Perubahan Tahap 03

- Permintaan awal tampil read-only dengan status konfirmasi.
- Rekomendasi tambahan tampil terpisah dari pekerjaan pelaksanaan.
- Pekerjaan ditolak tidak ditampilkan sebagai item approved.
- Form jasa dan sparepart tetap collapsible dari implementasi sebelumnya.
- Ringkasan jumlah dan biaya mengambil koleksi/estimate server.
- Tabel lama tetap dipertahankan, termasuk status draft dan aksi hapus yang sudah berotorisasi.
- Jika snapshot approval approved tidak selaras dengan temuan teknisi, panel amber `Perlu Klarifikasi Persetujuan` tampil dengan tautan rincian dan aksi POST resmi `Ajukan klarifikasi`.

## 8–9. QC, finalisasi, jasa, dan sparepart

Guard `assertReadyForQualityControl()` sekarang memeriksa bahwa detail tambahan dan sparepart pada Work Order yang memiliki approval dapat ditelusuri ke item approval yang disetujui atau jenis servis awal. `ServisSparepartService` menolak sparepart baru di luar cakupan approval ketika Work Order sudah memiliki item approval.

Finalisasi, transaksi pembayaran, invoice, InventoryService, locking, dan idempotency existing tidak diubah. Draft sparepart tetap tidak mengurangi stok.

## 10–12. Biaya, stok, dan backward compatibility

Perhitungan tetap berasal dari `WorkOrderEstimateService` dan `ServisService::finalizeService()`. Item approval tidak digandakan oleh guard baru. Data legacy tetap dapat dibaca; hanya diberi penanda klarifikasi dan tidak dipromosikan otomatis menjadi approved.

## 13. Mekanisme klarifikasi dan root cause WO 13822

Root cause bukan perubahan harga atau stok, melainkan tidak adanya relasi eksplisit antara judul/temuan rekomendasi dan snapshot item pada approval lama. WO 13822 mencatat temuan `Filter Oli Perlu Diganti`, tetapi snapshot approved adalah oli mesin. Jasa oli yang sudah ada juga tidak mempunyai `id_jenis_servis`, sehingga tidak boleh dipaksa menjadi relasi customer request hanya berdasarkan nama.

Sistem kini menandai kondisi tersebut saat approval berstatus `approved` dan item sparepart tidak selaras dengan judul/rekomendasi temuan. Petugas admin/petugas dapat mengirim POST klarifikasi. Backend memvalidasi ownership approval terhadap Work Order, status Work Order, dan keputusan approval; lalu mencatat event `approval_clarification_requested` berisi Work Order, approval/item IDs, alasan, aktor, waktu, dan instruksi membuat approval customer baru. Approval lama, customer note, timestamp keputusan, transaksi, dan stok tidak diubah. Pengajuan kedua untuk approval yang sama ditolak agar audit tidak berulang.

Klarifikasi ini sengaja tidak membuat keputusan customer baru secara otomatis. Untuk WO 13822, keputusan customer baru masih diperlukan: apakah oli adalah pekerjaan awal yang telah dikonfirmasi, atau filter oli memang pekerjaan tambahan dengan item snapshot yang benar.

## 14. Audit Work Order 13822

Temuan read-only:

- Booking: `BSV-202610-89C56E36`, status `diproses`, jenis servis `Servis Berkala`.
- Permintaan booking: `Ganti Oli Mesin Berkala 10.000 KM & Cek Rutin Kendaraan YZA`.
- Temuan: `Filter Oli Perlu Diganti`.
- Approval: `Persetujuan Penggantian Filter Oli`, status `approved`.
- Snapshot approval: `Oli Mesin Fully Synthetic 0W-20 (1 Liter)`, SKU `YZA-OIL-SYN1L`, 1 unit, Rp165.000.
- Jasa tercatat: `Jasa Penggantian Oli Mesin dan Pemeriksaan Filter Oli`, Rp75.000, tanpa `id_jenis_servis`.
- Draft sparepart cocok dengan snapshot oli dan terhubung melalui `applied_sparepart_id`.

Kesimpulan: pekerjaan awal terbukti berasal dari booking servis berkala, tetapi jasa existing belum mempunyai referensi jenis servis eksplisit. Approval dinyatakan disetujui untuk item oli mesin, sementara judul approval menyebut filter oli. Karena snapshot historis tidak boleh diubah, item ini ditampilkan sebagai kondisi yang perlu klarifikasi resmi; sistem tidak memperbaiki record atau membuat approval baru secara otomatis.

## 15. File yang diubah

- `app/Services/WorkOrderSourceService.php`
- `app/Services/ServiceExecutionService.php`
- `app/Services/WorkOrderSourceService.php`
- `app/Services/ServisSparepartService.php`
- `app/Http/Controllers/OperationsServisController.php`
- `app/Http/Controllers/ServiceExecutionController.php`
- `routes/web.php`
- `resources/views/operations/servis/wizard.blade.php`
- `tests/Feature/ServiceExecutionPhase4ATest.php`

## 16–17. Testing dan browser QA

- PHP lint untuk service/controller: PASS.
- Blade cache: PASS.
- Focused regression suite final: 86 passed, 2 skipped, 230 assertions.
- Test klarifikasi baru mencakup audit event dan pembuktian histori approval tidak berubah; suite dijalankan ulang setelah perbaikan assertion timestamp.
- Browser visual QA admin: belum dapat dilakukan karena browser automation tidak memiliki sesi admin; status ini dilaporkan jujur dan tidak diklaim PASS.

## 18. Masalah tersisa

Work Order historis yang dibuat sebelum relasi applied item tersedia masih memerlukan klarifikasi resmi. Sistem mempertahankan kemampuan membaca histori dan menahan item execution baru yang tidak memiliki sumber sah ketika approval sudah ada.

## 19. Status

PARTIAL — implementasi bisnis dan regression tests selesai, tetapi browser QA admin dan prosedur koreksi resmi untuk histori Work Order 13822 masih memerlukan sesi/keputusan operasional admin.
