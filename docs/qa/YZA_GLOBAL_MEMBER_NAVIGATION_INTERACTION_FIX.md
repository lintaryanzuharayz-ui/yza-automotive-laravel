# YZA Global Member Navigation & Interaction Fix

Tanggal verifikasi: 2026-10-12

## Ruang lingkup

Perbaikan ini menangani pemulihan interaksi frontend global dan navigasi member tanpa mengubah password, user, database produksi, schema, migration, pembayaran, penjadwalan, atau personel.

## Temuan dan perbaikan

- GET `/login` sebelumnya berada di dalam middleware `guest`. Saat member yang sudah login membuka URL tersebut, middleware default mengarahkannya ke `/`. Route GET sekarang tetap dapat dipanggil dan `AuthController` mengarahkan role aktif ke dashboard yang benar: pelanggan ke Member Center, admin ke dashboard admin, dan petugas ke dashboard petugas. POST login dan registrasi tetap dilindungi middleware `guest`.
- Navbar publik sekarang membedakan CTA guest dan member aktif. Guest tetap mendapat `MASUK MEMBER` dan `BUAT MEMBER`; member aktif mendapat `MEMBER CENTER` dan tidak lagi mendapat CTA guest yang bisa membawa alur kembali ke login/register.
- Script booking diberi guard inisialisasi per halaman. Perbaikan sebelumnya juga dipertahankan: tidak ada `MutationObserver` rekursif yang menulis ulang total ringkasan, dan kendaraan dari query `kendaraan` tetap dipreload.
- Audit global tidak menemukan service worker, listener history, `location.reload`, atau handler dokumen yang membatalkan klik navigasi. Listener yang ada terbatas pada pembukaan/penutupan menu dan overlay drawer.

## Verifikasi otomatis

Tes terarah mencakup:

- guest dapat membuka `/login`;
- member aktif `/login` diarahkan ke Member Center;
- admin dan petugas diarahkan ke dashboard masing-masing;
- landing guest/member memiliki CTA yang sesuai;
- halaman booking mempertahankan konteks `from=garage`, preselect kendaraan, dan tidak memuat pola freeze lama atau rewrite URL.

Perintah validasi:

```text
php artisan optimize:clear
php artisan view:cache
php artisan test --filter="AuthNavigationHardeningTest|BookingServisStage4CTest"
git diff --check
```

## Verifikasi browser

Screenshot yang diberikan menunjukkan halaman landing dan booking telah merender. Jendela Edge eksternal tidak tersedia untuk otomasi UI pada sesi Codex ini, sehingga klik manual end-to-end di Edge dicatat sebagai **belum diverifikasi manual**. Verifikasi otomatis dan inspeksi source tetap dilakukan.
