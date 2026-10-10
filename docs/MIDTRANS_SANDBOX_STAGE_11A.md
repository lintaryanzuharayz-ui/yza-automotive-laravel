# Midtrans Sandbox + Snap Redirect — Tahap 11A

## Architecture

Midtrans adalah channel tambahan untuk pembayaran incoming YZA Member. Pembayaran manual tetap tersedia.

Flow:

1. Member memilih transaksi eligible.
2. Laravel mengunci transaksi dan menghitung seluruh sisa tagihan.
3. Laravel membuat reservation gateway dengan order_id server-side.
4. Laravel memanggil Snap Sandbox dan menyimpan token serta redirect_url.
5. Browser diarahkan ke redirect_url resmi Midtrans.
6. Webhook signed atau Get Status server-side masuk ke pipeline processing yang sama.
7. Settlement membuat satu pembayaran internal melalui PaymentService existing.
8. PaymentService menjalankan completion domain dan YZA Points existing.

Browser return tidak pernah dianggap sebagai bukti pembayaran.

## Sandbox and Credentials

Configuration memakai:

- MIDTRANS_ENV=sandbox;
- MIDTRANS_SERVER_KEY;
- MIDTRANS_CLIENT_KEY;
- MIDTRANS_IS_PRODUCTION=false.

Server key hanya dibaca backend dari environment dan tidak disimpan pada database, Blade, dokumentasi, atau log. Test memakai Http::fake tanpa credential nyata.

Tahap ini tidak menggunakan Snap.js atau Snap Popup. Mode yang digunakan adalah Snap Redirect.

## Eligible Transactions

Allowlist gateway:

- servis;
- cuci_mobil;
- penjualan_sparepart;
- penjualan_mobil;
- trade_in.

Pembelian mobil bekas, outgoing payment, dealer payout, transaksi batal, lunas, walk-in tanpa pelanggan, dan transaksi tanpa sisa tagihan ditolak.

Gateway hanya dapat dibuat oleh pelanggan aktif pemilik transaksi. id_pelanggan tidak diterima dari request.

## Database

### pembayaran_gateway

Menyimpan reservation dan state provider:

- id_pembayaran_gateway;
- id_transaksi;
- id_pembayaran nullable unique;
- provider;
- order_id unique;
- gross_amount immutable;
- status_provider;
- payment_type;
- provider_transaction_id;
- snap_token;
- redirect_url;
- initiated_by;
- processed_at;
- last_synced_at.

### event_pembayaran_gateway

Menyimpan event audit dan idempotency:

- event_key unique;
- provider status;
- status code;
- fraud status;
- payment type;
- provider transaction id;
- sanitized payload hash;
- received_at.

Tidak disimpan card number, CVV, OTP, authorization header, Server Key, atau raw payload sensitif.

## Snap Create and Amount

Gross amount selalu seluruh sisa tagihan yang dihitung server-side setelah transaksi dikunci. Nominal request pelanggan tidak digunakan.

Order ID dibuat server-side dalam format YZA-{id_transaksi}-{random}, unique, aman, dan kurang dari 50 karakter.

Attempt aktif berstatus created atau pending direuse bila gross amount masih sama. Attempt expired, cancel, deny, atau failure dapat digantikan order baru.

Pending manual payment membuat pembuatan attempt baru ditolak. Payment model memiliki centralized collision guard agar pembuatan atau verifikasi manual tidak berjalan ketika gateway attempt aktif.

## MidtransClient

MidtransClient menangani:

- POST Snap Sandbox;
- GET Transaction Status Sandbox;
- Basic Auth dengan Server Key;
- timeout;
- safe error;
- signature SHA512;
- constant-time hash_equals.

Tidak ada HTTP provider call dari controller.

## Webhook and Status Sync

Webhook:

- POST /payment/midtrans/notification;
- tidak membutuhkan authenticated session;
- hanya route exact tersebut yang dikecualikan dari CSRF;
- validasi signature wajib.

Signature:

SHA512(order_id + status_code + gross_amount + SERVER_KEY)

GET status pelanggan tersedia melalui POST sync yang owner-scoped dan menggunakan status dari server Midtrans, bukan body request.

localhost tidak menerima webhook internet secara langsung. Deployment Sandbox memerlukan public HTTPS URL atau tunnel eksternal. Tunnel tidak menjadi dependency aplikasi.

## Provider Status

- settlement: successful;
- capture: successful bila fraud status tidak deny;
- pending: belum dibayar;
- deny, cancel, expire, failure: tidak dibayar;
- refund, partial_refund, chargeback, partial_chargeback: dicatat dan memerlukan review; tidak memicu reversal otomatis.

State sukses tidak diturunkan menjadi pending oleh event out-of-order.

## Internal Payment Bridge

Gateway tidak menulis transaksi menjadi lunas secara langsung. Settlement membuat payment internal melalui PaymentService existing, lalu menjalankan verify pipeline. Metode internal tetap mengikuti enum pembayaran existing; tipe provider disimpan pada pembayaran_gateway.payment_type.

Unique id_pembayaran memastikan satu attempt sukses maksimal memiliki satu pembayaran internal. PaymentService tetap mengelola status transaksi, completion domain, dan YZA Points.

Trade-In tetap tidak mendapat Points hanya karena payment lunas. TradeInService harus memfinalisasi trade-in lebih dulu.

## Idempotency and Concurrency

Event key deterministic:

midtrans:{order_id}:{transaction_status}:{status_code}:{provider_transaction_id}

Gateway row dikunci sebelum processing. Duplicate webhook tidak membuat payment kedua. Amount mismatch mengubah gateway menjadi requires_review tanpa payment.

Jika payment manual race dengan gateway, transaksi/payment model guard menolak mutation yang berpotensi collision.

## Security

- ownership member diverifikasi dari authenticated user;
- unknown order ditolak;
- signature invalid ditolak;
- amount mismatch tidak dibayar;
- redirect URL hanya berasal dari response provider;
- CSRF customer routes tetap aktif;
- webhook exemption hanya exact endpoint;
- tidak ada open redirect dari request;
- tidak ada raw secret/provider credential pada log atau view.

## Deferred

Refund automation, chargeback automation, production Midtrans, point redemption, promo redemption, external notification, dan payment gateway lain belum termasuk.

## Verification

Test khusus: 7 passed, 16 assertions passed.

HTTP test memakai fake provider dan tidak memanggil internet.

Live Sandbox smoke test: SKIPPED karena credential valid dan isolated live fixture tidak tersedia.

Native database tetap read-only.
