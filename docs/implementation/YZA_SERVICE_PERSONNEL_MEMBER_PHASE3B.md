# YZA Member Service Personnel Phase 3B

Tanggal implementasi: 12 Oktober 2026  
Scope: integrasi member booking dengan personnel availability backend Phase 3A.

## Contract yang digunakan

UI memakai `docs/contracts/YZA_SERVICE_PERSONNEL_API_CONTRACT.md` dan endpoint existing:

`GET /pelanggan/booking-servis/technicians?date=YYYY-MM-DD&time=HH:MM&jenis_servis_id=<id>`

Field yang dirender hanya field public contract: `display_name`, `photo`, `title`, `specialization`, `experience_years`, `availability_status`, dan `available`. Tidak ada data phone, email, salary, credential, internal note, atau assignment customer lain yang dirender.

## Integrasi member

- Section **Preferensi Teknisi** ditambahkan pada Step 02 setelah tanggal dan slot valid dipilih.
- Endpoint teknisi hanya dipanggil setelah service, date, dan arrival time lengkap.
- **Tidak Ada Preferensi** selalu tersedia dan menjadi default.
- Hanya response dengan `available=true` yang dapat dipilih. Status `available`, `assigned`, `time_off`, dan `unavailable` memiliki label UI yang sesuai.
- Photo memakai URL response actual bila ada; bila null digunakan initials avatar lokal.
- Specialization dirender sebagai chips dari array response. Experience hanya ditampilkan bila bernilai tersedia.
- Perubahan service, tanggal, atau slot menghapus preference dan memuat ulang data teknisi.
- Request sequencing dan `AbortController` mencegah response lama menimpa slot terbaru.
- Preference dikirim hanya sebagai nullable `id_preferred_petugas`; backend tetap authoritative dan melakukan revalidation.
- Error preference unavailable dipresentasikan sebagai pilihan teknisi yang sudah tidak tersedia, dengan opsi memilih teknisi lain atau lanjut tanpa preference.

## Confirmation dan detail booking

Step 03 dan sticky summary menampilkan **Teknisi Pilihan** atau **Tidak Ada Preferensi**. Halaman detail booking membedakan preference member dari penugasan final; tidak ada label teknisi penanggung jawab pada preference.

Service Advisor tidak ditampilkan sebagai pilihan member. Mileage booking tetap read-only karena persistence kilometer booking belum tersedia pada schema; tidak ada migration atau workaround.

## Validasi

- `php artisan view:cache` — lulus
- Focused personnel/scheduling/member booking tests dijalankan tanpa migration
- Perubahan dibatasi pada member booking presentation, member controller presentation kecil, tests, dan dokumentasi.

Remaining gaps: customer approval, Quality Control, dan final personnel assignment tetap berada pada flow operasional fase berikutnya.
