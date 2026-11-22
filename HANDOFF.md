# Handoff: SIPANTAU Harga KSB

Catatan serah terima untuk melanjutkan proyek ini di perangkat lain dengan Claude Code CLI. Pengerjaan lanjutan dilakukan di lokal saja (clone, revisi, commit lokal, tanpa push).
Ditulis 22 November 2026, setelah versi pertama dan perombakan desain selesai.

## Proyek ini apa

Dashboard Pengendalian Inflasi Daerah untuk TPID Kabupaten Sumbawa Barat (proyek klien Pemkab).
Ada dua bagian:

- **Situs publik**: harga harian bahan pokok per pasar, tren per komoditas, disparitas antarpasar (koefisien variasi), IPH mingguan, inflasi NTB, berita dan kegiatan TPID, panduan membaca data. Semua tabel bisa diunduh sebagai CSV.
- **Panel pengelola** (`/masuk`): enumerator menginput harga pasar tugasnya, verifikator menyetujui atau menolak dengan alasan, admin mengelola komoditas, pasar, dan akun. Publik hanya melihat harga berstatus `disetujui`.

Stack: Laravel 12, PHP 8.2+, MySQL/MariaDB. Tidak perlu Node atau build step. Chart.js dan font Inter disimpan lokal di `public/`.

Rincian peran, alur data, dan peta kode ada di `README.md`.

## Keputusan yang sudah diambil (jangan diubah tanpa alasan)

| Keputusan | Alasan |
|---|---|
| Laravel + MySQL, Blade biasa, tanpa build step | Gampang diserahkan dan di-hosting di server Pemkab |
| Tiga peran: admin, verifikator, enumerator | Harga harus diverifikasi sebelum tayang |
| IPH dan inflasi diinput manual dari rilis resmi, tidak dihitung dari harga pasar | Sumbawa Barat bukan kota sampel IHK; angka IPH resmi dari BPS dan Kemendagri |
| Pembanding inflasi: Provinsi NTB, Kota Mataram, Kota Bima, Kab. Sumbawa | Kota IHK terdekat |
| Zona waktu `Asia/Makassar` (WITA) | Lokasi KSB |
| Desain mengikuti `docs/DESIGN-clay.md` | Permintaan pemilik proyek |

### Aturan desain yang sudah diterapkan

- Latar krem `#fffaf0`, judul Inter weight 500 dengan letter-spacing negatif, tombol hitam bersudut 12px, kartu warna jenuh berurutan teal, pink, lavender, peach, oker (tidak boleh dua warna sama berdampingan), footer krem dengan ilustrasi bukit.
- Ilustrasi bergaya tanah liat digambar sendiri sebagai SVG di `resources/views/components/ilustrasi.blade.php`. Jangan memakai aset, logo, atau maskot Clay.
- **Penyimpangan yang disengaja dari panduan desain, demi keterbacaan:**
  - Teks naik, turun, dan waspada memakai versi gelap `#b42318`, `#157a3c`, `#8a5300`. Merah dan hijau dari panduan kontrasnya hanya 3,6:1 dan 2,2:1 di latar krem.
  - Teks di kartu pink memakai warna hitam. Putih di atas pink hanya 3,1:1.
  - Warna garis grafik memakai palet tervalidasi buta warna (`--series-1` sampai `--series-8` di `public/css/app.css`), bukan warna kartu Clay. Naik/turun pada grafik IPH memakai pasangan merah `#e34948` dan biru `#2a78d6`.
  - Arah harga selalu ikon plus teks, tidak pernah warna saja.

## Status per 22 November 2026

Sudah jalan:

- Semua halaman publik dan panel pengelola, termasuk unduh CSV dan tampilan HP (menu lipat, tanpa geser ke samping).
- 12 feature test lolos (`tests/Feature/AlurHargaTest.php`): alur input, ajukan, setujui, tolak, tampil publik, batas akses per peran, tanggal masa depan ditolak, akun nonaktif, dan semua halaman.
- Pernah online lewat Cloudflare quick tunnel dari laptop pemilik proyek. **Tunnel sudah dimatikan.**

Belum dikerjakan:

1. **Data master masih contoh.** Nama pasar (`Pasar Taliwang`, `Seteluk`, `Maluk`, `Jereweh`, `Brang Rea`) dan harga acuan HET/HAP di `database/seeders/DatabaseSeeder.php` harus dicocokkan dengan Dinas Perdagangan KSB. Nama kecamatan (8 kecamatan) sudah benar.
2. **Domain.** Pemilik proyek belum punya domain. Pilihannya: beli domain (misalnya `.my.id`) lalu pakai Cloudflare named tunnel, atau minta subdomain `.go.id` ke Diskominfo KSB.
3. **Hosting permanen.** Saat ini hanya jalan di lokal. Langkah deploy ada di `README.md`.
4. **Fitur lanjutan** yang sudah diusulkan tapi belum dibuat: impor harga dari Excel/CSV, peta harga per kecamatan, notifikasi lonjakan harga ke WhatsApp/Telegram, API JSON.

