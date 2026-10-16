# YZA Admin Service Operations Phase 1

Tanggal implementasi: 12 Oktober 2026  
Scope: admin/operations only. Member UI, My Garage, auth, payment, purchase, PDI, handover, dan inventory tidak diubah.

## Alur operasional

Alur existing dipertahankan:

`Booking Member Masuk → Review Admin → Terima/Tolak → Buat Work Order → Work Order Center`

Booking yang diterima tidak membuat Work Order otomatis. Admin/petugas tetap menekan **Buat Work Order** secara eksplisit.

## Pembaruan Phase 1

- Booking queue tetap memakai route `admin.booking-servis.index` dan menampilkan data aktual pelanggan, kendaraan, nopol, jenis servis, jadwal, status, dan aksi.
- Status database tidak diubah. Label UI sekarang lebih jelas:
  - `menunggu` → Menunggu Konfirmasi
  - `diterima` → Booking Diterima
  - `ditolak` → Booking Ditolak
  - `dibatalkan` → Dibatalkan Pelanggan
  - `diproses` → Work Order Aktif
  - `selesai` → Selesai
- Halaman booking dan Work Order memiliki navigasi internal: Booking Menunggu, Work Order, dan Jadwal Bengkel.
- Jadwal Bengkel ditampilkan sebagai readiness state “Segera hadir · belum aktif”; tidak ada kalender atau slot palsu.
- Detail booking menampilkan data pelanggan, kendaraan, nomor polisi, jenis servis, tanggal, jam, keluhan, catatan, kilometer kendaraan, dan aksi sesuai status.
- Setelah booking diterima, aksi yang tersedia tetap **Buat Work Order**.
- Jika Work Order sudah ada, UI menampilkan **Buka Work Order Servis** dan status Work Order Aktif.
- Work Order detail menampilkan konteks booking, jenis servis, tanggal/jam booking, dan petugas/teknisi penanggung jawab. Jika belum ada, labelnya “Belum ditugaskan”.
- Work Order Center menampilkan jenis servis dari relasi booking yang sudah tersedia, tanpa query per baris tambahan.
- Dashboard admin mengarahkan alert booking servis yang belum diproses ke detail booking admin.

## Duplicate Work Order protection

Tabel `servis.id_booking_servis` sudah memiliki unique constraint `uq_servis_booking`. Tidak ada migration yang diperlukan.

`ServisService::createFromBooking` sekarang:

1. Mengunci booking dalam transaction.
2. Memeriksa apakah Work Order untuk booking tersebut sudah ada.
3. Mengembalikan Work Order existing jika ada.
4. Hanya membuat Work Order baru jika booking berstatus `diterima` dan belum memiliki Work Order.

Dengan demikian repeated POST atau double click tidak membuat Work Order kedua. Controller mengarahkan user ke Work Order yang sama setelah proses tersebut.

## Authorization

Middleware role existing tetap digunakan. Route admin/petugas tetap terpisah dari route member. Ownership booking, status booking, dan assignment petugas tetap diproses oleh backend existing.

## Validasi

- `php artisan optimize:clear`: lulus
- `php artisan view:cache`: lulus
- PHP lint controller/service: lulus
- Focused booking/service tests: **29 passed, 56 assertions**

Test penting yang lulus:

- booking member dapat dibuat dan dikonfirmasi;
- reject dan pembatasan transisi status;
- guest/member tidak dapat mengakses operasi admin;
- Work Order mengunci dan menyinkronkan booking;
- satu booking hanya menghasilkan satu Work Order;
- repeated create mengembalikan Work Order existing;
- aturan kilometer dan inspection tetap berjalan.

## Belum diimplementasikan

Jadwal Bengkel masih readiness state. Fitur berikut sengaja belum dibuat karena belum memiliki source of truth:

- Calendar;
- Operating Hours;
- Slots;
- Capacity;
- Technician Preference;
- Service Advisor.

## Dampak perubahan

- Member files changed: **NO**
- Database schema changed: **NO**
- Migration executed: **NO**
- New database created: **NO**
- Existing finalization flow: **PRESERVED**
- Existing payment flow: **PRESERVED**
