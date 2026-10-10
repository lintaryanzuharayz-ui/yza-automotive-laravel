# YZA Member Center — Premium Sidebar Redesign

## Scope

Redesign presentation halaman aktif `/pelanggan/dashboard` agar Member Center memiliki navigasi sidebar kiri yang rapi, premium, dan responsive. Perubahan ini hanya menyentuh layer Blade/CSS/JavaScript pada halaman member dan tidak mengubah business logic, autentikasi, membership, transaksi, atau database.

## Implementasi

- View baru: `resources/views/pelanggan/dashboard-sidebar.blade.php`.
- `DashboardController::pelanggan()` menggunakan view sidebar baru melalui data dari `MemberDashboardService` yang sudah ada.
- Root layout hotfix: `layouts.app` kini memiliki mode `member-portal` yang merender konten member langsung tanpa `.yza-shell`/header publik global, sehingga tidak terjadi nested app shell atau double header.
- Member Portal menggunakan flex shell flush-left: sidebar 250px/76px collapsed, workspace `flex:1` dengan `min-width:0`, tanpa `margin-left`, `100vw`, atau outer container.
- Sidebar desktop memiliki mode expanded sekitar 280px dan collapsed sekitar 82px.
- Status collapsed disimpan di `localStorage` dengan key `yza_member_sidebar_collapsed`.
- Mobile menggunakan off-canvas drawer dengan overlay, tombol menu, dan penutupan melalui overlay, link, atau Escape.
- Member Center kini memakai satu dark topbar dengan tombol collapse, search field compact, notification, dan profile; breadcrumb serta duplicate company bar dihilangkan.
- Main content menggunakan ruang layar utama secara penuh dengan padding responsive; tidak ada fixed `max-width` sempit yang mengunci layout desktop.
- Hero memakai asset otomotif lokal `public/assets/images/hero/yza-hero-reference-v3.png` dengan overlay, CTA Booking Servis/Lihat Promo, serta member card berada di dalam banner.
- Quick Services memakai empat image card lokal untuk Booking Servis, Test Drive, Car Wash, dan Trade-In. Aktivitas mendatang, reminder, promo, dan aktivitas terakhir dirender dalam komposisi dashboard padat berbasis data aktual.
- Navigasi menggunakan route yang sudah tersedia: Member Center, Kendaraan Saya, Booking, Test Drive, Servis, Car Wash, Trade-In, Transaksi & Pembayaran, Promo Member, YZA Points, Profil, dan Keluar.
- State active route, tooltip `title`, label aksesibel, dan focusable buttons disediakan untuk menjaga usability saat sidebar diciutkan.
- Konten dashboard tetap data-driven: identitas member, KPI, kendaraan, booking, reminder, promo, points, dan aktivitas berasal dari data aplikasi yang sudah ada.

## Hal yang Tidak Diubah

- Tidak ada migration, schema, seed, model bisnis, service workflow, payment flow, atau authorization rule baru.
- Tidak ada data demo/fake yang ditambahkan.
- Tidak ada perubahan pada native database.
- Route member yang sudah ada tetap dipakai.

## Validasi

- `php artisan optimize:clear` berhasil.
- `php artisan view:cache` berhasil.
- `php artisan test --filter=MemberFoundationStage10ATest` — 7 passed, 28 assertions.
- Full suite — **290 passed, 879 assertions, 0 failures**.
- Browser helper pada sesi sebelumnya tidak stabil sehingga visual QA otomatis tidak diklaim. Layout perlu diperiksa manual pada desktop expanded/collapsed dan mobile drawer menggunakan browser aplikasi.

## Final UI Scale Refinement

- Desktop sidebar collapse/expand control dihapus; sidebar desktop permanen dengan lebar 260px.
- Mobile tetap menggunakan hamburger/off-canvas drawer.
- Sidebar menu diperbesar ke 15px, section label 12px, icon sekitar 19px, dan row 48px.
- Topbar diperbesar menjadi 76px; title 21px; search 40px.
- Hero menggunakan heading responsive hingga 56px dan body 17px.
- KPI memakai tinggi minimum 140px, label 14px, value 38px, description/CTA 14px.
- Quick service cards memakai tinggi minimum 178px dengan title 19px, description 14px, dan arrow button 40px.
- Panel bawah, promo banner, empty state, dan activity timeline diperbesar agar lebih mudah dibaca pada desktop.

## Large-Scale Refinement

- Audit project tidak menemukan `zoom`, `transform: scale(...)`, atau state `localStorage` collapse aktif pada Member Center.
- Desktop wide memakai sidebar 300px, topbar 86px, hero minimum 400px, dan content dibiarkan scroll secara natural.
- Pada viewport desktop besar, hero heading memakai `clamp(56px, 4vw, 80px)`, member card sekitar 500px, KPI minimum 170px, section heading hingga 32px, dan quick-service card minimum 225px.
- Base `html`/Member Portal ditetapkan 16px; tidak ada browser scaling hack.
- Automated browser computed-style/screenshot QA belum tersedia pada sesi ini; ukuran di atas adalah declared CSS dan tetap perlu verifikasi manual pada 1920×1080 @100%.

## 67% Browser Zoom Target

- Menyesuaikan target visual utama pengguna pada 1920×1080 dengan browser zoom 67%.
- Desktop wide menggunakan sidebar aktual 350px, topbar 116px, hero minimum 560px, KPI minimum 230px, quick-service card minimum 320px, dan panel bawah minimum 360px.
- Typography wide diperbesar secara nyata: sidebar menu 25px, hero heading sampai 108px, member name sampai 116px, hero description 28px, KPI value 74px, section heading 40px, quick-service title 34px, dan button 66px.
- Tidak menambahkan CSS `zoom` atau `transform: scale`; halaman dibiarkan scroll secara normal.
- Screenshot terakhir menunjukkan browser zoom 67%; computed-style browser otomatis belum tersedia, sehingga QA pixel-level tetap perlu dilakukan manual pada Edge/Chrome.
