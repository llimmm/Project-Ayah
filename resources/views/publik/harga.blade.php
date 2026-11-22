@extends('layouts.publik')
@section('judul', 'Harga Harian')

@section('isi')
@include('publik._kepala', [
    'eyebrow' => 'Harga harian',
    'judul' => 'Harga bahan pokok',
    'lead' => ($pasarId ? $pasar->firstWhere('id', $pasarId)?->nama : 'Rata-rata semua pasar')
        . ($r['tanggal'] ? ', ' . tgl($r['tanggal'], true) : '')
        . ($r['kemarin'] ? '. Dibanding data ' . tgl($r['kemarin']) . '.' : '.'),
])

<div class="wrap">
    <form class="filter" method="get">
        <label>Tanggal
            <input type="date" name="tanggal" value="{{ $r['tanggal'] }}" max="{{ today()->toDateString() }}">
        </label>
        <label>Pasar
            <select name="pasar">
                <option value="">Semua pasar (rata-rata)</option>
                @foreach ($pasar as $p)
                    <option value="{{ $p->id }}" @selected($pasarId == $p->id)>{{ $p->nama }} ({{ $p->kecamatan }})</option>
                @endforeach
            </select>
        </label>
        <button class="btn">Tampilkan</button>
        @if ($r['baris']->isNotEmpty())
            <a class="btn kedua" href="{{ request()->fullUrlWithQuery(['unduh' => 'csv', 'tanggal' => $r['tanggal']]) }}">Unduh CSV</a>
        @endif
    </form>

    @if ($r['baris']->isNotEmpty())
    <div class="grid g4" style="margin-bottom:24px">
        <div class="fitur pink"><span class="label">Naik</span><span class="angka">{{ $r['naik'] }}</span><p class="ket">komoditas</p></div>
        <div class="fitur teal"><span class="label">Turun</span><span class="angka">{{ $r['turun'] }}</span><p class="ket">komoditas</p></div>
        <div class="fitur krem"><span class="label">Stabil</span><span class="angka">{{ $r['stabil'] }}</span><p class="ket">perubahan di bawah {{ persen(\App\Support\Harga::AMBANG_STABIL, 0, false) }}</p></div>
        <div class="fitur peach"><span class="label">Di atas harga acuan</span><span class="angka">{{ $r['di_atas_acuan'] }}</span><p class="ket">komoditas</p></div>
    </div>
    @endif

    @include('publik._tabel_harga', ['baris' => $r['baris']])
    <p class="sumber">Harga eceran hasil pemantauan enumerator, dalam rupiah. Hanya data yang sudah diverifikasi yang ditampilkan.
        Kolom "Ubah 7 hari" membandingkan dengan data {{ $r['minggu_lalu'] ? tgl($r['minggu_lalu']) : 'seminggu sebelumnya' }}.</p>
</div>
@endsection
