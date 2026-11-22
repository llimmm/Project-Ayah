@extends('layouts.publik')
@section('judul', 'Panduan & Definisi')

@section('isi')
@include('publik._kepala', [
    'eyebrow' => 'Panduan',
    'judul' => 'Cara membaca data',
    'lead' => 'Asal data, arti angka, dan istilah yang dipakai di dashboard ini.',
])

<div class="wrap">
    <div class="grid g3">
        <div class="fitur teal">
            <span class="label">Naik · turun · stabil</span>
            <h3 class="title-lg" style="margin:0">Dibanding data sebelumnya</h3>
            <p class="ket">Perubahan di bawah {{ persen(\App\Support\Harga::AMBANG_STABIL, 0, false) }} dihitung stabil. "Ubah 7 hari" membandingkan dengan data seminggu sebelumnya.</p>
        </div>
        <div class="fitur pink">
            <span class="label">HET dan HAP</span>
            <h3 class="title-lg" style="margin:0">Batas harga dari pemerintah</h3>
            <p class="ket">Ditetapkan Badan Pangan Nasional dan Kementerian Perdagangan. Tanda "Di atas acuan" berarti rata-rata pasar melewati batas itu.</p>
        </div>
        <div class="fitur lavender">
            <span class="label">Koefisien variasi (KV)</span>
            <h3 class="title-lg" style="margin:0">Ukuran beda harga antarpasar</h3>
            <p class="ket">Simpangan baku dibagi rata-rata, dikali 100%. KV di atas 10% ditandai untuk ditelusuri.</p>
        </div>
    </div>

    <article class="artikel" style="margin-top:48px">
        <h2>Asal data harga harian</h2>
        <p>Enumerator mencatat harga eceran bahan pokok di pasar tradisional Kabupaten Sumbawa Barat setiap hari pasar.
            Verifikator dari dinas terkait memeriksa setiap isian sebelum ditayangkan. Harga yang belum diverifikasi tidak tampil di halaman publik.</p>
        <p>Angka "rata-rata semua pasar" adalah rata-rata sederhana dari harga di pasar yang melapor pada tanggal itu.</p>

        <h2>Indikator Perubahan Harga (IPH)</h2>
        <p>IPH dipakai sebagai proksi inflasi untuk kabupaten/kota yang tidak menjadi kota sampel Indeks Harga Konsumen (IHK), termasuk Sumbawa Barat.
            Angka IPH dihitung BPS dari data harga mingguan komoditas pangan dan dirilis dalam Rapat Koordinasi Pengendalian Inflasi Daerah bersama Kemendagri.
            Dashboard ini menayangkan angka IPH resmi tersebut, bukan menghitung sendiri dari data pasar.</p>
        <p>IPH positif berarti harga pangan secara umum naik dibanding bulan sebelumnya. Komoditas pemberi andil adalah komoditas yang paling mendorong perubahan itu.</p>

        <h2>Inflasi MtM, YtD, dan YoY</h2>
        <ul>
            <li><b>MtM (month-to-month):</b> perubahan IHK terhadap bulan sebelumnya.</li>
            <li><b>YtD (year-to-date):</b> perubahan IHK terhadap Desember tahun lalu.</li>
            <li><b>YoY (year-on-year):</b> perubahan IHK terhadap bulan yang sama tahun lalu. Angka ini yang dipakai untuk sasaran inflasi nasional.</li>
        </ul>

        <h2>Unduh data</h2>
        <p>Setiap tabel punya tombol <b>Unduh CSV</b>. Berkas bisa langsung dibuka di Excel. Grafik bisa disimpan lewat klik kanan, lalu "Simpan gambar".</p>
    </article>
</div>
@endsection
