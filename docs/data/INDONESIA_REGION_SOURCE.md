# Sumber Data Wilayah Indonesia

- Sumber: [dataset-wilayah-indonesia](https://github.com/prodhokter/dataset-wilayah-indonesia), fork dari `emsifa/api-wilayah-indonesia`.
- Basis data: kode wilayah administratif Kemendagri, sebagaimana dicatat pada README sumber.
- Pengambilan: 4 Oktober 2026.
- Format lokal: empat CSV tanpa header di `resources/data/indonesia/` (`provinces`, `regencies`, `districts`, `villages`).
- Struktur relasi: `province.id -> regencies.province_id -> districts.regency_id -> villages.district_id`.
- Runtime: Laravel membaca CSV lokal melalui `IndonesiaRegionService`; browser hanya meminta anak wilayah yang sedang dibutuhkan melalui endpoint same-origin. Tidak ada ketergantungan API eksternal saat registrasi.
