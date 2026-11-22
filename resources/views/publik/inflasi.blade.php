@extends('layouts.publik')
@section('judul', 'Inflasi')

@section('isi')
<div class="kepala">
    <div>
        <h1>Inflasi {{ $wilayah }}</h1>
        <p>Kabupaten Sumbawa Barat bukan kota sampel IHK, jadi pembandingnya inflasi Provinsi NTB dan kota IHK di NTB.</p>
    </div>
</div>

<form class="filter" method="get">
    <label>Wilayah
        <select name="wilayah">
            @foreach (\App\Models\Inflasi::WILAYAH as $w)<option @selected($wilayah === $w)>{{ $w }}</option>@endforeach
        </select>
    </label>
    <button class="btn">Tampilkan</button>
    @if ($data->isNotEmpty())<a class="btn garis" href="{{ request()->fullUrlWithQuery(['unduh' => 'csv']) }}">Unduh CSV</a>@endif
</form>

@if (! $terakhir)
    <div class="catatan">Belum ada data inflasi untuk wilayah ini.</div>
@else
<div class="grid g4" style="margin-bottom:16px">
    <div class="kartu stat"><h3>Bulanan (MtM)</h3><div class="nilai">{{ persen($terakhir->mtm) }}</div><p class="ket">{{ bulan_nama($terakhir->bulan) }} {{ $terakhir->tahun }} terhadap bulan sebelumnya</p></div>
    <div class="kartu stat"><h3>Tahun kalender (YtD)</h3><div class="nilai">{{ persen($terakhir->ytd) }}</div><p class="ket">terhadap Desember {{ $terakhir->tahun - 1 }}</p></div>
    <div class="kartu stat"><h3>Tahunan (YoY)</h3><div class="nilai">{{ persen($terakhir->yoy) }}</div><p class="ket">terhadap {{ bulan_nama($terakhir->bulan) }} {{ $terakhir->tahun - 1 }}</p></div>
    <div class="kartu stat"><h3>IHK</h3><div class="nilai">{{ angka($terakhir->ihk) }}</div><p class="ket">Indeks Harga Konsumen (2022=100)</p></div>
</div>

<div class="kartu">
    <h2>Perkembangan inflasi (%)</h2>
    <div class="legenda">
        <span><i style="background:var(--series-1)"></i>Bulanan (MtM)</span>
        <span><i style="background:var(--series-2)"></i>Tahun kalender (YtD)</span>
        <span><i style="background:var(--series-3)"></i>Tahunan (YoY)</span>
    </div>
    <div class="grafik"><canvas id="g-inflasi" role="img" aria-label="Grafik inflasi {{ $wilayah }}"></canvas></div>
    <p class="sumber">Sumber: Berita Resmi Statistik BPS Provinsi NTB.</p>
</div>

@if ($semuaWilayah->count() > 1)
<section class="seksi">
    <h2>Perbandingan wilayah, {{ bulan_nama($terakhir->bulan) }} {{ $terakhir->tahun }}</h2>
    <div class="tabel-wrap">
        <table>
            <thead><tr><th>Wilayah</th><th class="r">IHK</th><th class="r">MtM</th><th class="r">YtD</th><th class="r">YoY</th></tr></thead>
            <tbody>
            @foreach ($semuaWilayah as $w)
                <tr @if ($w->wilayah === $wilayah) style="background:var(--brand-soft)" @endif>
                    <td>{{ $w->wilayah }}</td><td class="r num">{{ angka($w->ihk) }}</td>
                    <td class="r num">{{ persen($w->mtm) }}</td><td class="r num">{{ persen($w->ytd) }}</td><td class="r num">{{ persen($w->yoy) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

<section class="seksi">
    <h2>Tabel data</h2>
    <div class="tabel-wrap">
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
