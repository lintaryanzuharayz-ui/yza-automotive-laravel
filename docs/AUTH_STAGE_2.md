# YZA Automotive — Authentication Stage 2

## 1. Scope

Tahap ini hanya mencakup identity dan authentication untuk `roles`, `users`, `petugas`, dan `pelanggan`. Modul bisnis, membership, YZA Points, payment, Midtrans, dan registrasi publik belum diaktifkan.

## 2. Source dan target

- Source reference: `yza_automotive` — dibaca saja.
- Development target: `yza_automotive_laravel`.
- Primary key existing dipertahankan: `id_role`, `id_user`, `id_petugas`, `id_pelanggan`.
- Data identity disalin dengan primary key yang sama.

## 3. Schema mapping

Migration `2026_09_28_000000_create_yza_identity_tables.php` mempertahankan nama tabel, nama kolom, ENUM, nullable/default, unique key, index, timestamp, dan foreign key existing.

Relasi:

```text
roles 1 ─── * users
users 1 ─── 0..1 petugas
users 1 ─── 0..1 pelanggan
```

## 4. Models

- `Role` → `roles.id_role`
- `User` → `users.id_user`
- `Petugas` → `petugas.id_petugas`
- `Pelanggan` → `pelanggan.id_pelanggan`

`User` memiliki relasi `role`, `petugas`, dan `pelanggan`, serta fallback display name ke username.

## 5. Password compatibility

Hash source diaudit hanya berdasarkan algoritma dan panjang, tanpa mencetak hash lengkap. Seluruh akun yang diperiksa menggunakan bcrypt `$2y$` dengan panjang 60 karakter.

Verifikasi menggunakan `password_verify()` melalui `PasswordCompatibilityService`. Tidak ada reset password, rehash otomatis, plaintext password, atau logging password.

## 6. Authentication flow

```text
GET /login
→ username/email + password
→ validasi account aktif
→ password_verify()
→ Auth::login()
→ session regenerate
→ update users.last_login pada database development
→ redirect sesuai role
```

Display name mengikuti perilaku native:

1. `petugas.nama_lengkap`
2. `pelanggan.nama_lengkap`
3. `users.username`

## 7. Authorization

Middleware `RoleMiddleware` membatasi dashboard berdasarkan role exact:

- `role:admin`
- `role:petugas`
- `role:pelanggan`

Role `member` tidak dibuat.

## 8. Routes

- `GET /login` — `login`
- `POST /login` — `login.store`
- `POST /logout` — `logout`
- `GET /admin/dashboard` — `admin.dashboard`
- `GET /petugas/dashboard` — `petugas.dashboard`
- `GET /pelanggan/dashboard` — `pelanggan.dashboard`

Route `/register` tidak dibuat.

## 9. Security

- CSRF aktif pada login dan logout.
- Session ID diregenerasi setelah login.
- Logout melakukan `Auth::logout()`, invalidate session, dan regenerate CSRF token.
- Session file, encryption, HTTP-only, dan SameSite `lax` digunakan.
- Rate limit: 5 percobaan per menit per identity/IP.
- Pesan login gagal bersifat umum.
- Mass assignment dibatasi dengan guarded fields.
- Password disembunyikan dari serialization.
- Tidak ada remember-me.

## 10. Test result

- Schema migration: PASS.
- Source/target counts: roles 3/3, users 5/5, petugas 3/3, pelanggan 3/3.
- Role values: admin, petugas, pelanggan cocok.
- Foreign keys: PASS.
- Login username: PASS.
- Login email: PASS.
- Wrong password: PASS.
- Generic login error: PASS.
- Admin dashboard: PASS.
- Petugas dashboard: PASS.
- Pelanggan dashboard: PASS.
- Pelanggan ke admin: `403`.
- Petugas ke admin: `403`.
- Guest ke dashboard: redirect ke `/login`.
- Session regeneration: PASS.
- Logout dan invalidation: PASS.
- CSRF login/logout valid: PASS.
- CSRF login/logout tanpa token: `419`.
- Rate limiting: PASS.
- `last_login` target berubah: PASS.
- `last_login` source tidak berubah: PASS.
- Homepage PHP Native: `200 OK`.
- Database source tetap 37 object live: PASS.
- Laravel PHPUnit suite: 2 tests passed.

## 11. Known limitations

- Registrasi publik belum tersedia.
- Password reset belum tersedia.
- Email verification belum tersedia.
- Dashboard masih placeholder.
- Belum ada modul bisnis Laravel.

## 12. Files changed

- `app/Models/User.php`
- `app/Models/Role.php`
- `app/Models/Petugas.php`
- `app/Models/Pelanggan.php`
- `app/Services/PasswordCompatibilityService.php`
- `app/Http/Middleware/RoleMiddleware.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/DashboardController.php`
- `bootstrap/app.php`
- `routes/web.php`
- `public/css/app.css`
- `database/migrations/2026_09_28_000000_create_yza_identity_tables.php`
- `resources/views/auth/login.blade.php`
- `resources/views/components/dashboard-card.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/petugas/dashboard.blade.php`
- `resources/views/pelanggan/dashboard.blade.php`
- `tests/Feature/ExampleTest.php`
- `phpunit.xml`
