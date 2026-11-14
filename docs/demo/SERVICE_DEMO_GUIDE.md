# PANDUAN LENGKAP DEMO SISTEM SERVIS YZA AUTOMOTIVE
## Panduan Presentasi Dosen & Penguji Akademik

---

### 1. CARA MENJALANKAN APLIKASI DALAM MODE DEMO TERISOLASI

Aplikasi YZA Automotive dilengkapi sistem isolasi database otomatis agar demonstrasi tidak mengubah atau merusak database bisnis utama.

1. Buka Command Prompt / PowerShell di folder project:
   ```cmd
   cd C:\xampp\htdocs\yza_automotive_laravel
   ```
2. Jalankan script launcher mode demo:
   ```cmd
   switch-to-demo.bat
   ```
   *Script ini otomatis mengalihkan koneksi ke database `yza_automotive_laravel_demo` dan membersihkan cache.*
3. Jalankan web server Laravel (jika belum berjalan):
   ```cmd
   C:\xampp\php\php.exe artisan serve
   ```
   Aplikasi dapat diakses pada browser di: `http://127.0.0.1:8000`

---

### 2. CARA MEMVERIFIKASI KONEKSI DATABASE DEMO

Pastikan sistem berada pada database demo sebelum memulai presentasi:
```cmd
C:\xampp\php\php.exe -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo 'DATABASE AKTIF: ' . Illuminate\Support\Facades\DB::connection()->getDatabaseName() . PHP_EOL;"
```
Output yang benar:
```text
DATABASE AKTIF: yza_automotive_laravel_demo
```
> **Proteksi Keamanan Fail-Closed:**  
> Jika script seeder atau skenario demo dijalankan pada database selain demo, sistem otomatis memicu `RuntimeException` untuk mencegah modifikasi data utama.

---

### 3. AKUN DEMO TERSEDIA (TANPA MEMAKAI AKUN BISNIS ASLI)

| Role | Username / Email | Nama Tampilan | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator Bengkel** | `admin.demo@yza.local` | Budi Santoso (Admin Demo) | Akses penuh inventori, booking, work order, dan pembayaran kasir |
| **Teknisi Bengkel** | `petugas.demo@yza.local` | Rian Hidayat (Teknisi Demo) | Pengerjaan servis, diagnosis, dan input suku cadang |
| **Member VIP (Verified Car)** | `member.demo@yza.local` | Lintar YZA Member (Demo VIP) | Pemilik sah Toyota GR Supra (`B 1984 YZA`), booking servis online |

*Password demo terkonfigurasi pada file kredensial demo lokal dan tidak pernah disimpan dalam repository publik.*

---

### 4. URUTAN HALAMAN & SKENARIO PRESENTASI DOSEN

#### Langkah 1: Pengadaan & Stok Masuk (Admin)
1. Buka browser: `http://127.0.0.1:8000/login`
2. Login sebagai Admin Demo (`admin.demo@yza.local`).
3. Buka menu **Sparepart & Inventori** (`http://127.0.0.1:8000/admin/sparepart`).  
   - Tunjukkan **16 katalog suku cadang** dan **9 kategori**.
   - Tunjukkan saldo awal **Oli Mesin 1L (`YZA-OIL-SYN1L`)** adalah 0.
4. Buka menu **Stok Masuk** $\to$ Klik **"Buat Draft"** (`/admin/stok-masuk/create`).
5. Isi catatan pengiriman, simpan draft.
6. Pada form review, pilih sparepart `YZA-OIL-SYN1L`, isi kuantitas `20` botol, klik **"Tambahkan"**.
7. Klik tombol hijau **"Finalisasi & Terima Stok Fisik"**.
8. Buka kembali `/admin/sparepart` $\to$ Buktikan saldo fisik kini **tepat 20 botol**.
9. Buka `/admin/inventory/riwayat` $\to$ Tunjukkan audit trail penambahan stok (+20).

#### Langkah 2: Booking Servis Online (Member Terverifikasi)
1. Logout admin, buka `http://127.0.0.1:8000/login`.
2. Login sebagai Member Demo (`member.demo@yza.local`).
3. Masuk ke menu **Booking Servis** $\to$ **Buat Booking Baru** (`/pelanggan/booking-servis/create`).
4. Tunjukkan validasi kepemilikan: Mobil **Toyota GR Supra (Nopol: `B 1984 YZA`)** otomatis muncul karena terverifikasi dari riwayat serah terima YZA.
5. Pilih jenis servis: **Ganti Oli**.
6. Pilih jadwal besok jam 09:00, masukkan keluhan: *"Ganti oli berkala 10.000 KM"*.
7. Klik **"Kirim Booking"** $\to$ Kode booking (contoh: `BSV-...`) langsung terbit dengan status *Menunggu*.

