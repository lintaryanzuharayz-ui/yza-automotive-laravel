# LAPORAN QA & PERBAIKAN: SINKRONISASI DATA PRE-ORDER & MODAL POPUP
**Sistem:** YZA Automotive Admin Management Portal  
**Target Halaman:** `http://127.0.0.1:8000/admin/mobil?tab=inventori`  
**Kasus Kendaraan:** Bugatti Chiron (dan seluruh model fisik & katalog terpadu)  
**Dokumen:** `docs/qa/YZA_PREORDER_COUNT_MODAL_SYNC_FIX.md`  
**Status:** **RESOLVED & VERIFIED PASS** (100% In-Place Modal System)

---

## 1. IDENTIFIKASI MASALAH & HASIL AUDIT READ-ONLY

### Gejala Masalah
Pada baris **Bugatti Chiron** di tabel Manajemen Mobil & Showroom:
- Kolom **Pre-Order Aktif** menampilkan: `1 permintaan`
- Kolom **Aksi** menampilkan tombol: `PO (1)`
- Kolom **Stok Fisik**: `0 unit`, **Stok Ready**: `0 unit`, **Status**: `Belum Ada Unit Fisik`
- Namun, ketika tombol `PO (1)` atau badge `1 permintaan` ditekan, popup modal menampilkan:
  - *Total Pre-Order Aktif:* `0 Permintaan`
  - *Status Box:* `Tidak Ada Permintaan Pre-Order Aktif`
  - *Daftar Member:* Kosong (menampilkan fallback pendaftaran unit)

### Hasil Audit Read-Only Database Operasional
Audit read-only terhadap database operasional `yza_automotive_laravel` menemukan record aktif berikut:
- **Tabel MySQL:** `pre_order_mobil_member`
- **ID Preorder:** `1`
- **Kode Preorder:** `PRE-00001`
- **Customer / Member:** Lintar Yan Zuhara (`PLG-202610-7EE538`)
- **Nomor Telepon:** `087784304417`
- **Email:** `lintaryanzuhara.yz@gmail.com`
- **Model Kendaraan:** Bugatti Chiron (Tahun 2024, Varian Super Sport, Warna Atlantic Blue)
- **Product Key:** `showcase:bugatti-chiron`
- **Showcase Slug:** `bugatti-chiron`
- **Status Permintaan:** `menunggu_stok` (Aktif)
- **Catatan Audit:** Data Pre-Order resmi tidak pernah dihapus, dimanipulasi, atau rusak. Record database 100% valid dan tersimpan utuh.

---

## 2. PENYEBAB KETIDAKSINKRONAN DATA (ROOT CAUSE)

Penyebab ketidaksinkronan angka antara tabel utama dan popup modal berasal dari **perbedaan nama tabel database dalam pengecekan schema controller**:

1. **Penyebab Utama di `MobilController.php` (Line 214):**
   ```php
   // Kode Sebelumnya:
   $allActivePreorders = \Illuminate\Support\Facades\Schema::hasTable('member_vehicle_preorders')
       ? \App\Models\MemberVehiclePreorder::active()->...->get()
       : collect();
   ```
   Tabel di database MySQL bernama `pre_order_mobil_member` (sesuai definisi Eloquent Model `MemberVehiclePreorder::$table = 'pre_order_mobil_member'`), bukan `member_vehicle_preorders`.
   Akibatnya:
   - `Schema::hasTable('member_vehicle_preorders')` mengembalikan `false`.
   - `$allActivePreorders` selalu dievaluasi sebagai koleksi kosong (`collect()`).

2. **Divergensi Antara Badge Tabel dan Modal Payload di Blade:**
   - **Badge / Tombol Tabel:** Menghitung jumlah pre-order melalui pemanggilan langsung `$identity->activePreorders($model)`, yang melakukan query mandiri langsung ke database model via Eloquent `MemberVehiclePreorder::active()` sehingga berhasil membaca data dan menampilkan `1 permintaan` serta `PO (1)`.
   - **Isi Modal (JSON Script Payload):** Mengisi array `car.preorders` dari koleksi `$allActivePreorders`. Karena koleksi ini kosong akibat pengecekan schema yang salah, script `#car-data-showcase:bugatti-chiron` menerima `'preorders' => []`.
   - **Render JavaScript Modal:** Ketika popup dibuka, fungsi `renderPreorderModal(car, dom)` membaca `car.preorders || []` yang bernilai array kosong (`length = 0`), sehingga menampilkan `0 Permintaan` dan kotak kosong "*Tidak Ada Permintaan Pre-Order Aktif*".

