# YZA AUTOMOTIVE — SIDEBAR SCROLL RESULT

Tanggal: 6 November 2026. Scope: sidebar backoffice non-owner; tanpa perubahan permission, menu, KPI, payment, atau alur bisnis.

## Audit dan root cause

Komponen aktual adalah `resources/views/components/admin-sidebar.blade.php`; perilaku collapse dan penyimpanan posisi ada di `resources/views/layouts/app.blade.php`, sedangkan drawer/focus trap ada di `public/assets/js/responsive-foundation.js`. CSS dimuat berurutan: app.css, admin-portal.css, stylesheet halaman, responsive-foundation.css.

Wheel sidebar desktop pada baseline 1280×720 **sudah berjalan**: scrollTop 0 → 1440 dan scrollY dokumen tetap 0. Gejala wheel desktop yang dilaporkan tidak berhasil direproduksi, sehingga perubahan ini tidak diklaim membuktikan penyebab tunggal gejala tersebut.

Konflik yang berhasil ditemukan dan direproduksi: admin-portal.css mengunci overflow pada elemen html, sementara responsive-foundation.css mengubah shell/main menjadi height auto dan overflow visible untuk scroll dokumen. Akibatnya wheel di area utama tidak bergerak. Setelah aturan root diselaraskan, wheel utama mengubah scrollY menjadi 720 tanpa mengubah posisi nav. Ada pula beberapa deklarasi tinggi sidebar lama yang ditimpa berulang, area scroll belum memiliki fokus keyboard eksplisit, dan drawer belum mengunci scroll dokumen.

Pernyataan awal mengenai backdrop berada di atas sidebar bukan penyebab terkonfirmasi: audit cascade menunjukkan sidebar sebelumnya z-index 220, backdrop 210. Nilai sidebar kini dipertegas 270 sebagai penguatan layering, bukan bukti bug overlay sebelumnya.

## Implementasi

- `public/assets/css/responsive-foundation.css`: root dokumen non-owner dapat scroll; sidebar fixed dengan fallback 100vh lalu 100dvh, min-height 0 dan overflow hidden; brand tidak menyusut; nav memakai flex 1 1 0%, min-height 0 dan overflow-y auto; overscroll contain, touch-action pan-y, scrollbar gutter stabil, ruang bawah safe-area, serta outline fokus. Drawer terbuka mengunci scroll body.
- `resources/views/components/admin-sidebar.blade.php`: area nav diberi data-admin-sidebar-scroll, tabindex 0, role region dan label aksesibel.
- `public/assets/js/responsive-foundation.js`: fokus dikembalikan sebelum drawer disembunyikan/inert, aria-hidden disinkronkan, dan body lock dilepas ketika drawer ditutup atau layar menjadi desktop.

Struktur sebelum/sesudah tetap: aside fixed → brand + nav-scroll → nav → tautan. Tidak ada menu yang dihapus atau dipindahkan; perubahan mempertegas batas ukuran dan interaksinya. Owner memakai shell tersendiri dan dikecualikan oleh selector perbaikan root/sidebar.

## Bukti interaksi browser

Pengujian menggunakan browser IAB pada localhost, zoom default 100%, fixture sementara yang merender **komponen sidebar aktual** dengan role super_admin sintetis tanpa akses data pelanggan; memuat CSS aktual, script layout aktual, dan script responsive aktual. Halaman utama fixture berisi konten panjang. Fixture dihapus setelah QA. Ini bukan klaim pengujian sesi login semua role atau seluruh halaman aplikasi.

Input wheel dikirim melalui API scroll browser pada koordinat sidebar; tidak pernah mengatur scrollTop secara programatis. Nilai scrollTop dibaca hanya untuk memverifikasi hasil input.

