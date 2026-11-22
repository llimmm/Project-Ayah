@extends('layouts.publik')
@section('judul', 'Inflasi')

@section('isi')
@include('publik._kepala', ['eyebrow' => 'Inflasi', 'judul' => 'Inflasi ' . $wilayah, 'lead' => 'Sumbawa Barat bukan kota sampel IHK, jadi pembandingnya inflasi Provinsi NTB dan kota IHK di NTB.'])

<div class="wrap">

<form class="filter" method="get">
    <label>Wilayah
        <select name="wilayah">
            @foreach (\App\Models\Inflasi::WILAYAH as $w)<option @selected($wilayah === $w)>{{ $w }}</option>@endforeach
        </select>
    </label>
    <button class="btn">Tampilkan</button>
    @if ($data->isNotEmpty())<a class="btn kedua" href="{{ request()->fullUrlWithQuery(['unduh' => 'csv']) }}">Unduh CSV</a>@endif
</form>

@if (! $terakhir)
    <div class="catatan">Belum ada data inflasi untuk wilayah ini.</div>
@else
<div class="grid g4" style="margin-bottom:24px">
    <div class="fitur teal"><span class="label">Bulanan (MtM)</span><span class="angka">{{ persen($terakhir->mtm) }}</span><p class="ket">{{ bulan_nama($terakhir->bulan) }} {{ $terakhir->tahun }} terhadap bulan sebelumnya</p></div>
    <div class="fitur pink"><span class="label">Tahun kalender (YtD)</span><span class="angka">{{ persen($terakhir->ytd) }}</span><p class="ket">terhadap Desember {{ $terakhir->tahun - 1 }}</p></div>
    <div class="fitur lavender"><span class="label">Tahunan (YoY)</span><span class="angka">{{ persen($terakhir->yoy) }}</span><p class="ket">terhadap {{ bulan_nama($terakhir->bulan) }} {{ $terakhir->tahun - 1 }}</p></div>
    <div class="fitur peach"><span class="label">IHK</span><span class="angka">{{ angka($terakhir->ihk) }}</span><p class="ket">Indeks Harga Konsumen (2022=100)</p></div>
</div>

<div class="kartu lega">
    <h3 class="title-lg">Perkembangan inflasi (%)</h3>
    <div class="legenda">
        <span><i style="background:var(--series-1)"></i>Bulanan (MtM)</span>
        <span><i style="background:var(--series-2)"></i>Tahun kalender (YtD)</span>
        <span><i style="background:var(--series-3)"></i>Tahunan (YoY)</span>
    </div>
    <div class="grafik"><canvas id="g-inflasi" role="img" aria-label="Grafik inflasi {{ $wilayah }}"></canvas></div>
    <p class="sumber">Sumber: Berita Resmi Statistik BPS Provinsi NTB.</p>
</div>

@if ($semuaWilayah->count() > 1)
<section class="band-sm">
    <div class="band-head"><div><span class="eyebrow">{{ bulan_nama($terakhir->bulan) }} {{ $terakhir->tahun }}</span><h2>Perbandingan wilayah</h2></div></div>
    <div class="tabel">
        <table>
            <thead><tr><th>Wilayah</th><th class="r">IHK</th><th class="r">MtM</th><th class="r">YtD</th><th class="r">YoY</th></tr></thead>
            <tbody>
            @foreach ($semuaWilayah as $w)
                <tr @if ($w->wilayah === $wilayah) style="background:var(--surface-card)" @endif>
                    <td>{{ $w->wilayah }}</td><td class="r num">{{ angka($w->ihk) }}</td>
                    <td class="r num">{{ persen($w->mtm) }}</td><td class="r num">{{ persen($w->ytd) }}</td><td class="r num">{{ persen($w->yoy) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

<section class="band-sm">
    <div class="band-head"><div><span class="eyebrow">Data</span><h2>Tabel inflasi</h2></div></div>
    <div class="tabel">
        <table>
            <thead><tr><th>Bulan</th><th class="r">IHK</th><th class="r">MtM</th><th class="r">YtD</th><th class="r">YoY</th></tr></thead>
            <tbody>
            @foreach ($data->reverse() as $d)
                <tr><td>{{ bulan_nama($d->bulan) }} {{ $d->tahun }}</td><td class="r num">{{ angka($d->ihk) }}</td><td class="r num">{{ persen($d->mtm) }}</td><td class="r num">{{ persen($d->ytd) }}</td><td class="r num">{{ persen($d->yoy) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
</div>
@endsection

@push('js')
@if ($terakhir)
<script>
Grafik.persenGaris(document.getElementById('g-inflasi'), @json($data->map->label), [
  { nama: 'MtM', data: @json($data->pluck('mtm')) },
  { nama: 'YtD', data: @json($data->pluck('ytd')) },
  { nama: 'YoY', data: @json($data->pluck('yoy')) },
]);
</script>
@endif
@endpush
