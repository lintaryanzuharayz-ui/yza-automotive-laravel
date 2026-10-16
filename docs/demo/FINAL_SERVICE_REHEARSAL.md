# LAPORAN FINAL GLADI BERSIH PRESENTASI (LIVE DEMO REHEARSAL)
## YZA AUTOMOTIVE SERVICE & SPAREPART MANAGEMENT SYSTEM

**Tanggal Pelaksanaan:** 11 Oktober 2026  
**Status Kesiapan:** **READY FOR DEMO (100% TERVERIFIKASI & TERISOLASI)**  
**Project Path:** `C:\xampp\htdocs\yza_automotive_laravel`  
**Active Production DB:** `yza_automotive_laravel` (Utuh & Tidak Tersentuh)  
**Demo Target DB:** `yza_automotive_laravel_demo` (Terisolasi Sempurna)  
**Legacy Native DB:** `yza_automotive` (READ-ONLY, Utuh)

---

## 1. RINGKASAN EKSEKUTIF GLADI BERSIH

Gladi bersih presentasi servis kendaraan telah dijalankan dari hulu ke hilir menggunakan lingkungan browser dan database demo terisolasi. Seluruh skenario inti pembuktian inventori dan servis berhasil:

1. **Stok Awal:** Oli Mesin Fully Synthetic 0W-20 1L (`YZA-OIL-SYN1L`) berawal dari **0 botol**.
2. **Penerimaan Stok Masuk:** Dokumen `SM-20261011225354-375` difinalisasi dengan penambahan **+20 botol** (@ Rp 125.000). Stok menjadi **20 botol**.
3. **Pemesanan Servis Member:** Member VIP demo memesan servis ganti oli pada kendaraan terverifikasi Toyota GR Supra (`B 1984 YZA`). Kode Booking: **`BSV-202610-5783A2E6`**.
4. **Work Order & Draft Penggunaan Oli:** Admin menerima antrean booking dan menerbitkan Work Order **`SRV-202610-9627CF57`**. Menambahkan jasa servis Rp 75.000 dan pemakaian 1 botol oli status draft.
5. **Integritas Stok Saat Draft:** Stok fisik oli mesin terbukti **TETAP 20 botol** selama draft belum difinalisasi.
6. **Finalisasi Servis & Pengurangan Stok Atomik:** Work Order difinalisasi, tagihan dibuat otomatis **`TRX-202610-A13C72C2`** senilai **Rp 240.000**, dan stok oli berkurang secara atomik dari **20 menjadi 19 botol**.
7. **Pencatatan Pembayaran:** Pembayaran tunai demo dicatat dan diverifikasi. Transaksi berstatus **`lunas`**, Work Order berstatus **`selesai`**.
8. **Penerbitan Invoice & Riwayat Member:** Invoice resmi dapat diakses di `/admin/invoice/servis/19970`, dan riwayat servis pelanggan langsung muncul di portal member `/pelanggan/servis`.

---

## 2. HASIL SETIAP LANGKAH SIMULASI BROWSER

