# YZA Admin Work Order Premium UI Redesign

Tanggal: 12 Oktober 2026

## Files changed

- `resources/views/operations/servis/show-premium.blade.php`
- `app/Http/Controllers/OperationsServisController.php`

## Layout structure

1. Breadcrumb dan action bar kembali ke daftar.
2. Hero Work Order horizontal dengan gambar kendaraan internal, identitas kendaraan, status, dan progress stepper.
3. Booking context full-width dengan pemisahan preferensi teknisi, teknisi final, dan Service Advisor.
4. Dua kolom fokus operasional: Temuan Tambahan dan Persetujuan Customer.
5. Quality Control strip dengan checklist existing.
6. Lower operations grid: Customer & Vehicle, Complaint, Inspection, Work In Progress, Spare Parts, Cost Summary, dan Finalization & Payment.

## Presentation behavior

- Desktop memakai ruang konten kanan secara maksimal.
- Form input, select, dan textarea memiliki ukuran, border, radius, focus state, dan tombol yang konsisten.
- Grid turun menjadi satu kolom pada laptop/tablet; tabel memakai horizontal scroll hanya di area tabel pada layar kecil.
- Status, harga total, dan data customer tetap berasal dari model/controller existing.
- Gambar kendaraan memakai `VehicleGalleryService` dan fallback internal YZA; tidak ada gambar external atau data screenshot yang di-hardcode.

## Existing actions preserved

- Inspection tetap memakai route inspection existing.
- Detail pekerjaan dan sparepart tetap memakai service/action existing.
- Finding, approval customer, QC start/update, finalisasi, invoice, dan payment tetap memakai route existing.
- Promo selector existing tetap dipakai pada finalisasi.

## Business logic preserved

Tidak ada perubahan status machine, approval lifecycle, QC rule, inventory timing, payment, authorization, notification, atau customer ownership. Perubahan controller hanya eager-load gallery kendaraan dan mengirim URL gambar presentasi ke view.

## Validation

- `php artisan view:cache` passed.
- PHP lint controller passed.
- `git diff --check` passed for touched files.
- Focused service regression: 69 tests passed, 185 assertions.
- Database schema tidak berubah dan migration tidak dijalankan.
