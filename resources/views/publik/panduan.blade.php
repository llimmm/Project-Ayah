@extends('layouts.publik')
@section('judul', 'Panduan & Definisi')

@section('isi')
<article class="artikel">
    <h1>Panduan membaca data</h1>
    <p class="muted">Konsep, sumber data, dan cara membaca angka di dashboard ini.</p>

    <h2 class="seksi">Asal data harga harian</h2>
    <p>Enumerator mencatat harga eceran bahan pokok di pasar tradisional Kabupaten Sumbawa Barat setiap hari pasar.
        Verifikator dari dinas terkait memeriksa setiap isian sebelum ditayangkan. Harga yang belum diverifikasi tidak tampil di halaman publik.</p>
    <p>Angka "rata-rata semua pasar" adalah rata-rata sederhana dari harga di pasar yang melapor pada tanggal itu.</p>

    <h2 class="seksi">Naik, turun, dan stabil</h2>
    <p>Perubahan harian membandingkan harga dengan data terverifikasi sebelumnya. Perubahan di bawah
        {{ persen(\App\Support\Harga::AMBANG_STABIL, 0, false) }} dihitung <b>stabil</b>. Perubahan 7 hari membandingkan dengan data seminggu sebelumnya.</p>

    <h2 class="seksi">Harga acuan (HET dan HAP)</h2>
    <p>HET (Harga Eceran Tertinggi) dan HAP (Harga Acuan Pembelian/Penjualan) adalah batas harga yang ditetapkan pemerintah pusat,
        misalnya oleh Badan Pangan Nasional dan Kementerian Perdagangan. Tanda "Di atas acuan" berarti harga rata-rata pasar melewati batas itu.
        Tidak semua komoditas punya harga acuan.</p>

    <h2 class="seksi">Disparitas dan KV</h2>
    <p>Disparitas adalah perbedaan harga satu komoditas antarpasar. Ukurannya koefisien variasi (KV): simpangan baku dibagi rata-rata, dikali 100%.
        KV di atas 10% ditandai sebagai perbedaan yang perlu ditelusuri.</p>

    <h2 class="seksi">Indikator Perubahan Harga (IPH)</h2>
    <p>IPH dipakai sebagai proksi inflasi untuk kabupaten/kota yang tidak menjadi kota sampel Indeks Harga Konsumen (IHK), termasuk Sumbawa Barat.
        Angka IPH dihitung BPS dari data harga mingguan komoditas pangan dan dirilis dalam Rapat Koordinasi Pengendalian Inflasi Daerah bersama Kemendagri.
        Dashboard ini menayangkan angka IPH resmi tersebut, bukan menghitung sendiri dari data pasar.</p>
    <p>IPH positif berarti harga pangan secara umum naik dibanding bulan sebelumnya. Komoditas pemberi andil adalah komoditas yang paling mendorong perubahan itu.</p>

    <h2 class="seksi">Inflasi MtM, YtD, dan YoY</h2>
    <ul>
        <li><b>MtM (month-to-month):</b> perubahan IHK terhadap bulan sebelumnya.</li>
        <li><b>YtD (year-to-date):</b> perubahan IHK terhadap Desember tahun lalu.</li>
        <li><b>YoY (year-on-year):</b> perubahan IHK terhadap bulan yang sama tahun lalu. Angka ini yang dipakai untuk sasaran inflasi nasional.</li>
    </ul>

    <h2 class="seksi">Unduh data</h2>
    <p>Setiap tabel punya tombol <b>Unduh CSV</b>. Berkas bisa langsung dibuka di Excel. Grafik bisa disimpan lewat klik kanan, lalu "Simpan gambar".</p>
</article>
@endsection
