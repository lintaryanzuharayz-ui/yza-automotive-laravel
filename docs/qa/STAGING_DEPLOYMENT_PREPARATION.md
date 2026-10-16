# YZA Automotive — Staging Deployment Preparation

Tanggal: 16 Oktober 2026  
Baseline: **412 PASS, 0 FAIL, 0 SKIPPED, 1503 assertions**

Status dokumen: **SIAP UNTUK KEPUTUSAN OWNER — BELUM DEPLOYMENT**

Dokumen ini hanya menyiapkan persyaratan dan checklist. Tidak ada DNS, tunnel, firewall, server publik, database operasional, port 8000/8001, atau konfigurasi lokal yang diubah.

## 1. Persyaratan infrastruktur

Status yang belum memiliki keputusan owner ditandai `WAITING FOR OWNER DECISION`.

| Komponen | Kebutuhan | Status |
|---|---|---|
| Domain/subdomain | Domain staging khusus; nama sebenarnya belum ditentukan | **WAITING FOR OWNER DECISION** |
| Hosting | VPS/host staging terisolasi dengan akses admin terbatas | **WAITING FOR OWNER DECISION** |
| Reverse proxy | Nginx/Apache/managed proxy yang meneruskan HTTPS ke Laravel internal | Belum disediakan |
| TLS | Sertifikat valid untuk domain staging dan renewal otomatis | Belum disediakan |
| Runtime | PHP 8.2.x kompatibel, Composer dependencies, ekstensi Laravel yang sama | Harus diverifikasi di host |
| Database | MySQL staging terpisah; tidak memakai database port 8000, UAT lokal, atau PHP Native | **WAITING FOR OWNER DECISION** |
| Backup/rollback | Backup terenkripsi, restore drill, snapshot/release rollback | Belum disediakan |
| Midtrans | Credential Sandbox melalui secret manager/environment | Belum dipasang pada staging |
| Session/cache/queue/storage | Semua namespace dan lokasi terpisah dari production | Harus dikonfigurasi |
| UAT users | Akun sintetis dengan password kuat dan unik | Harus dibuat owner/operator |

Jangan mengarang domain, alamat server, IP allowlist, atau vendor hosting sebelum owner menetapkannya.

## 2. Template environment staging

Template berikut adalah referensi deployment, bukan file credential. Jangan commit hasil akhirnya dan jangan menyalin `.env.uat` secara langsung.

```dotenv
APP_NAME="YZA Automotive Staging"
APP_ENV=staging
APP_DEBUG=false
APP_KEY=base64:GENERATE_UNIQUE_KEY_ON_STAGING_HOST
APP_URL=https://OWNER_APPROVED_STAGING_DOMAIN

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=PRIVATE_STAGING_DB_HOST
DB_PORT=3306
DB_DATABASE=yza_automotive_staging
DB_USERNAME=STAGING_DB_USER
DB_PASSWORD=INJECT_FROM_SECRET_MANAGER

SESSION_DRIVER=file
SESSION_FILES=/srv/yza-automotive/shared/sessions-staging
SESSION_COOKIE=yza_automotive_staging_session
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=OWNER_APPROVED_STAGING_DOMAIN
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=file
CACHE_PREFIX=yza-automotive-staging-
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MIDTRANS_ENV=sandbox
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=INJECT_FROM_SECRET_MANAGER
MIDTRANS_CLIENT_KEY=INJECT_FROM_SECRET_MANAGER
```

Catatan:

- `APP_KEY` harus unik untuk staging dan tidak boleh sama dengan lokal atau production.
- `APP_URL` baru diisi setelah domain dan TLS disetujui.
- `SESSION_SECURE_COOKIE=true` hanya untuk HTTPS staging; UAT localhost tetap memakai konfigurasi HTTP lokalnya.
- Secret Midtrans tidak boleh berada di source code, Git, screenshot, laporan, atau command history.
- `MIDTRANS_ENV=sandbox` sebaiknya eksplisit walaupun aplikasi memiliki default sandbox.
- `APP_DEBUG=false` wajib sebelum endpoint dapat diakses dari jaringan bukan localhost.
- Reverse proxy harus meneruskan `X-Forwarded-Proto: https`. Trusted proxy harus dibatasi ke alamat proxy yang diketahui; jangan mempercayai semua proxy. Implementasi trusted-proxy perlu diverifikasi pada host staging karena belum diubah di source lokal.
- Staging tidak boleh memakai nama database `yza_automotive_laravel_uat` atau production. Seeder fixture UAT tidak boleh dijalankan pada staging publik.

## 3. Security checklist

- [ ] Owner menyetujui domain dan host staging.
- [ ] DNS diarahkan hanya setelah host dan reverse proxy siap.
- [ ] TLS valid, hostname cocok, renewal diuji, dan HTTP diarahkan ke HTTPS.
- [ ] Port MySQL tidak terbuka ke internet.
- [ ] Port 8000/8001 lokal tidak dipublikasikan sebagai endpoint staging.
- [ ] Firewall hanya membuka 443 dan akses administrasi terbatas.
- [ ] Database staging memakai user non-root dengan privilege minimum.
- [ ] Backup terenkripsi dan restore drill tersedia.
- [ ] `APP_DEBUG=false`, error page tidak membocorkan stack trace.
- [ ] `APP_KEY` staging unik.
- [ ] Cookie session staging unik, Secure, HttpOnly, dan SameSite sesuai kebutuhan.
- [ ] Cache, queue, failed jobs, logs, storage, dan scheduler tidak berbagi namespace production.
- [ ] Credential Midtrans Sandbox aktif dan bukan credential production.
- [ ] Akun staff/UAT tidak memakai password sederhana.
- [ ] Tidak ada secret hardcoded; audit Git dan deployment artifact selesai.
- [ ] Tidak ada akses atau koneksi ke database PHP Native.
- [ ] Endpoint webhook hanya menerima signature Midtrans yang valid dan mencatat audit sanitasi.
- [ ] Log tidak memuat Server Key, Client Key, Authorization header, cookie, atau Snap token.
- [ ] Rate limit, monitoring error, alerting, dan log rotation aktif.

