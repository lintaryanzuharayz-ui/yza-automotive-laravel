# YZA Post-Payment Flow Audit

Tanggal audit: 11 Oktober 2026  
Project: `C:\xampp\htdocs\yza_automotive_laravel`  
Ruang lingkup: audit read-only. Tidak ada implementasi, migration, perubahan database, perubahan status, atau perubahan business logic pada tahap ini.

## 1. Executive Summary

Fondasi workflow pasca-pembayaran pembelian mobil sudah tersedia dan dapat dilanjutkan ke pengembangan berikutnya.

| Area | Status audit | Temuan utama |
|---|---|---|
| Payment dan Midtrans | **ADA** | Settlement/capture diverifikasi, dicatat sebagai pembayaran terverifikasi, dan idempotency event tersedia. |
| PDI | **PARTIAL** | Ada tabel, model, route admin, service, dan checklist 10 kategori; belum ada lifecycle pemeriksaan yang lengkap. |
| Pickup / jadwal pengambilan | **PARTIAL** | Ada `jadwal_pengambilan`, tetapi belum ada status jadwal/pickup terpisah. |
| Handover | **ADA** | Admin dapat menyelesaikan serah-terima setelah PDI lulus dan lunas. |
| Inventory | **ADA, berbasis agregat** | Reservasi dihitung dari order aktif; unit menjadi sold saat handover. |
| Ownership / My Garage | **ADA** | Kendaraan baru dibuat/sinkron setelah handover selesai, bukan saat payment. |
| Notification | **PARTIAL** | Infrastruktur notifikasi ada, tetapi event khusus payment/PDI/pickup mobil belum lengkap. |
| Schema untuk target bisnis | **PARTIAL** | Tidak cukup untuk audit PDI/pickup rinci tanpa penambahan field/tabel pada fase implementasi. |

Kesimpulan: **READY WITH MIGRATION** untuk implementasi target lengkap. Workflow saat ini sudah aman untuk prinsip penting: pembayaran lunas tidak langsung membuat ownership; ownership baru dibuat setelah serah-terima.

## 2. Current Workflow

Alur member yang ditemukan:

1. Member checkout melalui `Pelanggan\MemberVehiclePurchaseController@store`.
2. `MemberVehiclePurchaseService::create()` mengunci row mobil/inventory, memeriksa ketersediaan, membuat `pesanan_mobil_member` berstatus `menunggu_verifikasi`, dan untuk unit individual mengubah mobil menjadi `dibooking`.
3. Admin menyetujui melalui `Admin\MemberVehiclePurchaseController@approve`.
4. `MemberVehiclePurchaseService::approve()` membuat satu `transaksi` dan satu `penjualan_mobil`, lalu order menjadi `menunggu_pembayaran`.
5. Pembayaran dapat diajukan offline atau dibuat melalui `MidtransPaymentService`.
6. Pembayaran terverifikasi mengubah `transaksi` dan order member menjadi `lunas`.
7. Admin dapat mengisi PDI setelah transaksi lunas.
8. Jika seluruh checklist PDI bernilai benar, order menjadi `siap_diambil`.
9. Admin menjadwalkan pengambilan melalui `jadwal_pengambilan`.
10. Admin menyelesaikan handover setelah order `siap_diambil`, transaksi lunas, dan PDI lulus.
11. Handover mengubah penjualan menjadi `selesai`, inventory menjadi terjual atau mobil individual menjadi `terjual`, order menjadi `diserahkan`, lalu sinkronisasi kendaraan ke My Garage dijalankan.

Komponen utama:

- Model: `PesananMobilMember`, `Transaksi`, `Pembayaran`, `PembayaranGateway`, `EventPembayaranGateway`, `PenjualanMobil`, `PdiPesananMobil`, `SerahTerimaPesananMobil`, `VehicleInventory`, `VehicleInventoryHistory`, `KendaraanPelanggan`, `Mobil`.
- Service: `MemberVehiclePurchaseService`, `PaymentService`, `MidtransPaymentService`, `VehicleInventoryService`, `VerifiedCarOwnershipService`.
- Controller: controller member/admin pembelian mobil, `MidtransController`, `CarSalePaymentController`, `CarSaleInvoiceController`, `Pelanggan\KendaraanController`.
- Route penting: `pelanggan.pesanan-mobil.*`, `admin.pesanan-mobil.*`, `pelanggan.midtrans.*`, `pelanggan.kendaraan.*`, dan invoice mobil.
- Middleware/authorization: `auth`, `role:pelanggan`, `role:admin`, `member`; controller member memeriksa kepemilikan `id_pelanggan`.

## 3. Current State Machine

### Order `pesanan_mobil_member`

State aktual yang dipakai source code:

```text
menunggu_verifikasi
  -> menunggu_pembayaran       (admin approve)
  -> ditolak                   (admin reject)

menunggu_pembayaran
  -> lunas                     (payment verified)
  -> dibatalkan                (payment rejected)

lunas
  -> pdi_lulus                 (PDI seluruh item lulus)
  -> pdi_gagal                 (ada item checklist false)

pdi_lulus
  -> siap_diambil              (status dijadwalkan/ditampilkan sebagai siap)

siap_diambil
  -> diserahkan                 (handover berhasil)
```

Catatan: service `schedule()` menerima status `menunggu_pembayaran`, `lunas`, `pdi_lulus`, dan `siap_diambil`, tetapi field jadwal sendiri tidak mengubah status order. Status `pdi_lulus` dibuat oleh `savePdi()`, sedangkan `siap_diambil` juga dapat dibuat oleh PDI lulus. Tidak ada enum database untuk order; status adalah string aplikasi.

### Transaction `transaksi`

Enum database: `draft`, `belum_bayar`, `dp`, `lunas`, `dibatalkan`.

Untuk pembelian mobil member:

```text
belum_bayar -> lunas
belum_bayar -> dibatalkan
```

`PaymentService` menghitung jumlah pembayaran terverifikasi. Pembayaran transfer/Midtrans awalnya `menunggu` atau gateway `pending`; baru setelah verifikasi settlement/capture transaksi menjadi `lunas`.

### Payment

Enum pembayaran: arah `masuk|keluar`, metode `tunai|transfer|kredit|lainnya`, status `menunggu|terverifikasi|ditolak`.

Midtrans gateway menggunakan state provider seperti `created`, `pending`, `settlement`, `capture`, `deny`, `cancel`, `expire`, `failure`, `refund`, dan `requires_review`.

### Sale

Enum `penjualan_mobil.status`: `proses`, `selesai`, `dibatalkan`.

Pada member vehicle purchase, sale tetap `proses` setelah lunas dan baru menjadi `selesai` ketika handover berhasil.

## 4. Payment Completion

Payment completion memiliki guard dan transaksi database:

- `MidtransPaymentService::process()` memakai `lockForUpdate()` pada gateway dan transaksi.
- Signature Midtrans diverifikasi untuk notification.
- Gross amount dibandingkan dengan nominal gateway dan sisa tagihan.
- `EventPembayaranGateway` memakai `event_key` untuk mencegah event identik diproses berulang.
- Payment record hanya dibuat sekali saat gateway sukses dan `id_pembayaran` masih kosong.
- `PaymentService` menolak overpayment, pembayaran setelah `lunas`, dan pembayaran pada transaksi dibatalkan.
- Finish URL browser tidak dijadikan source of truth; status server Midtrans disinkronkan.

Gap audit: `PaymentService` memiliki method `assertCarSaleStage()`, tetapi pemeriksaan ini tidak tampak dipanggil di awal `create()`/`verify()`. Jalur member offline memiliki pemeriksaan status order sendiri, dan Midtrans hanya memeriksa status transaksi. Hal ini perlu direview pada fase implementasi agar semua jalur pembayaran mobil konsisten mensyaratkan order sudah disetujui admin.

## 5. Existing PDI Capability