#### Langkah 3: Konfirmasi & Pembuatan Work Order (Admin/Petugas)
1. Login kembali sebagai Admin Demo.
2. Buka menu **Antrian Booking** (`/admin/booking-servis`).
3. Buka booking milik member $\to$ Klik tombol **"Terima Booking"**.
4. Klik tombol **"Mulai Pengerjaan"** (buat Work Order).
5. Pada halaman Work Order (`/admin/servis/{id}`):
   - Masukkan hasil pemeriksaan fisik teknisi $\to$ Klik simpan (status beralih ke *Dikerjakan*).
   - Tambahkan biaya jasa: *"Jasa Ganti Oli Mesin & Tune Up"* sebesar **Rp 75.000**.
   - Tambahkan pemakaian sparepart: Pilih `YZA-OIL-SYN1L`, kuantitas `1` botol (Rp 165.000).
   - **Tunjukkan Keunggulan Integritas:** Selama masih dalam draft Work Order, stok fisik di master sparepart **tetap 20 botol** (tidak terpotong prematur).

#### Langkah 4: Pembuktian Pemotongan Stok Atomik (20 $\to$ 19 Botol)
1. Pada halaman Work Order, klik tombol **"Finalisasi Servis & Buat Tagihan"**.
2. Buka halaman master sparepart (`/admin/sparepart`).
3. **Buktikan Langsung di Hadapan Dosen:** Stok oli mesin otomatis terpotong menjadi **19 botol**.
4. Buka `/admin/inventory/riwayat` $\to$ Tunjukkan baris mutasi stok baru: jenis *Servis*, perubahan *-1 botol*, saldo `20` $\to$ `19`.
5. Tunjukkan perhitungan tagihan akurat:
   $$\text{Jasa (Rp 75.000)} + \text{Oli Mesin (Rp 165.000)} = \mathbf{Rp\ 240.000}$$

#### Langkah 5: Pembayaran & Invoice Kasir
1. Pada halaman Work Order, klik tombol **"+ Catat Pembayaran"** (atau buka transaksi servis terkait).
2. Masukkan jumlah bayar **Rp 240.000**, metode **Tunai**, klik simpan.
3. Transaksi otomatis berstatus **Lunas**, dan status Work Order berubah menjadi **Selesai**.
4. Klik tombol **"Lihat Invoice"** $\to$ Invoice resmi bengkel YZA Automotive dengan rincian biaya terbuka rapi.

#### Langkah 6: Verifikasi Portal Member
1. Login kembali sebagai Member Demo.
2. Buka **Riwayat Servis** (`/pelanggan/servis`).
3. Tunjukkan bahwa pengerjaan servis dan status lunas langsung tampil pada dashboard member secara real-time.

---

### 5. CARA MENGULANG SKENARIO DEMO (RESET BERSIH)

Jika ingin mengulang presentasi dari awal:
1. Jalankan perintah seeding ulang database demo:
   ```cmd
   C:\xampp\php\php.exe artisan db:seed --class=IsolatedDemoDatabaseSeeder --database=demo
   ```
   *Perintah ini otomatis mengembalikan saldo sparepart menjadi 0 dan membersihkan transaksi demo tanpa menyentuh database utama.*

---

### 6. CARA KEMBALI KE DATABASE UTAMA

Setelah demonstrasi selesai:
```cmd
switch-to-main.bat
```
Aplikasi akan kembali menggunakan database `yza_automotive_laravel` secara normal.

---

### 7. TROUBLESHOOTING

| Masalah | Solusi Cepat |
| :--- | :--- |
| **Apache / MySQL mati di XAMPP** | Buka XAMPP Control Panel $\to$ Klik Start pada Apache dan MySQL. |
| **Database salah saat login** | Jalankan `switch-to-demo.bat` lalu `C:\xampp\php\php.exe artisan config:clear`. |
| **CSS/Asset tidak muncul** | Pastikan URL browser menggunakan `http://127.0.0.1:8000`. |
| **Pesan CSRF / Session Expired** | Refresh browser atau login ulang melalui `/login`. |
