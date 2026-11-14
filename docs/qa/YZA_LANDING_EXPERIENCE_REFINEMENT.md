# YZA Landing Experience Refinement

> DIBATALKAN atas permintaan pengguna pada 6 November 2026. Sumber landing, navbar, CSS, dan helper dikembalikan ke snapshot sebelum perapian ini. Komponen/form/footer serta tes khusus perubahan ini dipindahkan ke `landing-refinement/reverted-source/`. Hasil dan gambar di bawah adalah catatan historis desain yang dibatalkan, bukan tampilan aktif.

Tanggal: 6 November 2026. Target: landing publik lokal `http://127.0.0.1:8000/`.

## Hasil

- Susunan halaman: hero, koleksi mobil, layanan, jual/tukar tambah, test drive, membership, ulasan, kunjungan/formulir, footer.
- Ukuran judul, tombol, kartu kendaraan, jarak antarelemen, ikon layanan, dan hierarki CTA diselaraskan. Warna merah/hitam serta aset showroom yang sudah ada dipertahankan.
- Koleksi memiliki tombol sebelumnya/berikutnya, navigasi keyboard, dan indikator posisi. Label SPORT mengikuti data kendaraan.
- Layanan memiliki grid yang menyesuaikan layar. Tombol Jadwalkan Servis memilih kebutuhan Service pada formulir kunjungan.
- Video diputar setelah klik dan dijeda saat keluar layar. Pembuka YZA menjadi pilihan di footer; Escape menutup dan mengembalikan fokus.
- Formulir dan informasi showroom digabung. Tautan kosong dan tujuan sosial/legal yang belum tersedia dihapus.
- Pengaturan alamat/jam operasional kosong memakai informasi kunjungan sesuai janji temu, sebagaimana disetujui pengguna. Tidak ada alamat atau jam operasional rekaan. Peta hanya ditampilkan bila alamat tersedia.
- Nomor WhatsApp kosong kini memakai nomor publik yang sudah ada: 0877 8430 4417. Pesan formulir tetap dilanjutkan ke WhatsApp dan tidak dikirim otomatis.

Pada viewport 1280 × 720, tinggi dokumen turun dari sekitar 6670 menjadi 4909 px (sekitar 26%). Angka ini merupakan pengukuran layout, bukan pengukuran kecepatan pemuatan.

## Verifikasi

| Pemeriksaan | Hasil |
| --- | --- |
| Regresi penuh, database pengujian | 586 tests, 2711 assertions; 0 failures/errors/skipped |
| Landing, membership, configurator | 14 tests, 138 assertions; lulus |
| Build frontend final | Lulus |
| PHP, CSS, inline JavaScript | Pemeriksaan sintaks lulus |
| Git diff whitespace | Lulus; peringatan normalisasi CRLF pada file lama |
| Lebar viewport 320, 375, 390, 430, 768, 1024, 1280, 1366, 1440, 1920 px | Tidak ada overflow horizontal pada dokumen |
| Interaksi browser | Navigasi layanan, pemilihan Service, carousel, play/pause video, intro/Escape/fokus, menu ponsel/Escape lulus |
| Browser console akhir | Tidak ada error tercatat |

Inspeksi visual dilakukan pada tampilan desktop dan ponsel terpilih. Matriks sepuluh viewport mengukur geometri dokumen; bukan klaim inspeksi visual setiap kemungkinan ukuran. Zoom browser native tidak diuji. Formulir tidak dikirim dan WhatsApp tidak dihubungi selama QA. Tidak ada penulisan data operasional, deploy, commit, atau push.

## Bukti

- [Log regresi penuh](landing-refinement/full.log) dan [JUnit](landing-refinement/full.xml).
- [Log pengujian terarah](landing-refinement/focused.log), [JUnit](landing-refinement/focused.xml), dan [build](landing-refinement/build.log).
- [Matriks viewport](landing-refinement/viewport-matrix.json).
- [Hero desktop](landing-refinement/screenshots/hero-desktop.jpg).
- [Layanan desktop](landing-refinement/screenshots/services-desktop.jpg).
- [Kunjungan desktop](landing-refinement/screenshots/visit-desktop.jpg).
- [Hero ponsel](landing-refinement/screenshots/hero-mobile.jpg).
- [Formulir ponsel](landing-refinement/screenshots/visit-mobile.jpg).

Snapshot sumber sebelum perubahan tersimpan di `landing-refinement/source-before/`. Perubahan lama di working tree dipertahankan.
