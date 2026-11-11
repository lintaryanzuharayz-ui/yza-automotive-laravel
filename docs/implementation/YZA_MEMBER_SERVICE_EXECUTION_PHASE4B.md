# YZA Member Service Execution Phase 4B

Tanggal implementasi: 12 Oktober 2026  
Scope: member service status center, approval UI, dan integrasi frozen contract Phase 4A.

## Parallel integration

Endpoint Phase 4B tidak dibuat di sisi member. UI disiapkan untuk mengonsumsi contract berikut saat backend Admin Phase 4A tersedia:

- `GET /pelanggan/servis/{servis}/tracking`
- `GET /pelanggan/servis/{servis}/approvals`
- `POST /pelanggan/servis/{servis}/approvals/{approval}/decision`

Saat endpoint belum tersedia, halaman menampilkan loading/error state dan tombol retry tanpa membuat data tracking atau approval palsu.

## Tracking UI

SERVICE STATUS CENTER menampilkan current stage dari `current_stage`, timeline dari array `stages` backend, pending approval count, dan tombol refresh manual. Tidak ada polling agresif atau subsystem realtime baru. Stage tidak ditandai selesai berdasarkan asumsi client.

## Approval UI

Approval card menampilkan title, reason, requested date, status, item description, quantity, unit price/subtotal, dan estimasi tambahan dari response backend. Member selalu dapat melihat opsi **Tidak Ada Preferensi** secara terpisah dari execution approval.

Approve/reject menggunakan confirmation dialog, disable tombol saat submit, dan hanya mengirim `decision` serta `note` opsional. UI tidak mengirim harga, quantity, item mutation, labor amount, atau status. Approved/rejected state tidak menyediakan decision ulang.

## QC, payment, dan ready state

UI execution menyediakan tempat untuk current status, QC status, payment status, ready-for-pickup, dan completed state dari tracking response. Payment tidak dibangun ulang; link invoice/payment existing tetap dipertahankan. Tidak ada pickup date/time atau total invoice yang dihitung dari asumsi frontend.

## Privacy dan authorization

Detail servis tetap dibatasi oleh ownership check existing. UI hanya merender field public dari frozen contract dan tidak menampilkan phone, email, salary, credentials, internal notes, customer lain, atau assignment internal.

## Validasi

- `php artisan view:cache` — lulus
- Focused Phase 4B integration tests — 2 passed, 7 assertions
- Tidak ada migration, schema change, database baru, shared service change, route baru, atau admin/operations file change.

Status integrasi: **READY TO VERIFY WITH ADMIN PHASE 4A**.
