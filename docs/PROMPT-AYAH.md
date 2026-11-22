# Prompt siap tempel

Dua prompt untuk Claude Code CLI. Proyek dikerjakan di komputer lokal saja: clone, revisi, dan commit lokal, tanpa push.

## 1. Pertama kali (komputer belum disiapkan)

Buka terminal di folder mana saja, jalankan `claude`, lalu tempel seluruh teks di bawah ini.

```text
Tolong siapkan dan jalankan proyek SIPANTAU Harga KSB di komputer ini, lalu bantu saya
merevisinya. Proyeknya dashboard pengendalian inflasi daerah Kabupaten Sumbawa Barat:
Laravel 12 + MySQL/MariaDB, tanpa Node dan tanpa build step.
Repo: https://github.com/llimmm/Project-Ayah.git

Semua pekerjaan hanya di komputer ini. Jangan pernah git push, jangan membuka situs ke
internet (tunnel, port forwarding, hosting), dan jangan deploy ke mana pun.

TAHAP 1: SIAPKAN STACK
1. Kenali sistem operasinya (Windows, macOS, atau Linux), lalu cek yang sudah terpasang:
   git, php (versi dan ekstensinya), composer, dan mysql/mariadb.
2. Pasang yang belum ada dengan cara yang paling umum untuk sistem operasi ini:
   - Windows: winget (cek ID paketnya dengan `winget search`). Kalau XAMPP atau Laragon
     sudah terpasang, pakai PHP dan MySQL dari situ.
   - macOS: Homebrew (php, composer, mariadb), jalankan mariadb lewat `brew services`.
   - Linux: apt atau dnf. Laravel 12 butuh PHP 8.2 atau lebih baru. Kalau versi bawaan
     terlalu lama, jelaskan pilihannya ke saya dulu sebelum memasang.
   Kebutuhan minimum: PHP 8.2+ dengan ekstensi pdo_mysql, mbstring, openssl, fileinfo,
   curl, zip, xml, tokenizer, ctype; Composer 2; MySQL 5.7+ atau MariaDB 10.4+; Git.
3. Sebelum menjalankan perintah yang butuh hak admin/sudo atau mengubah pengaturan sistem
   (PATH, php.ini, layanan), jelaskan singkat apa yang akan dilakukan dan minta izin saya.
4. Pastikan server database berjalan, dan ikut menyala lagi setelah komputer dinyalakan
   ulang kalau sistem operasinya mendukung.

TAHAP 2: PASANG PROYEK
1. Clone repo ke folder Project-Ayah di folder pengguna (home), kecuali saya minta lokasi
   lain. Kalau foldernya sudah ada, pakai yang ada lalu jalankan `git pull`.
2. Jalankan `composer install`, salin .env.example ke .env, lalu `php artisan key:generate`.
3. Buat user database khusus (misalnya `ksb`) dengan kata sandi acak, dan dua database
   utf8mb4: `inflasi_ksb` dan `inflasi_ksb_test`. Beri user itu akses penuh ke keduanya.
   Tulis kredensialnya hanya di .env (test memakai kredensial yang sama).
4. Jalankan `php artisan migrate --seed`, `php artisan db:seed --class=DemoSeeder`
   (data contoh 90 hari), dan `php artisan storage:link`.
5. Jalankan `php artisan test`. Hasilnya harus 12 passed. Kalau ada yang gagal, cari
   penyebabnya dan perbaiki dulu sebelum lanjut.
6. Jalankan `php artisan serve` di latar belakang dan pastikan http://127.0.0.1:8000
   bisa dibuka.

Setelah selesai, laporkan dengan bahasa sederhana:
- apa saja yang dipasang atau diubah di komputer ini,
- alamat situs dan alamat login (/masuk),
- akun untuk mencoba: email admin, verifikator, dan satu enumerator (kata sandi awalnya
  tertulis di README.md),
- hasil test.

TAHAP 3: PELAJARI PROYEK, LALU TUNGGU PERMINTAAN SAYA
Baca HANDOFF.md, README.md, dan docs/DESIGN-clay.md. Ringkas dalam beberapa kalimat apa
yang sudah ada dan apa yang belum dikerjakan, lalu tunggu permintaan saya.

ATURAN SELAMA REVISI
- Jelaskan dalam bahasa Indonesia sederhana. Hindari istilah teknis kecuali perlu.
- Untuk setiap permintaan: kerjakan, jalankan `php artisan test`, lalu sebutkan halaman
  mana yang perlu saya buka untuk melihat hasilnya.
- Kalau permintaan saya kurang jelas, atau ada beberapa cara yang hasilnya jauh berbeda,
  tanya dulu dengan pilihan singkat.
- Setelah saya setuju dengan hasilnya, buat commit lokal dengan pesan berbahasa Indonesia
  supaya mudah dibatalkan. Kalau saya bilang "batalkan" atau "kembalikan", tunjukkan dulu
  apa yang akan hilang, lalu kembalikan ke commit sebelumnya.
- Jangan ubah keputusan di tabel "Keputusan yang sudah diambil" di HANDOFF.md tanpa bertanya.
- Tampilan mengikuti docs/DESIGN-clay.md, dengan penyimpangan yang dicatat di HANDOFF.md:
  warna teks status versi gelap, palet grafik tervalidasi, arah harga selalu ikon + teks.
- Fitur baru wajib punya feature test di tests/Feature.
- Tanya dulu sebelum menghapus data di database atau menjalankan `migrate:fresh`. Kalau
  butuh data contoh lagi, jalankan ulang DemoSeeder.
- Jangan pernah git push.
```

## 2. Sesi berikutnya (komputer sudah disiapkan)

Buka terminal di folder `Project-Ayah`, jalankan `claude`, lalu tempel:

```text
Lanjutkan proyek SIPANTAU Harga KSB di folder ini.
1. Baca HANDOFF.md dan `git log --oneline -10` supaya tahu perubahan terakhir.
2. Pastikan database berjalan, jalankan `php artisan test`, lalu nyalakan
   `php artisan serve` di latar belakang.
3. Laporkan singkat: hasil test, alamat situs, dan perubahan terakhir. Lalu tunggu
   permintaan saya.
Aturan revisi tetap sama dengan aturan di docs/PROMPT-AYAH.md: bahasa sederhana, test
setiap perubahan, commit lokal setelah saya setuju, tanya dulu sebelum menghapus data,
dan jangan pernah git push.
```