## Menyiapkan di perangkat baru

Kebutuhan: Git, PHP 8.2+ (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`), Composer, MySQL 5.7+ atau MariaDB 10.4+. Di Windows paling mudah memakai XAMPP (di laptop asal: PHP 8.2.12, MariaDB 10.4.32).

```bash
git clone https://github.com/llimmm/Project-Ayah.git
cd Project-Ayah
composer install
cp .env.example .env
php artisan key:generate
```

Buat dua database (yang kedua untuk test):

```sql
CREATE DATABASE inflasi_ksb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE inflasi_ksb_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env`, lalu:

```bash
php artisan migrate --seed                 # tabel, data master, akun awal
php artisan db:seed --class=DemoSeeder     # opsional: data fiktif 90 hari untuk demo
php artisan storage:link
php artisan serve                          # buka http://127.0.0.1:8000
php artisan test                           # harus 12 passed
```

**Akun awal.** Semua akun hasil seeder memakai kata sandi `ksb-ganti-segera`, kecuali `SEED_PASSWORD` diisi di `.env` sebelum `migrate --seed`. Daftar email ada di `README.md`. Ganti kata sandi setelah login pertama. Repo ini publik: jangan pernah menulis kata sandi asli ke dalam repo.

Database di laptop asal tidak ikut dipindahkan. Perangkat baru mulai dari database kosong hasil seeder.

## Jebakan yang sudah ditemukan

- **Test memakai MySQL, bukan SQLite.** SQLite menyimpan kolom tanggal sebagai `2026-11-22 00:00:00`, sehingga pencarian per tanggal meleset. `phpunit.xml` sudah diarahkan ke database `inflasi_ksb_test`.
- **Cloudflare quick tunnel lewat QUIC putus-putus** di jaringan laptop asal. Pakai HTTP/2:
  `cloudflared tunnel --no-autoupdate --protocol http2 --url http://127.0.0.1:8000`
  Alamat `*.trycloudflare.com` berganti setiap kali tunnel dijalankan ulang.
- **Sebelum membuka situs ke internet**, set `APP_ENV=production` dan `APP_DEBUG=false` di `.env`, dan ganti semua kata sandi bawaan. Kalau debug menyala, error akan memperlihatkan isi `.env`.
- **HTTPS di balik tunnel**: `bootstrap/app.php` memercayai proxy dari `127.0.0.1` supaya URL CSS/JS ditulis `https://`. Kalau cloudflared atau reverse proxy jalan di mesin lain, alamatnya harus ditambahkan di situ.
- **Label pembaca layar** (`.sr`, `position: absolute`) di dalam tabel pernah membuat halaman HP bisa digeser ke samping. Perbaikannya `.tabel { position: relative }`. Jangan dihapus.
- **DemoSeeder** melewati hari Minggu dan menandai hari data terakhir sebagai `diajukan`, supaya antrean verifikasi punya contoh. Berita demo berawalan `[DEMO]`. Jangan dijalankan di server produksi.

## Peta kode singkat

| Berkas | Isi |
|---|---|
| `app/Support/Harga.php` | Semua perhitungan: ringkasan naik/turun/stabil, tren, disparitas |
| `app/Http/Controllers/PublikController.php` | Halaman publik dan unduh CSV |
| `app/Http/Controllers/Admin/HargaController.php` | Dasbor, input harga, verifikasi |
| `app/Http/Controllers/Admin/MasterController.php` | CRUD komoditas, pasar, pengguna, IPH, inflasi, berita (definisi field di satu tempat) |
| `public/css/app.css` | Seluruh gaya, token warna di `:root` |
| `public/js/grafik.js` | Pembungkus Chart.js |
| `resources/views/components/ilustrasi.blade.php` | Ilustrasi SVG (pasar, buku, bukit, logo) |
| `docs/DESIGN-clay.md` | Panduan desain acuan |

## Prompt siap tempel

Ada di `docs/PROMPT-AYAH.md`:

1. **Pertama kali**: Claude menyiapkan stack (PHP, Composer, MySQL/MariaDB, Git), clone repo, membuat database dan `.env`, menjalankan test dan server, lalu siap menerima revisi.
2. **Sesi berikutnya**: Claude membaca catatan ini, menyalakan server, dan menunggu permintaan.

Proyek dikerjakan di lokal saja: revisi di-commit lokal, tanpa push.