---

## 3. PERBAIKAN SUMBER DATA & ARSITEKTUR KONSISTENSI

Untuk menjamin konsistensi matematis mutlak tanpa duplikasi query, arsitektur data disatukan ke dalam satu layanan otoritatif:

### A. Perbaikan Schema Check di `MobilController.php`
```php
// app/Http/Controllers/Admin/MobilController.php line 214:
$allActivePreorders = \Illuminate\Support\Facades\Schema::hasTable('pre_order_mobil_member')
    ? \App\Models\MemberVehiclePreorder::active()
        ->with(['pelanggan.user', 'mobil'])
        ->latest('created_at')
        ->get()
    : collect();
```

### B. Otoritatif Matching di `CatalogVehicleIdentityService.php`
Menambahkan method `activePreordersList()` dan menyelaraskan `activePreorders()` sehingga keduanya menggunakan logika pencocokan yang identik:
```php
public function activePreorders(Mobil $model, ?Collection $preloaded = null): int
{
    if ($preloaded !== null) {
        return $this->filterMatchingPreorders($model, $preloaded)->count();
    }
    // Query langsung jika koleksi preloaded tidak dilewatkan
    $key = $this->key($model);
    $ids = $this->physical($model)->pluck('id_mobil');
    return MemberVehiclePreorder::active()->where(function ($query) use ($key, $ids) {
        $query->where('product_key', $key)->orWhereIn('id_mobil', $ids);
        if (str_starts_with($key, 'showcase:')) $query->orWhere('showcase_slug', substr($key, 9));
    })->count();
}

public function activePreordersList(Mobil $model, ?Collection $preloaded = null): Collection
{
    if ($preloaded !== null) {
        return $this->filterMatchingPreorders($model, $preloaded)->values();
    }
    $key = $this->key($model);
    $ids = $this->physical($model)->pluck('id_mobil');
    return MemberVehiclePreorder::active()
        ->with(['pelanggan.user', 'mobil'])
        ->where(function ($query) use ($key, $ids) {
            $query->where('product_key', $key)->orWhereIn('id_mobil', $ids);
            if (str_starts_with($key, 'showcase:')) {
                $query->orWhere('showcase_slug', substr($key, 9));
            }
        })
        ->latest('created_at')
        ->get();
}

public function filterMatchingPreorders(Mobil $model, Collection $preorders): Collection
{
    $key = $this->key($model);
    $ids = $this->physical($model)->pluck('id_mobil')->all();
    $slug = str_starts_with($key, 'showcase:') ? substr($key, 9) : $model->showcase_slug;

    return $preorders->filter(function ($po) use ($key, $ids, $slug, $model) {
        if ($model->id_mobil && $po->id_mobil && (int) $po->id_mobil === (int) $model->id_mobil) return true;
        if ($po->id_mobil && in_array((int) $po->id_mobil, $ids, true)) return true;
        if ($po->product_key && $po->product_key === $key) return true;
        if ($slug && $po->showcase_slug === $slug) return true;
        return false;
    });
}
```

---

## 4. STRUKTUR PAYLOAD & SINKRONISASI MODAL PRE-ORDER

Di dalam `resources/views/admin/mobil/index.blade.php`:
Baik unit fisik maupun model virtual katalog kini dievaluasi secara seragam:

```blade
@php
    $identity = app(\App\Services\CatalogVehicleIdentityService::class);
    $vMatchingPreorderModels = $identity->activePreordersList($model, $allActivePreorders ?? null);
    $vActivePo = $vMatchingPreorderModels->count();
    $vMatchingPreorders = $vMatchingPreorderModels->map(function ($po) {
        return [
            'id_preorder' => $po->getKey(),
            'kode_preorder' => 'PRE-' . str_pad((string) $po->getKey(), 5, '0', STR_PAD_LEFT),
            'customer_name' => $po->pelanggan?->nama_lengkap ?: 'Member YZA',
            'customer_code' => $po->pelanggan?->kode_pelanggan ?: 'MEM',
            'customer_phone' => $po->pelanggan?->nomor_telepon ?: ($po->pelanggan?->no_hp ?: '—'),
            'customer_email' => $po->pelanggan?->email ?: ($po->pelanggan?->user?->email ?: '—'),
            'vehicle' => $po->nama_mobil,
            'variant' => $po->varian ?: 'Standar',
            'year' => (int) ($po->tahun ?: date('Y')),
            'color' => $po->warna ?: 'Standar',
            'status' => $po->status,
            'status_label' => ucfirst(str_replace('_', ' ', $po->status)),
            'created_at' => optional($po->created_at)->format('d M Y, H:i') ?: '',
            'detail_url' => route('admin.preorder-mobil.detail', $po),
        ];
    })->values()->all();
@endphp
```

