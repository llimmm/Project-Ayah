# SIPANTAU Harga KSB

Dashboard Pengendalian Inflasi Daerah Kabupaten Sumbawa Barat. Ada dua bagian:

- **Situs publik**: harga harian bahan pokok per pasar, tren per komoditas, disparitas antarpasar, IPH mingguan, inflasi NTB, berita dan kegiatan TPID, serta panduan membaca data. Semua tabel bisa diunduh sebagai CSV.
- **Panel pengelola** (`/masuk`): enumerator menginput harga, verifikator memeriksa sebelum harga tayang, admin mengelola data master dan akun.

Stack: Laravel 12, PHP 8.2+, MySQL/MariaDB. Tidak perlu Node atau build step. CSS ada di `public/css/app.css`, Chart.js disimpan lokal di `public/vendor/`.

## Peran dan alur data

| Peran | Bisa apa |
|---|---|
| Enumerator | Input harga harian untuk **pasar tugasnya saja**, simpan draft, ajukan ke verifikator, perbaiki isian yang ditolak |
| Verifikator | Semua kemampuan enumerator untuk semua pasar, verifikasi (setujui/tolak dengan alasan), input IPH, inflasi, berita |
| Admin | Semua kemampuan verifikator, plus kelola komoditas, pasar, dan pengguna. Hanya admin yang bisa mengubah harga yang sudah disetujui |

```
Enumerator input  ->  draft  ->  diajukan  ->  Verifikator  ->  disetujui  ->  tampil di publik
                                                          \->  ditolak (dengan alasan) -> kembali ke enumerator
```

Halaman publik **hanya** menampilkan harga berstatus `disetujui`. Di form input, perubahan di atas 20% dari data sebelumnya ditandai kuning dan perlu konfirmasi kedua sebelum diajukan. Di layar verifikasi, baris yang menyimpang di atas 20% dari data sebelumnya atau dari rata-rata pasar lain juga ditandai.

IPH dan inflasi **tidak dihitung** dari data pasar. Keduanya diinput manual dari rilis resmi (IPH: BPS/Kemendagri, inflasi: BRS BPS NTB). Sumbawa Barat bukan kota sampel IHK.

## Menjalankan di lokal (XAMPP)

```bash
composer install
cp .env.example .env          # sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --seed    # tabel + data master + akun awal
php artisan db:seed --class=DemoSeeder   # OPSIONAL: data fiktif 90 hari untuk demo
php artisan storage:link
php artisan serve
```

Buka http://127.0.0.1:8000, panel di http://127.0.0.1:8000/masuk.

### Akun awal

Kata sandi semua akun awal sama: `ksb-ganti-segera`. Bisa diganti sebelum seeding dengan `SEED_PASSWORD=...` di `.env`. **Wajib diganti** setelah login pertama.

| Email | Peran |
|---|---|
| admin@inflasi.ksb | Admin |
| verifikator@inflasi.ksb | Verifikator |
| enum.taliwang@inflasi.ksb, enum.seteluk@..., enum.maluk@..., enum.jereweh@..., enum.brangrea@... | Enumerator per pasar |

## Yang wajib dicek bersama Dinas/TPID sebelum dipakai resmi

1. **Daftar pasar** di seeder (`database/seeders/DatabaseSeeder.php`) adalah contoh. Ganti dengan pasar yang benar-benar dipantau.
2. **Harga acuan (HET/HAP)** di seeder adalah contoh. Cocokkan dengan Perbadan/Permendag yang berlaku untuk zona NTB.
3. **Daftar komoditas**, satuan, dan urutannya.
4. Jangan jalankan `DemoSeeder` di server produksi. Semua berita demo berawalan `[DEMO]`.

## Deploy ke server Pemkab

Kebutuhan: PHP 8.2+ (ekstensi pdo_mysql, mbstring, openssl, fileinfo), MySQL 5.7+/MariaDB 10.4+, Composer.

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
# di .env: APP_ENV=production, APP_DEBUG=false, APP_URL=https://domain-resmi, kredensial DB
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

- Document root web server diarahkan ke folder `public/`, bukan ke root proyek.
- Folder `storage/` dan `bootstrap/cache/` harus bisa ditulis oleh user web server.
- Gunakan HTTPS. Login sudah dibatasi 5 percobaan per menit per email+IP.
- Backup database harian (tabel terpenting: `harga_harian`).

## Test

```bash
php artisan test
```

Test memakai database MySQL `inflasi_ksb_test` (lihat `phpunit.xml`), buat dulu: `CREATE DATABASE inflasi_ksb_test;`. Test mencakup alur input, ajukan, setujui, tolak, tampil publik, batas akses per peran, validasi tanggal, dan semua halaman publik dan panel.

## Peta kode

| Berkas | Isi |
|---|---|
| `app/Support/Harga.php` | Semua perhitungan: ringkasan naik/turun/stabil, tren, disparitas (KV) |
| `app/Http/Controllers/PublikController.php` | Halaman publik + unduh CSV |
| `app/Http/Controllers/Admin/HargaController.php` | Dasbor, input harga, verifikasi |
| `app/Http/Controllers/Admin/MasterController.php` | CRUD komoditas, pasar, pengguna, IPH, inflasi, berita (definisi field di satu tempat) |
| `app/Http/Middleware/Peran.php` | Cek peran (`peran:admin,verifikator`) |
| `database/migrations/2026_11_22_000001_create_inflasi_tables.php` | Skema tabel |
| `public/js/grafik.js` | Gaya grafik Chart.js yang seragam |

## Pengembangan berikutnya (belum dibuat)

- Impor harga dari Excel/CSV untuk input massal
- Peta sebaran harga per kecamatan
- Notifikasi WhatsApp/Telegram ke TPID saat harga melonjak
- API JSON untuk integrasi dengan SP2KP atau dashboard provinsi