| No | Tahap Simulasi | Role | Target URL / Aksi | Hasil Verifikasi | Bukti Visual (Screenshot) |
|---|---|---|---|---|---|
| **1** | Login Administrator | Admin | `/login` $\to$ `/admin/dashboard` | Login berhasil, diarahkan ke Command Center | `docs/demo/screenshots/01_admin_dashboard.png` |
| **2** | Periksa Katalog Sparepart | Admin | `/admin/sparepart` | Katalog 16 sparepart tampil, stok oli = 0 | `docs/demo/screenshots/02_sparepart_catalog_zero_stock.png` |
| **3** | Form Stok Masuk | Admin | `/admin/stok-masuk/1` | Rincian draft 20 botol oli @ Rp 125.000 | `docs/demo/screenshots/03_stok_masuk_draft.png` |
| **4** | Finalisasi Stok Masuk | Admin | `/admin/stok-masuk/1` (Finalize) | Status berubah menjadi `Selesai (Diterima)` | `docs/demo/screenshots/04_stok_masuk_finalized.png` |
| **5** | Pembuktian Stok 20 Botol | Admin | `/admin/sparepart` | Stok `YZA-OIL-SYN1L` bertambah menjadi 20 | `docs/demo/screenshots/05_sparepart_stock_20.png` |
| **6** | Riwayat Mutasi Masuk | Admin | `/admin/inventory/riwayat` | Mutasi stok tercatat `+20` botol | `docs/demo/screenshots/06_riwayat_mutasi_masuk.png` |
| **7** | Login Member Demo | Member | `/login` $\to$ `/pelanggan` | Login berhasil, VIP Member aktif | `docs/demo/screenshots/07_member_dashboard.png` |
| **8** | Booking Servis Ganti Oli | Member | `/pelanggan/booking-servis/create` | Booking berhasil: `BSV-202610-5783A2E6` | `docs/demo/screenshots/08_member_booking_success.png` |
| **9** | Konfirmasi Booking | Admin | `/admin/booking-servis` | Booking diterima oleh bengkel | `docs/demo/screenshots/09_booking_confirmed.png` |
| **10** | Terbitkan Work Order | Admin | `/admin/servis/create` $\to$ `/admin/servis/1` | Work Order diterbitkan: `SRV-202610-9627CF57` | `docs/demo/screenshots/10_work_order_created.png` |
| **11** | Tambah Jasa & Draft Oli | Admin | `/admin/servis/1` | Jasa Rp 75.000 & 1 botol oli draft ditambahkan | `docs/demo/screenshots/11_work_order_with_draft_oil.png` |
| **12** | Bukti Stok Draft | Admin | `/admin/sparepart` | **Stok terbukti tetap 20 botol** | `docs/demo/screenshots/12_stock_still_20_during_draft.png` |
| **13** | Finalisasi Work Order | Admin | `/admin/servis/1` (Finalize) | Work order selesai, tagihan Rp 240.000 terbit | `docs/demo/screenshots/13_work_order_finalized.png` |
| **14** | Pembuktian Stok Berkurang | Admin | `/admin/sparepart` | **Stok berkurang dari 20 menjadi 19 botol** | `docs/demo/screenshots/14_stock_decreased_to_19.png` |
| **15** | Riwayat Mutasi Keluar | Admin | `/admin/inventory/riwayat` | Mutasi tercatat `-1` botol, sisa saldo 19 | `docs/demo/screenshots/15_riwayat_mutasi_keluar_19.png` |
| **16** | Pembayaran Servis | Kasir | `/admin/pembayaran` | Bayar tunai demo Rp 240.000 $\to$ status `lunas` | `docs/demo/screenshots/16_payment_paid_lunas.png` |
| **17** | Penerbitan Invoice | Admin | `/admin/invoice/servis/19970` | Invoice cetak resmi berstatus Lunas | `docs/demo/screenshots/17_service_invoice_lunas.png` |
| **18** | Riwayat Servis Member | Member | `/pelanggan/servis` | Riwayat servis Toyota GR Supra tampil Lunas | `docs/demo/screenshots/18_member_service_history.png` |

---

## 3. PEMBUKTIAN INTEGRITAS DATABASE UTAMA

Pengujian transaksional dibuktikan **100% terisolasi** tanpa ada perubahan row count pada database utama maupun database legacy native:

```text
DATABASE AUDIT MATRIX:
+-------------------------------+---------------------+--------------------+--------------------+
| Entitas Data                  | yza_automotive_laravel | yza_automotive_demo| yza_automotive (Native) |
+-------------------------------+---------------------+--------------------+--------------------+
| sparepart (Katalog)           | 16 baris (TETAP)    | 16 baris           | 1 baris (READ-ONLY)|
| transaksi                     | 12 baris (TETAP)    | 2 baris (Demo)     | N/A                |
| servis (Work Orders)          | 6 baris (TETAP)     | 1 baris (Demo)     | N/A                |
| booking_servis                | 5 baris (TETAP)     | 1 baris (Demo)     | N/A                |
| stok_masuk                    | 9 baris (TETAP)     | 1 baris (Demo)     | N/A                |
+-------------------------------+---------------------+--------------------+--------------------+
Status Database Utama: TIDAK ADA DATA RIIL YANG BERUBAH MAUPUN TERHAPUS.
```

---

## 4. PENJELASAN AUTOMATED TEST YANG SKIPPED

Pada eksekusi automated regression suite (321 test lulus, 1 skipped):
- **File Test:** `tests/Feature/YzaPointsStage10BTest.php`
- **Metode:** `test_legacy_customer_without_user_is_not_a_member()`
- **Pesan:** `Tidak ada pelanggan legacy tanpa user pada baseline.`
- **Penyebab Skip:** Test ini dirancang untuk memvalidasi edge case migrasi pelanggan lama PHP native yang belum memiliki user account. Karena pada database testing baseline semua entitas pelanggan telah memiliki relasi user aktif, guard check fixture melewatkan pengujian ini (`markTestSkipped`) sesuai rancangan arsitektur.
- **Dampak:** **Nol (Zero Impact)** terhadap fungsionalitas live servis dan sparepart.