| Viewport | Wheel dari atas ke bawah | Menu terakhir terlihat | Scroll dokumen saat nav discroll | Overflow horizontal |
|---|---:|---|---:|---|
| 320×568 | 0 → 1463.2 | Ya | 0 | Tidak |
| 390×844 | 0 → 1179.2 | Ya | 0 | Tidak |
| 768×900 | 0 → 1123.2 | Ya | 0 | Tidak |
| 1024×768 | 0 → 1294.4 | Ya | 0 | Tidak |
| 1280×720 | 0 → 1479.2 | Ya | 0 | Tidak |
| 1366×768 | 0 → 1431.2 | Ya | 0 | Tidak |
| 1440×900 | 0 → 1299.2 | Ya | 0 | Tidak |
| 1920×1080 | 0 → 1119.2 | Ya | 0 | Tidak |

Hasil lain:

- Desktop wheel: PASS pada fixture, atas sampai bawah, semua delapan viewport.
- Drag scrollbar: PASS, thumb ditarik dari y220 ke y620 pada x218, nav mencapai 1479.2; dokumen tetap pada 720.
- Keyboard: PASS, fokus pada region kemudian PageDown menghasilkan 476.8; End mencapai 1479.2; dokumen tetap 0.
- Main independen: PASS, wheel di main menghasilkan scrollY 720 dengan posisi nav tetap 1246.4; wheel nav tidak menggerakkan main.
- Collapse/expand: PASS, lebar rail 72px, wheel rail mencapai 1246.4; expand kembali dan menu terbawah bisa diakses.
- Menu terakhir: Profil Admin terlihat dan benar-benar diklik; mengarah ke login karena fixture tidak memiliki sesi autentikasi. Back memulihkan scrollTop 1479.2. Ini memverifikasi klik/navigasi, bukan otorisasi halaman profil.
- Drawer mobile: PASS untuk buka, scroll wheel, Escape, tombol tutup, dan resize ke desktop. Escape mengembalikan fokus ke pemicu; aria-hidden true ketika tertutup, body lock terlepas. Pada viewport mobile yang diuji, body terkunci ketika drawer terbuka.
- Touchpad fisik dan gesture sentuh perangkat nyata: BELUM DIUJI; input wheel otomatis tidak dianggap bukti pengujian perangkat tersebut.
- Zoom 125%, 150%, 200%: BELUM DIUJI. Pengujian lintas ukuran di atas tidak diklaim menggantikan browser zoom.
- Sesi login dashboard nyata semua role: BELUM DIUJI dalam matriks ini; render dan navigasi admin dicakup focused tests.

Bukti visual fixture: [sidebar-scroll-desktop.png](sidebar-scroll-desktop.png), memperlihatkan sidebar di bagian bawah, termasuk Profil Admin.

## Build dan regression

- Frontend build: PASS (Vite).
- JavaScript syntax check: PASS untuk responsive-foundation.js.
- PHP lint: PASS untuk komponen sidebar.
- Focused: PASS, AdminDashboardRegressionTest + AdminWorkspaceNavigationTest, 8 tests / 52 assertions.
- git diff --check: PASS; terdapat peringatan normalisasi CRLF pada perubahan lain yang sudah ada.
- Full regression: **FAIL**, 587 tests / 2687 assertions / 1 failure. `MemberVehiclePurchaseFlowTest::test_member_order_detail_renders_premium_dashboard_for_owned_order`, baris 51, mengharapkan teks `Informasi Pembeli` sementara tampilan member menggunakan `Data Pembeli`. Kegagalan di area teks member tidak diubah atau dilemahkan dalam tugas sidebar ini.
- Database testing: phpunit.xml memaksa DB `yza_automotive_laravel_test`; tests/TestCase.php menolak nama database lain. Tidak menjalankan migrasi atau perubahan data pada database operasional.
- Database operasional berubah: **NO**.
- Commit/push/deploy: tidak dilakukan.

## Final status

Perbaikan frontend selesai dan interaksi sidebar yang diuji berjalan. Status keseluruhan **PARTIAL VERIFIED**: full regression belum seluruhnya hijau karena satu assertion teks member, dan touchpad fisik/touch/zoom tambahan serta seluruh sesi role nyata belum diverifikasi. Gejala wheel sidebar desktop awal belum direproduksi; jangan menyebutnya sebagai root cause yang sudah terbukti.
