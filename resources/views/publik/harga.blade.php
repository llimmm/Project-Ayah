@extends('layouts.publik')
@section('judul', 'Harga Harian')

@section('isi')
<div class="kepala">
    <div>
        <h1>Harga harian bahan pokok</h1>
        <p>{{ $pasarId ? $pasar->firstWhere('id', $pasarId)?->nama : 'Rata-rata semua pasar' }}
            @if ($r['tanggal']) · {{ tgl($r['tanggal'], true) }} @endif
            @if ($r['kemarin']) · dibanding {{ tgl($r['kemarin']) }} @endif
        </p>
    </div>
</div>

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
        <a class="btn garis" href="{{ request()->fullUrlWithQuery(['unduh' => 'csv', 'tanggal' => $r['tanggal']]) }}">Unduh CSV</a>
    @endif
</form>

@if ($r['baris']->isNotEmpty())
<div class="grid g4" style="margin-bottom:16px">
    <div class="kartu stat"><h3>Naik</h3><div class="nilai">{{ $r['naik'] }}</div><p class="ket">komoditas</p></div>
    <div class="kartu stat"><h3>Turun</h3><div class="nilai">{{ $r['turun'] }}</div><p class="ket">komoditas</p></div>
    <div class="kartu stat"><h3>Stabil</h3><div class="nilai">{{ $r['stabil'] }}</div><p class="ket">perubahan di bawah {{ persen(\App\Support\Harga::AMBANG_STABIL, 0, false) }}</p></div>
    <div class="kartu stat"><h3>Di atas harga acuan</h3><div class="nilai">{{ $r['di_atas_acuan'] }}</div><p class="ket">komoditas</p></div>
</div>
@endif

@include('publik._tabel_harga', ['baris' => $r['baris']])
<p class="sumber">Harga eceran hasil pemantauan enumerator, satuan rupiah. Hanya data yang sudah diverifikasi yang ditampilkan.
    Kolom "Ubah 7 hari" membandingkan dengan data {{ $r['minggu_lalu'] ? tgl($r['minggu_lalu']) : 'seminggu sebelumnya' }}.</p>
@endsection