---

## 5. REKAYASA & PERBAIKAN SELAMA GLADI BERSIH

1. **Aktivasi Membership Member Demo:**
   - Ditemukan bahwa saat login sebagai `member.demo@yza.local`, middleware `MemberAccessService` memvalidasi status keanggotaan VIP.
   - Ditambahkan seeding otomatis `App\Models\Membership` dengan status `active` pada `IsolatedDemoDatabaseSeeder.php` sehingga member langsung dapat mengakses menu Garasi dan Booking Servis tanpa terhambat aktivasi fee gateway.
2. **Perlindungan Kredensial & Git Safety:**
   - File konfigurasi demo `.env.demo`, backup `.env.*.backup`, serta file-file session PHP ditambahkan ke `.gitignore` untuk mencegah kebocoran kredensial ke repository.
3. **Restorasi Otomatis Environment:**
   - Setelah gladi bersih selesai, script `switch-to-main.bat` dijalankan untuk mengembalikan konfigurasi `.env` ke database utama `yza_automotive_laravel`. Cache konfigurasi, route, dan view dibersihkan secara otomatis.

---

## 6. PANDUAN MENGULANG DEMO PRESENTASI KEPADA DOSEN

### Langkah 1: Aktifkan Mode Demo
Buka terminal / Command Prompt di folder project dan jalankan:
```cmd
.\switch-to-demo.bat
```
*Script ini mencadangkan file konfigurasi utama, mengarahkan koneksi ke database demo, dan membersihkan cache Laravel.*

### Langkah 2: Reset Data Demo ke Kondisi Awal (Opsional)
Jika ingin mengulang demo dari kondisi stok 0 dan belum ada booking:
```cmd
C:\xampp\php\php.exe artisan db:seed --class=IsolatedDemoDatabaseSeeder
```

### Langkah 3: Buka Browser & Ikuti Urutan Presentasi
1. Buka `http://127.0.0.1:8000/login`
2. **Login Admin:** `admin.demo@yza.local` (Password: `DemoYZA@2026!`)
3. Buka `/admin/sparepart`, perlihatkan stok oli = 0.
4. Buka `/admin/stok-masuk/1`, klik **Finalisasi & Terima Stok Fisik**.
5. Buka kembali `/admin/sparepart`, buktikan stok oli menjadi **20 botol**.
6. Logout admin.
7. **Login Member:** `member.demo@yza.local` (Password: `DemoYZA@2026!`)
8. Buka menu Booking Servis (`/pelanggan/booking-servis/create`), pilih mobil Toyota Supra `B 1984 YZA`, pilih servis Ganti Oli, kirim booking.
9. Logout member.
10. **Login Admin kembali:**
    - Terima booking di `/admin/booking-servis`.
    - Mulai Work Order.
    - Masukkan hasil pemeriksaan & jasa servis Rp 75.000.
    - Tambahkan pemakaian 1 botol oli.
    - **Tunjukkan di tab terpisah `/admin/sparepart` bahwa stok masih 20 botol.**
    - Klik **Finalisasi Servis**.
    - **Buktikan bahwa stok oli langsung berkurang menjadi 19 botol.**
11. Buka tagihan di menu Pembayaran (`/admin/pembayaran`), catat pembayaran tunai demo Rp 240.000.
12. Klik **Lihat Invoice**, tampilkan invoice resmi berstatus Lunas.
13. Logout admin, login kembali sebagai member, perlihatkan riwayat servis di portal pelanggan yang sudah berstatus Lunas & Selesai.

### Langkah 4: Kembalikan ke Mode Normal Setelah Presentasi
Setelah sesi presentasi selesai:
```cmd
.\switch-to-main.bat
```

---

## 7. WAKTU PENYELESAIAN SIMULASI

- **Durasi Alur Penuh (End-to-End):** Sekitar **4 - 6 menit** saat presentasi langsung di depan dosen.
- **Hasil Pengujian Otomasi:** 321 passed, 1 skipped, 1058 assertions.
- **Tingkat Kesiapan Presentasi:** **100% SIAP DAN STABIL.**
