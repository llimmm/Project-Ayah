@extends('layouts.publik')
@section('judul', 'Indikator Perubahan Harga (IPH)')

@section('isi')
@include('publik._kepala', ['eyebrow' => 'Indikator Perubahan Harga', 'judul' => 'IPH mingguan', 'lead' => 'Perubahan IPH Kabupaten Sumbawa Barat terhadap bulan sebelumnya, dari rilis BPS dan Kemendagri.'])

<div class="wrap">

<form class="filter" method="get">
    <label>Tahun
        <select name="tahun">
            <option value="">Semua tahun</option>
            @foreach ($daftarTahun as $t)<option @selected($tahun == $t)>{{ $t }}</option>@endforeach
        </select>
    </label>
    <button class="btn">Tampilkan</button>
    @if ($data->isNotEmpty())<a class="btn kedua" href="{{ request()->fullUrlWithQuery(['unduh' => 'csv']) }}">Unduh CSV</a>@endif
</form>

@if ($data->isEmpty())
    <div class="catatan">Belum ada data IPH.</div>
@else
<div class="grid g-7-5">
    <div class="kartu lega">
        <h3 class="title-lg">Perubahan IPH per minggu (%)</h3>
        <div class="legenda"><span><i class="kotak" style="background:var(--div-naik)"></i>Naik (harga menguat)</span><span><i class="kotak" style="background:var(--div-turun)"></i>Turun (harga melemah)</span></div>
        <div class="grafik"><canvas id="g-iph" role="img" aria-label="Grafik perubahan IPH mingguan"></canvas></div>
    </div>
    <div class="fitur lavender">
        <span class="label">Komoditas pemberi andil</span>
        <h3 class="title-lg" style="margin:0">Paling sering mendorong IPH</h3>
        <p class="body-sm">Berapa kali komoditas masuk daftar pemberi andil, dalam {{ $data->count() }} minggu yang ditampilkan.</p>
        @php($maks = max(1, $andil->max() ?? 1))
        @foreach ($andil as $nama => $n)
            <div style="margin:10px 0">
                <div style="display:flex;justify-content:space-between;font-size:.9rem"><span>{{ $nama }}</span><span class="num">{{ $n }} minggu</span></div>
                <div class="progres" style="background:rgba(10,10,10,.12)"><i style="width:{{ $n / $maks * 100 }}%"></i></div>
            </div>
        @endforeach
    </div>
</div>

<section class="band-sm">
    <div class="band-head"><div><span class="eyebrow">Data</span><h2>Tabel IPH</h2></div></div>
    <div class="tabel">
        <table>
            <thead><tr><th>Periode</th><th class="r">Perubahan IPH</th><th>Komoditas pemberi andil</th><th>Sumber</th></tr></thead>
            <tbody>
            @foreach ($data->reverse() as $d)
                <tr>
                    <td>Minggu ke-{{ $d->minggu_ke }} {{ bulan_nama($d->bulan) }} {{ $d->tahun }}</td>
                    <td class="r"><span class="arah {{ $d->perubahan_iph > 0 ? 'naik' : ($d->perubahan_iph < 0 ? 'turun' : 'stabil') }}">{{ persen($d->perubahan_iph) }}</span></td>
                    <td>{{ $d->komoditas_andil ?: '-' }}</td>
                    <td class="caption muted">{{ $d->sumber }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
</div>
@endsection

@push('js')
@if ($data->isNotEmpty())
<script>Grafik.batangPersen(document.getElementById('g-iph'), @json($data->map->label), @json($data->pluck('perubahan_iph')));</script>
@endif
@endpush
