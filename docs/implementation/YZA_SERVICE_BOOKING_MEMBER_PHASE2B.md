# YZA Member Service Scheduling Phase 2B

Tanggal implementasi: 12 Oktober 2026  
Scope: member UI dengan konsumsi shared scheduling engine Phase 2A.

## Contract dan backend

Contract yang digunakan: `docs/contracts/YZA_SERVICE_SCHEDULING_API_CONTRACT.md`.
Availability member memakai route existing `pelanggan.booking-servis.availability`:

- month mode: `month=YYYY-MM&jenis_servis_id=<id>` untuk kalender;
- day mode: `date=YYYY-MM-DD&jenis_servis_id=<id>` untuk slot.

`App\Services\ServiceSchedulingService` tetap menjadi single source of truth untuk operating hours, break, blackout, durasi, capacity, status slot, dan validasi ulang saat booking disimpan. Tidak dibuat endpoint, calculator, atau scheduling engine kedua.

## Perubahan UI member

- Booking diubah menjadi progressive flow: **01 Pilih Jadwal**, **02 Detail Servis**, **03 Konfirmasi**.
- Vehicle preselection, ownership, gambar aktual, mileage read-only, dan Verified YZA Ownership dari Phase 1 dipertahankan.
- Jenis servis ditampilkan dari master aktif dengan deskripsi, estimasi harga, dan `duration_minutes` backend.
- Kalender memuat satu response month, menampilkan state `available`, `almost_full`, `full`, dan `unavailable`, serta menonaktifkan tanggal yang tidak valid.
- Pemilihan tanggal memuat day response dan hanya menampilkan slot yang dikembalikan backend. `capacity_remaining` dipakai langsung untuk label slot.
- Tidak ada input waktu bebas; waktu kedatangan dikirim melalui hidden field yang hanya diisi dari slot valid.
- Estimasi durasi dan waktu selesai dihitung untuk presentasi berdasarkan durasi service backend dan slot yang telah dinyatakan valid oleh backend.
- Step detail mempertahankan keluhan dan catatan. Konfirmasi menampilkan kendaraan, jadwal, servis, estimasi, dan detail input.
- Submit tetap memakai booking store existing; error slot penuh dipresentasikan sebagai pilihan jadwal yang baru saja penuh.
- Setelah berhasil, member diarahkan ke halaman detail booking dengan kode booking, kendaraan, servis, jadwal, status, dan CTA aktual.

## Mileage dependency

Schema `booking_servis` saat ini tidak memiliki field kilometer booking. Sesuai aturan Phase 2B, tidak dibuat migration dan tidak ada workaround penyimpanan palsu. UI menampilkan **Kilometer Terakhir** secara read-only; update kilometer resmi tetap mengikuti aturan Work Order.

## Validasi

- `php artisan optimize:clear` — lulus
- `php artisan view:cache` — lulus
- Focused member/scheduling tests — 27 passed, 62 assertions
- `git diff --check` — dijalankan setelah perubahan

## Gaps yang sengaja belum dikerjakan

- Mileage booking persistence belum tersedia (`READ-ONLY`).
- Preferensi teknisi, Service Advisor, customer approval, dan Quality Control tetap fase berikutnya.
- Race-condition UX ditangani pada sisi member dengan pesan khusus dan mempertahankan input; true parallel-process test tetap mengikuti coverage backend yang ada.

Tidak ada migration, perubahan schema, database baru, perubahan admin, atau perubahan algoritma scheduling.
