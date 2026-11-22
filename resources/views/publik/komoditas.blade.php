@extends('layouts.publik')
@section('judul', $k->nama)

@section('isi')
@php($akhir = collect($tren['rata'])->last())
@php($awal = collect($tren['rata'])->first())
<div class="kepala">
    <div>
        <p class="kecil"><a href="{{ route('harga') }}">Harga harian</a> / {{ $k->kategori }}</p>
        <h1>{{ $k->nama }}</h1>
        <p>Rata-rata semua pasar per {{ $k->satuan }}, {{ $hari }} hari sampai {{ tgl($sampai) }}.</p>
    </div>
    <form method="get" action="" onchange="location.href='{{ url('harga') }}/'+this.k.value+'?hari={{ $hari }}'">
        <label class="kecil muted">Ganti komoditas<br>
            <select name="k">
                @foreach ($semua as $s)
                    <option value="{{ $s->id }}" @selected($s->id === $k->id)>{{ $s->nama }}</option>
                @endforeach
            </select>
        </label>
    </form>
</div>

@if (empty($tren['label']))
    <div class="catatan">Belum ada data terverifikasi untuk komoditas ini pada rentang waktu tersebut.</div>
@else
<div class="grid g4" style="margin-bottom:16px">
    <div class="kartu stat"><h3>Harga terakhir</h3><div class="nilai">{{ rupiah($akhir) }}</div><p class="ket">rata-rata semua pasar</p></div>
    <div class="kartu stat"><h3>Perubahan {{ $hari }} hari</h3><div class="nilai"><x-arah :n="\App\Support\Harga::ubah($akhir, $awal)" /></div><p class="ket">dari {{ rupiah($awal) }}</p></div>
    <div class="kartu stat"><h3>Terendah · tertinggi</h3><div class="nilai" style="font-size:1.3rem">{{ rupiah($tren['min']) }} · {{ rupiah($tren['max']) }}</div><p class="ket">di salah satu pasar</p></div>
    <div class="kartu stat"><h3>Harga acuan (HET/HAP)</h3><div class="nilai">{{ rupiah($k->harga_acuan) }}</div><p class="ket">{{ $k->harga_acuan ? ($akhir > $k->harga_acuan ? 'Rata-rata di atas acuan' : 'Rata-rata di bawah acuan') : 'Tidak ada acuan resmi' }}</p></div>
</div>

<div class="kartu">
    <div class="kepala" style="margin-bottom:6px">
        <h2>Tren harga</h2>
        <div class="segmen">
            @foreach ([30, 90, 180, 365] as $h)
                <a href="?hari={{ $h }}" @class(['aktif' => $hari === $h])>{{ $h }} hari</a>
            @endforeach
        </div>
    </div>
    <div class="legenda">
        <span><i style="background:var(--ink);height:3px"></i>Rata-rata semua pasar</span>
        @foreach ($tren['per_pasar'] as $i => $p)
            <span><i style="background:var(--series-{{ $i + 1 }})"></i>{{ $p['nama'] }}</span>
        @endforeach
    </div>
    <div class="grafik"><canvas id="g-tren" role="img" aria-label="Grafik tren harga {{ $k->nama }}"></canvas></div>
</div>

<section class="seksi">
    <h2>Tabel harga per pasar</h2>
    <div class="tabel-wrap" style="max-height:480px">
        <table>
            <thead><tr><th>Tanggal</th><th class="r">Rata-rata</th>@foreach ($tren['per_pasar'] as $p)<th class="r">{{ $p['nama'] }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach (array_reverse(array_keys($tren['label'])) as $i)
                <tr>
                    <td class="num">{{ tgl($tren['label'][$i]) }}</td>
                    <td class="r num"><b>{{ rupiah($tren['rata'][$i]) }}</b></td>
                    @foreach ($tren['per_pasar'] as $p)<td class="r num">{{ rupiah($p['data'][$i]) }}</td>@endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
@endsection

@push('js')
@if (! empty($tren['label']))
<script>
Grafik.hargaGaris(document.getElementById('g-tren'), @json($tren['label']), [
  { nama: 'Rata-rata', data: @json($tren['rata']), utama: true },
  ...@json($tren['per_pasar']).map((p) => ({ nama: p.nama, data: p.data })),
]);
</script>
@endif
@endpush
