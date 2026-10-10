# YZA Member Center Premium Redesign

Tanggal: 8 Oktober 2026  
Target: `/pelanggan/dashboard` dan alias `/pelanggan/member`  
Status: `READY WITH NOTES`

## 1. Previous Layout

Member Center sebelumnya sudah memiliki hero, identity card, KPI, promo, reminder, kendaraan, booking, points, transaksi, dan benefit. Namun seluruh area memakai bobot visual yang relatif sama dan belum memiliki onboarding/upcoming activity yang jelas.

## 2. New Information Architecture

Urutan baru:

1. Member navbar
2. Premium member hero
3. Digital member identity card
4. Account summary/KPI
5. Smart getting started atau upcoming activity
6. Quick services
7. Service reminder dan exclusive promo
8. YZA Points
9. Recent activity
10. Member benefits
11. Footer

Layout menggunakan visual rhythm: hero dark card sebagai focal point, KPI ringan, quick actions, split content, lalu activity/benefit.

## 3. Hero

Hero menggunakan layout desktop sekitar 60/40 dengan greeting personal, label `YZA PREMIUM MEMBER`, status active, dan member since. Background halaman tetap terang agar digital card dark menjadi focal point.

## 4. Member Card

Digital Member Card memakai charcoal, diagonal pattern subtle, red accent line, logo YZA, member ID server-side, nama, member since, dan status ACTIVE. Tidak ada nomor member palsu atau efek glow/3D berat.

## 5. KPI

Empat KPI memakai data aktual:

- YZA Points dari `poin_member`;
- Promo Member dari promo eligible yang visible;
- My Garage dari kendaraan milik customer;
- Booking Aktif dari booking yang belum terminal.

Setiap KPI memiliki CTA route yang nyata dan wording zero state yang membantu.

## 6. Quick Services

Action card tersedia untuk:

- Test Drive;
- Service;
- Car Wash;
- Trade-In;
- My Garage.

Semua link memakai route pelanggan existing dan tidak membuat dead button.

## 7. Upcoming Activity

`MemberDashboardService` sekarang menyiapkan `upcomingActivities` dari booking aktual, eager-loaded dengan kendaraan/mobil, difilter dari status terminal, dan diurutkan dari tanggal terdekat. Sumbernya mencakup booking mobil, test drive, servis, dan car wash.

Jika member baru belum memiliki kendaraan dan booking, dashboard menampilkan onboarding `Mulai Perjalanan Anda`. Jika kendaraan sudah tersedia, onboarding diganti dengan upcoming activity ketika ada.

## 8. Service Reminder

Reminder tetap jujur sebagai calculated display dari riwayat servis selesai. Copy menyatakan bahwa jadwal diperkirakan dari history, tanpa klaim WhatsApp atau push otomatis.

Status actual `upcoming`, `due_soon`, `overdue`, dan `no_history` ditampilkan dengan semantic label.

## 9. Promo

Promo menggunakan `PromoMember::visible()` existing, maksimal tiga row pada service layer dan ditampilkan maksimal tiga card. Nama, deskripsi, tanggal berakhir, jenis benefit, dan detail promo berasal dari data aktual. Tidak ada angka promo hardcoded.

## 10. Points

Saldo dan ledger terbaru diambil dari `poin_member` dan `riwayat_poin` customer yang login. UI tidak menampilkan tombol redeem, reward catalog, atau klaim fitur yang belum tersedia.

## 11. Recent Activity

Activity feed existing tetap digunakan dan dibatasi maksimal lima item. Activity menampilkan tanggal, judul, dan kode aktivitas dalam vertical list/timeline ringan.

## 12. Benefits

Benefit dipertahankan tetapi copy dibuat lebih actionable:

- Promo Eksklusif → promo route;
- YZA Points → history route;
- Service Reminder → reminder route;
- Booking & Test Drive → booking route;
- Trade-In Member → trade-in route;
- Riwayat Transaksi → transaction section.

Copy tidak menyatakan redeem points, automatic WhatsApp reminder, renewal, atau expiry membership.

## 13. Empty States

Empty state disediakan untuk:

- belum ada kendaraan;
- belum ada booking aktif;
- belum ada points;
- belum ada promo eligible;
- belum ada reminder history;
- belum ada activity.

Setiap empty state memiliki next action yang aman jika route tersedia.

## 14. Responsive

Breakpoint presentation:

- desktop: hero 60/40, KPI 4 kolom, quick service 5 kolom;
- tablet: hero satu kolom, KPI 2 kolom, service 3 kolom, split panel satu kolom;
- mobile: navbar scrollable, card full width, KPI 2 kolom, service/benefit satu kolom, whitespace tetap lapang.

Tidak digunakan `zoom`, `transform: scale()`, atau fixed desktop width.

## 15. Accessibility

- semantic `header`, `nav`, `main`, `section`, `article`, dan `footer`;
- `aria-label` pada navigasi dan KPI group;
- alt text logo;
- focusable links/buttons native;
- status tidak hanya dibedakan oleh warna karena memiliki text label;
- reduced-motion media query;
- compatibility text lama dipertahankan sebagai screen-reader-only agar kontrak test dan aksesibilitas tetap aman.

## 16. Files Modified

- `app/Services/MemberDashboardService.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/pelanggan/dashboard-premium.blade.php`

View lama `resources/views/pelanggan/dashboard.blade.php` tidak dihapus sehingga fallback tetap tersedia.

## 17. Backend/Data Changes

Tidak ada perubahan authentication, role, middleware member, membership access, Midtrans, payment, booking, service, trade-in, inventory, authorization, ownership, atau schema database.

Perubahan service hanya menambahkan eager loading dan derived presentation data: point history, vehicle label untuk upcoming activity, upcoming activities, dan active booking count.

## 18. Tests

Baseline sebelum redesign: 290 passed / 879 assertions.  
Member Center regression test setelah redesign: 7 passed / 28 assertions.  
Suite penuh final dijalankan setelah perubahan terakhir dan ditargetkan tetap 0 unexpected failure.

Test existing yang dipertahankan mencakup active member access, pending/guest/admin/petugas blocking, customer scoping, stable identity, member since, empty states, dan route regression.

## 19. Visual QA

CSS/Blade static audit dilakukan untuk target 1920×1080, 1366×768, 1024, 768, 430, dan 390 melalui responsive breakpoint rules, overflow-safe grids, scrollable navbar, serta full-width mobile member card.

Automated browser visual helper tidak berhasil membuka target window pada environment ini. Karena itu tidak dibuat klaim manual browser screenshot pass; validasi browser nyata pada viewport tersebut tetap direkomendasikan.

## 20. Known Limitations

- Membership expiry/renewal belum tersedia dari backend dan tidak ditampilkan sebagai fitur.
- Points redeem/reward catalog belum tersedia dan tidak ditampilkan sebagai CTA.
- Reminder belum menjadi scheduler/WhatsApp/push notification otomatis.
- Recent activity masih memakai source aggregation existing; belum ada dedicated activity table.
- Navbar mobile menggunakan horizontal scroll existing-friendly, bukan drawer baru.
- Browser visual UAT lintas viewport masih perlu dilakukan manual.