## 4. Rencana deployment aman

Deployment dilakukan hanya setelah owner menyetujui domain/host.

1. Buat host dan database staging baru dengan backup/restore procedure.
2. Pasang source pada release directory immutable; jangan menyalin `.env.uat` atau `.env` production.
3. Pasang dependency Composer dari lockfile dan verifikasi PHP extensions.
4. Inject environment melalui secret mechanism; generate `APP_KEY` di host staging.
5. Konfigurasi reverse proxy HTTPS dan trusted proxy terbatas.
6. Jalankan pemeriksaan konfigurasi tanpa menampilkan secret.
7. Jalankan migration hanya terhadap database staging setelah backup, review, dan persetujuan owner.
8. Cache konfigurasi/route/view hanya setelah environment final terpasang.
9. Jalankan health check, login UAT, permission smoke test, dan endpoint webhook reachability.
10. Uji Midtrans Sandbox dengan order sintetis; jangan memakai order settlement production/UAT lama.
11. Monitor log dan database audit selama acceptance window.

Tidak ada langkah deployment di atas yang dijalankan pada pekerjaan ini.

## 5. Rencana rollback

- Simpan release version, checksum artifact, dan versi konfigurasi non-secret.
- Ambil backup database staging sebelum migration atau UAT mutation.
- Untuk masalah aplikasi, arahkan reverse proxy ke release sebelumnya dan invalidate cache staging.
- Untuk masalah schema, gunakan rollback migration yang direview atau restore snapshot; jangan menghapus data manual.
- Untuk masalah webhook, nonaktifkan callback staging pada Midtrans Sandbox dan pertahankan audit untuk investigasi.
- Credential Sandbox dapat dirotasi tanpa mengubah source.
- Setelah rollback verifikasi HTTPS, login, session isolation, database target, webhook response, dan tidak ada akses production.

## 6. Rencana UAT webhook provider

Route existing yang akan diuji:

```text
POST /payment/midtrans/notification
```

Acceptance sequence:

1. Pastikan callback HTTPS dapat dijangkau Midtrans Sandbox tanpa bypass sertifikat.
2. Buat satu transaksi Sandbox sintetis melalui UI resmi.
3. Terima notification asli dengan signature valid dan cocokkan order ID serta gross amount.
4. Uji pending, settlement, expire, cancel, dan failure melalui capability Sandbox yang benar-benar tersedia.
5. Verifikasi signature invalid ditolak.
6. Verifikasi order ID unknown dan nominal mismatch ditolak atau masuk `requires_review` tanpa membuat payment.
7. Verifikasi duplicate settlement tidak membuat payment kedua dan status settlement tetap stabil.
8. Bandingkan webhook dengan server-to-server status inquiry.
9. Verifikasi retry notification menghasilkan audit yang idempotent.
10. Simpan bukti HTTP status, event ID, timestamp, order reference, dan hasil audit tanpa secret/token.

`PASS` hanya diberikan setelah notification provider asli diterima melalui HTTPS. HTTP fake PHPUnit atau direct service call bukan pengganti webhook provider.

## 7. Refund dan Sales Awards

Refund provider **tidak diaktifkan** pada staging preparation ini. Financial adjustment offline tetap mengikuti lifecycle existing: requested, under review, approved, processing, confirmed/rejected/failed; separation of duties, cap nominal, evidence, dan audit event tetap wajib.

Keputusan owner yang wajib disahkan:

- Sumber refund authoritative.
- Refund sebelum handover.
- Refund setelah handover.
- Refund parsial dan penuh.
- Role pengaju, approver, dan confirmer.
- Bukti pengeluaran dana dan retention period.
- Dampak refund terhadap ownership/My Garage.
- Dampak refund terhadap target Sales dan Sales Awards.
- Aturan chargeback, partial refund, dan provider dispute.
- Pemilik rekonsiliasi server-to-server.

Sampai keputusan tersedia, tidak boleh ada refund provider, ownership reversal otomatis, atau finalisasi Sales Awards. Sales Awards tetap fail-closed.

## 8. Blocker sebelum deployment

1. **WAITING FOR OWNER DECISION:** domain/subdomain staging.
2. **WAITING FOR OWNER DECISION:** VPS/hosting dan network boundary.
3. TLS certificate dan reverse proxy belum tersedia.
4. Database staging, backup, restore drill, dan rollback belum tersedia.
5. Credential Midtrans Sandbox staging belum di-inject.
6. Trusted proxy/HTTPS behavior belum diverifikasi pada host nyata.
7. Akun UAT kuat dan policy password belum disiapkan.
8. Webhook provider HTTPS nyata belum diuji.
9. Refund policy dan authoritative source belum disahkan.

Keputusan saat ini: **STAGING PREPARATION READY — DEPLOYMENT NOT AUTHORIZED**.
