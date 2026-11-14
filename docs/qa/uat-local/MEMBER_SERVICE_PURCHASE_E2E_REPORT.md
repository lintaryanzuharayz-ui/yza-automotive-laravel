# Member Service Verification & Vehicle Purchase E2E

Tanggal: 15 Oktober 2026

## Session recovery and Member Service

- Work Order `SRV-202610-ECD72CB1`: selesai.
- Mekanik utama: Rian Hidayat.
- Pendamping: tidak ada, sehingga tidak ditampilkan.
- Total servis: Rp150.000.
- Transaksi: `TRX-202610-B1BF99A3`, status `lunas`.
- Logout mekanik melalui tombol UI `Keluar` dengan POST/CSRF: **PASS**.
- Halaman `/login` setelah logout: **PASS** — tampil sebagai guest.
- Login `memberdemo` melalui form browser: **PASS** — Member Center terbuka.
- Riwayat servis Member Center: **PASS** — `SRV-202610-ECD72CB1`, status selesai, Rian Hidayat sebagai mekanik utama, pendamping tidak ada, `TRX-202610-B1BF99A3`, total Rp150.000, dan pembayaran Lunas tampil pada detail.

## Positive CSRF

Finalisasi Work Order dan pencatatan pembayaran berhasil melalui form UI resmi dengan session/CSRF sah, dan database UAT mengonfirmasi transaksi serta status `lunas`. Ini merupakan positive CSRF/business mutation evidence.

Negative checks sebelumnya tetap:

- tanpa token: 419
- token invalid: 419
- token lintas session: 419
- token valid dengan kredensial invalid: 302 tanpa perubahan bisnis

## CUA session resolution

Semua tab `Codex In-app Browser` menggunakan session/cookie yang sama. Parameter `sessionName` pada IAB tidak membuat browser context baru. Shell petugas yang sedang aktif tidak menampilkan tombol logout pada halaman dashboard, dan navigasi ke `/login` mengembalikan dashboard mekanik.

Sebagai pemeriksaan authorization, session mekanik mencoba membuka `/pelanggan/member` dan menerima **403 Forbidden**. Setelah logout melalui UI, session member baru dapat digunakan tanpa mengubah middleware atau authorization.

Root cause: shell petugas/mekanik tidak menyediakan tombol logout, sementara route logout hanya menerima POST ber-CSRF. Perbaikan minimal menambahkan form logout ke shell tersebut dan styling header yang kompatibel.

## Vehicle purchase E2E

Katalog Member menampilkan Toyota GR Supra 2024 warna Supra Red, tersedia, seharga Rp2.350.000.000. Checkout resmi berhasil membuat order `ORD-202610-B8358FB0`. Super Admin kemudian menyetujui order melalui UI dan status berubah menjadi `Dikonfirmasi`.

Upaya pembayaran online resmi melalui Midtrans berhenti dengan pesan aplikasi **“Midtrans Sandbox gagal membuat pembayaran.”** Opsi transfer offline meminta bukti transfer dan menyatakan dana harus masuk rekening resmi; tidak ada bukti atau dana nyata yang digunakan.

Status: **BLOCKED pada pembayaran provider**, bukan kegagalan checkout. PDI, pickup, handover, My Garage, dan Owner final tidak dilanjutkan karena pembayaran belum tervalidasi. Tidak ada status pembayaran, PDI, handover, atau ownership yang diubah melalui query.

## Sparepart

Work Order existing tidak menggunakan sparepart. Skenario browser terpisah untuk fitment, approval, konsumsi stok, dan histori pemakaian belum dijalankan.

Status: **PENDING BROWSER UAT**. Automated fitment/regression coverage tetap lulus, tetapi itu bukan pengganti browser E2E.

## Evidence and regression

Tidak ada screenshot file yang diklaim karena backend CUA hanya menampilkan screenshot inline pada sesi ini. Evidence browser yang benar-benar terlihat: halaman guest login setelah logout, Member Center, riwayat servis/detail, katalog/detail mobil, checkout/order, persetujuan admin, dan pesan kegagalan Midtrans.

Perubahan source untuk menyediakan logout wajib karena bug UI terbukti. Focused regression: **11 passed, 57 assertions**. Full regression setelah perubahan: **407 passed, 0 failed, 0 skipped, 1.488 assertions**.

Tidak ada bug baru yang terbukti dan tidak ada perubahan database operasional/native.