Status: **PARTIAL, sudah ada dan berjalan**.

Yang sudah ada:

- Tabel `pdi_pesanan_mobil`, unique satu PDI per order.
- Model `PdiPesananMobil` dan relasi dari order.
- Route admin `POST admin/pesanan-mobil/{pesanan_mobil}/pdi`.
- `MemberVehiclePurchaseService::savePdi()`.
- PDI hanya boleh disimpan jika transaksi `lunas` dan order belum diserahkan/dibatalkan/ditolak.
- Checklist kategori: `mesin`, `eksterior`, `interior`, `rem`, `ban`, `kelistrikan`, `lampu`, `ac`, `dokumen`, `final_quality`.
- Status aktual PDI: `belum_diperiksa`, `gagal`, `lulus`.
- Petugas, catatan, dan waktu pemeriksaan disimpan.

Yang belum ada untuk target bisnis:

- State eksplisit `sedang_diperiksa`.
- `started_at` dan `completed_at` terpisah; saat ini hanya `diperiksa_at`.
- Struktur temuan per item, severity, foto, tindakan koreksi, dan approval.
- Reinspection/history PDI; `firstOrNew()` menyimpan satu row aktif dan dapat menimpa pemeriksaan sebelumnya.
- Hasil umum/member-facing yang lebih terstruktur.

## 6. Existing Pickup/Handover Capability

### Pickup

Status: **PARTIAL**.

Yang ada adalah field `jadwal_pengambilan` pada `pesanan_mobil_member` dan route admin `.../schedule`. Field ini menyimpan tanggal/waktu dan dapat ditampilkan ke member. Belum ada tabel/entitas jadwal pickup tersendiri, lokasi, status `belum_dijadwalkan|dijadwalkan|siap_diambil`, atau audit perubahan jadwal.

### Handover

Status: **ADA, tetapi minimal**.

Tabel `serah_terima_pesanan_mobil` menyimpan:

- petugas,
- nama penerima,
- nomor identitas,
- pelunasan terverifikasi,
- dokumen lengkap,
- kendaraan sesuai,
- catatan,
- `diserahkan_at`.

Route admin dan service memvalidasi order `siap_diambil`, transaksi `lunas`, serta PDI `lulus`. Handover bersifat satu row per order dan dilindungi unique `id_pesanan`. Belum ada tanda tangan digital, bukti serah-terima, foto, lokasi, atau jadwal aktual terpisah dari waktu penyerahan.

## 7. Vehicle Ownership

Source of truth ownership YZA adalah kombinasi:

```text
penjualan_mobil.status = selesai
transaksi.jenis_transaksi = penjualan_mobil
transaksi.status = lunas
pesanan_mobil_member.status = diserahkan
mobil memiliki nomor_rangka/nomor_mesin
```

`VerifiedCarOwnershipService::completedSalesFor()` mensyaratkan seluruh kondisi transaksi/sale/member order tersebut. Ini membedakan kendaraan yang hanya didaftarkan member dari kendaraan yang benar-benar dibeli melalui YZA.

`kendaraan_pelanggan` sendiri memiliki `id_pelanggan`, data kendaraan, nomor rangka/mesin, dan status; tetapi tabel ini tidak memiliki foreign key langsung ke `id_transaksi`, `id_penjualan`, atau `id_pesanan`.

## 8. My Garage Integration

Status: **ADA dan sesuai target ownership final**.

`VerifiedCarOwnershipService::syncVehicleFromCompletedSale()` dipanggil di akhir handover. Service menolak sinkronisasi jika transaksi belum lunas, sale belum selesai, atau order belum `diserahkan`. Jika nomor rangka atau nomor mesin yang sama sudah dimiliki pelanggan, service mengaktifkan existing row; jika belum ada, service membuat satu `KendaraanPelanggan`.

Hal yang sudah dibuktikan test:

- Payment saja tidak membuat kendaraan My Garage.
- Handover lengkap membuat tepat satu kendaraan.
- Handover kedua tidak membuat duplicate ownership.
- Kendaraan manual tidak membuka akses fitur yang memerlukan verified purchase.

