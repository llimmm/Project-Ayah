# SIPANTAU Harga KSB

Dashboard pengendalian inflasi daerah Kabupaten Sumbawa Barat. Laravel 12 + MySQL, tanpa build step.

- Baca `HANDOFF.md` sebelum mengerjakan apa pun: status, keputusan, aturan desain, dan jebakan ada di sana.
- Pekerjaan tampilan mengikuti `docs/DESIGN-clay.md`, dengan penyimpangan yang dicatat di `HANDOFF.md`.
- Bahasa Indonesia untuk teks antarmuka, penjelasan, dan pesan commit.
- Test: `php artisan test` (MySQL, database `inflasi_ksb_test`). Harus lolos sebelum pekerjaan dinyatakan selesai.
- Kerja lokal saja: commit lokal setelah revisi disetujui, jangan pernah `git push`, jangan membuka situs ke internet.
- Prompt kerja ada di `docs/PROMPT-AYAH.md`.
- Jangan menulis kata sandi atau isi `.env` ke file yang di-commit.
