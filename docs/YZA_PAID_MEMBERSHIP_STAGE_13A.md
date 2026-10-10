# YZA Paid Membership Stage 13A

Membership adalah status benefit pada role `pelanggan`, bukan role baru. Registrasi membuat akun, akun YZA Member `pending_payment`, dan biaya aktivasi configurable melalui `membership_activation_fee` (default Rp249.000). Pelanggan lama dibackfill sebagai `active` untuk menjaga kompatibilitas data demo.

Pembayaran memakai tabel gateway Midtrans existing dengan relasi `id_membership`. Order prefix `YZA-MBR-`, amount dari server, signature, status sync, webhook idempotency, dan amount mismatch protection tetap berlaku. Aktivasi tidak membuat Points.

Fasilitas member memakai status active; pending diarahkan ke `/pelanggan/membership/aktivasi`. Tidak ada renewal, refund otomatis, atau tier membership pada versi ini.