Gap: relasi historis langsung antara kendaraan My Garage dan transaksi/sale belum tersimpan; validasi dilakukan melalui pencocokan nomor rangka/mesin dan query kondisi completed sale.

## 9. Inventory Lifecycle

Ada dua pola inventory:

### Unit individual

```text
tersedia -> dibooking -> terjual
```

Checkout mengubah mobil menjadi `dibooking`. Rejection admin atau rejection payment dapat mengembalikan ke `tersedia`. Handover mengubah menjadi `terjual`.

### Inventory agregat

`vehicle_inventories` menyimpan `stock_total` dan `sold_units`. Reservation tidak disimpan sebagai angka tetap; dihitung dari booking aktif, order member aktif, dan direct sale aktif yang belum lunas/dibatalkan. Karena order dibatalkan atau diserahkan tidak lagi dihitung sebagai reservation, pelepasan reservasi terjadi secara derivatif.

```text
stock_total - sold_units - reservation_count = available stock
```

Saat handover, `VehicleInventoryService::recordSale()` menaikkan `sold_units` dan menulis `vehicle_inventory_histories`. Payment lunas belum menaikkan sold units; unit tetap dialokasikan sampai handover. Ini konsisten dengan target ownership final setelah serah-terima.

Risiko: reservation agregat tidak memiliki row/ID unit fisik tertentu. Untuk kendaraan yang membutuhkan VIN/nomor mesin aktual, assignment unit fisik perlu dirancang sebelum PDI final.

## 10. Authorization

Role yang tampak sesuai dengan implementasi sekarang:

- `admin`: approval order, reject, schedule, PDI, handover, dan payment management.
- `petugas`: tersedia sebagai actor domain untuk service/payment lama, tetapi route PDI/handover pembelian mobil berada di group admin.
- `pelanggan`: membuat checkout, melihat order miliknya, mengajukan pembayaran offline, dan melihat kendaraan/hasil yang diizinkan.

Member detail order hanya memuat relasi milik customer melalui pemeriksaan `id_pelanggan`. Route PDI, schedule, dan handover tidak berada di group member. Pola read-only member sudah didukung secara struktural, tetapi endpoint read-only khusus hasil PDI/jadwal belum dipisahkan sebagai API/route khusus.

Rekomendasi audit: sebelum implementasi, tetapkan apakah PDI seharusnya dikerjakan role `petugas` operasional atau tetap `admin`; saat ini route membatasi ke admin walaupun data menyimpan `id_petugas`.

## 11. Notifications

Infrastructure notifikasi sudah ada melalui model `Notifikasi`, `NotifikasiController`, dan `ServiceNotificationService`; ada juga badge admin dan endpoint menandai notifikasi telah dibaca.

Namun dari source yang diaudit:

- notifikasi pembayaran/pengerjaan yang lengkap terutama dipakai workflow servis;
- `PaymentService` tidak mengirim event khusus pembelian mobil member ketika status menjadi lunas;
- PDI mulai, PDI gagal/perlu koreksi, PDI lulus, pickup scheduled, vehicle ready, dan handover completed belum memiliki rangkaian notifikasi pembelian mobil yang konsisten.

Status: **infrastructure reusable, event coverage partial**.

## 12. Idempotency

Proteksi yang sudah ada:

- Database unique: nomor order, transaksi penjualan, penjualan, PDI per order, serah-terima per order, inventory key.
- `lockForUpdate()` pada order, mobil, inventory, gateway, transaksi, payment, dan handover di titik kritis.
- Midtrans event key dan existing `id_pembayaran` mencegah payment duplicate.
- `approve`, `reject`, dan `savePdi` memeriksa current status.
- Handover memakai `firstOrNew()` dan unique `id_pesanan`; ownership mencari nomor rangka/mesin sebelum create.
- Inventory record sale tidak boleh melewati `stock_total`.

Risiko/gap:

- Handover `firstOrNew()` masih perlu diuji terhadap retry bersamaan dan perubahan input pada row yang sudah ada.
- PDI memakai satu row dan menyimpan ulang checklist; belum ada immutable inspection history.
- Tidak ada foreign key ownership ke transaksi/sale sehingga audit duplicate lebih bergantung pada VIN/nomor mesin.
- Payment stage guard yang tidak dipanggil secara eksplisit perlu ditinjau.

## 13. Database/Schema Gap

Schema saat ini cukup untuk workflow minimal, tetapi belum cukup untuk target lengkap.

Kebutuhan yang belum memiliki tempat terstruktur:

- PDI started/completed timestamps terpisah.
- PDI item-level finding, result, severity, corrective action, foto, dan reinspection history.
- PDI approval/approved_by.
- Pickup location, pickup status, scheduled/confirmed timestamps, dan perubahan jadwal.
- Handover planned time versus actual time, location, evidence/signature/photo.
- Assignment unit fisik/VIN/nomor mesin ke order sebelum PDI.
- Direct ownership provenance ke `id_penjualan`/`id_transaksi`.
- Notification event history khusus post-payment purchase flow.

Kesimpulan schema: **PARTIAL**.

## 14. Test Coverage

Coverage yang sudah ditemukan:

- `MemberVehiclePurchaseFlowTest`: checkout, approval, invoice fragment, payment pending/verified, PDI gating, PDI gagal, handover, My Garage duplicate prevention.
- `MidtransSandboxStage11ATest`: Snap attempt, server settlement sync, duplicate notification, signature/gross amount checks, finish return, legacy gateway.
- `VehicleAggregateInventoryFlowTest`: aggregate reservation, handover sold count, rejection/payment failure release, stock adjustment history.
- `VerifiedCarOwnershipTest`: manual vehicle exclusion, payment-before-handover exclusion, ownership gating.
- `VehicleAvailabilityStockTest` dan `InventoryStage4ATest`: ketersediaan/inventory umum.
- Payment tests lain mencakup overpayment, pending, rejected payment, dan transaksi atomic.

Gap test:

- PDI route authorization antara admin/petugas belum diuji khusus.
- Belum ada test lifecycle PDI `sedang_diperiksa`, reinspection, temuan, atau history karena fitur belum ada.
- Belum ada test pickup schedule conflict, location, reschedule, atau reminder.
- Belum ada test notification event khusus pembelian mobil.
- Belum ada concurrency test untuk retry handover/PDI bersamaan.
- Belum ada test assignment VIN/nomor mesin aktual dari inventory agregat ke order.

## 15. Recommended Target Workflow

Target yang paling aman:

```text
CHECKOUT
  -> VERIFIKASI ADMIN
  -> PEMBAYARAN MENUNGGU
  -> PEMBAYARAN LUNAS / VERIFIED
  -> PDI BELUM DIPERIKSA
  -> PDI SEDANG DIPERIKSA
  -> PDI LULUS
  -> BELUM DIJADWALKAN
  -> DIJADWALKAN
  -> SIAP DIAMBIL
  -> DISERAHKAN
  -> SELESAI
  -> MY GARAGE OWNERSHIP VERIFIED
```

Jika ada temuan:

```text
PDI SEDANG DIPERIKSA
  -> ADA TEMUAN / PERLU PERBAIKAN
  -> INSPEKSI ULANG
  -> LULUS
```

Entry condition PDI: transaksi pembayaran `lunas`, payment record terverifikasi, order telah diverifikasi admin, dan unit/order belum dibatalkan atau diserahkan.

## 16. Required Changes

Belum diimplementasikan. Untuk fase berikutnya, perubahan utama yang kemungkinan diperlukan:

1. Menetapkan state machine formal dan transition guard tunggal.
2. Memisahkan status order, status PDI, dan status pickup/handover.
3. Menambah model history PDI/reinspection dan checklist item terstruktur.
4. Menambah data assignment unit fisik sebelum PDI jika inventory agregat dipakai.
5. Menyediakan read-only member view untuk status/hasil PDI dan jadwal pickup.
6. Menentukan actor operasional PDI secara eksplisit (`admin` atau `petugas`).
7. Menambah notification events pasca-pembayaran.
8. Memperketat dan menguji payment-stage guard pada semua payment entry point.
9. Menambah audit trail untuk schedule, PDI, handover, dan ownership.

## 17. Migration Requirement

**POSSIBLY / kemungkinan besar YES** untuk target lengkap.

Workflow minimal dapat memakai schema existing, tetapi kebutuhan PDI detail, reinspection, pickup lifecycle, evidence handover, dan ownership provenance memerlukan migration atau tabel tambahan. Tidak ada migration yang dibuat pada audit ini.

## 18. Risks

- PDI sekarang dapat ditimpa karena belum memiliki history/reinspection.
- Status `siap_diambil` dan jadwal pickup belum memiliki lifecycle terpisah.
- Inventory agregat belum memilih unit fisik spesifik, padahal PDI membutuhkan VIN/nomor mesin.
- Payment stage guard perlu audit call-site agar offline, Midtrans, admin, dan retry memakai aturan sama.
- Notifikasi member dapat tertinggal walaupun status backend sudah maju.
- Ownership saat ini aman secara bisnis, tetapi provenance database langsung belum tersedia.
- Handover merupakan titik kritis yang sekaligus mengubah sale, inventory, order, dan My Garage sehingga perlu concurrency test.

## 19. Implementation Order

Urutan yang direkomendasikan setelah audit disetujui:

1. Finalisasi state machine dan aturan actor.
2. Kunci entry condition payment -> PDI dan transition guard.
3. Rancang schema PDI/reinspection serta assignment unit fisik.
4. Implementasi PDI service, checklist, history, dan approval.
5. Implementasi pickup schedule/status dan read-only member display.
6. Implementasi handover evidence dan finalization transaction.
7. Finalisasi ownership/My Garage dengan provenance yang dapat diaudit.
8. Tambahkan notifications.
9. Tambahkan idempotency/concurrency tests dan regression suite.
10. Jalankan migration dan UAT hanya setelah design disetujui.

## 20. Final Readiness

**READY WITH MIGRATION** untuk mulai perancangan implementasi.  
**IMPLEMENTATION: NOT STARTED** untuk scope audit ini.

### Final output

Payment Flow: checkout -> admin verification -> payment pending/verified -> transaksi `lunas`; Midtrans server status menjadi source of truth dan idempotency dasar tersedia.

PDI Existing: **PARTIAL**.

Pickup Existing: **PARTIAL**.

Handover Existing: **YES**.

My Garage Ownership Integration: ownership resmi hanya dibuat setelah payment lunas, PDI lulus, handover sukses, sale selesai, dan order `diserahkan`.

Inventory Assignment: unit individual memakai `dibooking` lalu `terjual`; aggregate inventory memakai reservation turunan dan menaikkan `sold_units` saat handover. Unit fisik spesifik belum ditetapkan oleh inventory agregat.

Notification Support: **PARTIAL**; infrastructure ada, event post-payment car purchase belum lengkap.

Schema Sufficient: **PARTIAL** untuk workflow minimal, **NO** untuk target PDI/pickup/handover detail penuh.

Migration Required: **POSSIBLY / kemungkinan besar YES**.

Main Risks: PDI overwrite/no history, pickup state minimal, aggregate VIN assignment, payment-stage guard call-site, notification gap, dan concurrency handover.

Recommended Implementation Order: state machine -> payment gate -> schema PDI/unit -> PDI -> pickup -> handover -> ownership -> notification -> idempotency tests -> migration/UAT.

Tests: purchase, payment, Midtrans, inventory aggregate, ownership, member vehicle, dan payment regression sudah tersedia; gap utama berada pada PDI lifecycle detail, pickup lifecycle, notification, concurrency, dan VIN assignment.

FINAL STATUS: **READY WITH MIGRATION**.