Dengan struktur ini:
- Jumlah permintaan pada tombol tabel (`PO ($vActivePo)`) dan badge (`$vActivePo permintaan`) selalu sama persis dengan `count($vMatchingPreorders)`.
- Tidak ada kemungkinan angka di tombol dan isi modal saling bertolak belakang.

---

## 5. INTEGRASI DETAIL INLINE & TINJAU PRE-ORDER

Sistem modal pop-up tetap beroperasi penuh di tempat (*in-place*, tanpa reload / navigasi):
1. **Daftar Pre-Order:**
   - Menampilkan total aktif: `1 Permintaan`
   - Ringkasan stok fisik: `0 Unit (Stok Habis / Belum Ada)`
   - Model: `Bugatti Chiron (2024)`
   - Baris antrean: `PRE-00001`, `Lintar Yan Zuhara`, `PLG-202610-7EE538 • 087784304417`, `Super Sport (2024) - Atlantic Blue`, status `Menunggu stok`.
2. **Tombol Tinjau:**
   - Menjalankan `viewPreorderDetail(1, 'showcase:bugatti-chiron')`.
   - Mengambil data JSON dari endpoint `GET /admin/pre-order-mobil/1/detail` (HTTP 200).
   - Menampilkan formulir tinjauan lengkap dengan opsi alokasi unit fisik, kontak WhatsApp/telepon langsung ke member, dan transisi status secara mulus di dalam modal yang sama.
   - Tombol `← Kembali ke Daftar Pre-Order` mengembalikan tampilan ke daftar antrean seketika tanpa request ulang ke server.

---

## 6. HASIL PENGUJIAN REGRESI & AUTOMATED TEST SUITE

### 1. Test Khusus Sinkronisasi Pre-Order & Modal Payload:
Menambahkan pengujian otomatis ke-26 pada `tests/Feature/UnifiedVehicleManagementTest.php`:
`test_preorder_count_and_modal_json_payload_are_strictly_synchronized`
- Memeriksa kesesuaian badge `PO (1)`, `1 permintaan`, ketersediaan script `#car-data-showcase:bugatti-chiron`, keberadaan array `preorders`, serta atribut lengkap member pemesan.
- **Hasil:** `PASS` (1 test, 15 assertions).

### 2. Seluruh Test Suite Manajemen Kendaraan Terpadu:
```bash
phpunit tests/Feature/UnifiedVehicleManagementTest.php
OK (26 tests, 236 assertions) - Time: 17.198s
```

### 3. Konsistensi Katalog & Pre-Order:
```bash
phpunit tests/Feature/CatalogInventoryPreorderConsistencyTest.php
OK (9 tests, 115 assertions) - Time: 9.505s
```

### 4. Build Aset Frontend:
```bash
npm run build
✓ built in 4.53s (Vite v7.3.7)
```

---

## 7. STATUS & VERIFIKASI AKHIR

| Aspek Pengujian | Status | Keterangan |
|---|---|---|
| Read-Only Integrity | **TERJAGA** | Database operasional `yza_automotive_laravel` tidak mengalami perubahan data preorder. Record PRE-00001 utuh. |
| Konsistensi Jumlah Preorder | **TERJAMIN** | Tabel menampilkan `1 permintaan` / `PO (1)`, dan modal menampilkan `1 Permintaan` dengan rincian Lintar Yan Zuhara. |
| In-Place Modal Navigation | **AKTIF** | Navigasi tetap berada di modal; tidak ada perpindahan URL atau pembukaan tab baru. |
| Role & Security Guard | **TERLINDUNGI** | Role Owner tetap read-only (`403 Forbidden` pada form mutasi stok/tambah unit). |
| PHP Blade Regression | **BEBAS ERROR** | HTTP 200 pada seluruh tab (`inventori`, `katalog`, `foto`), tanpa notice, warning, atau error. |

