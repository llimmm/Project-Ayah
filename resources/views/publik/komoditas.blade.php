@extends('layouts.publik')
@section('judul', $k->nama)

@section('isi')
@php($akhir = collect($tren['rata'])->last())
@php($awal = collect($tren['rata'])->first())
@php($pilih = '<form method="get" onchange="location.href=\'' . url('harga') . '/\'+this.k.value+\'?hari=' . $hari . '\'"><label class="eyebrow" for="ganti" style="margin-bottom:6px">Ganti komoditas</label><select id="ganti" name="k">'
    . $semua->map(fn ($s) => '<option value="' . $s->id . '"' . ($s->id === $k->id ? ' selected' : '') . '>' . e($s->nama) . '</option>')->implode('')
    . '</select></form>')
@include('publik._kepala', [
    'jejak' => '<a href="' . route('harga') . '">Harga harian</a> / ' . e($k->kategori),
    'eyebrow' => 'Per ' . $k->satuan . ' · rata-rata semua pasar',
    'judul' => $k->nama,
    'lead' => $hari . ' hari terakhir, sampai ' . tgl($sampai) . '.',
    'kanan' => $pilih,
])

<div class="wrap">
@if (empty($tren['label']))
    <div class="catatan">Belum ada data terverifikasi untuk komoditas ini pada rentang waktu tersebut.</div>
@else
    <div class="grid g4" style="margin-bottom:24px">
        <div class="fitur teal"><span class="label">Harga terakhir</span><span class="angka" style="font-size:40px">{{ rupiah($akhir) }}</span><p class="ket">rata-rata semua pasar</p></div>
        <div class="fitur lavender"><span class="label">Perubahan {{ $hari }} hari</span><span class="angka" style="font-size:40px">{{ persen(\App\Support\Harga::ubah($akhir, $awal)) }}</span><p class="ket">dari {{ rupiah($awal) }}</p></div>
        <div class="fitur peach"><span class="label">Terendah · tertinggi</span><span class="angka" style="font-size:26px;letter-spacing:-.5px">{{ rupiah($tren['min']) }} · {{ rupiah($tren['max']) }}</span><p class="ket">di salah satu pasar</p></div>
        <div class="fitur ochre"><span class="label">Harga acuan</span><span class="angka" style="font-size:40px">{{ rupiah($k->harga_acuan) }}</span><p class="ket">{{ $k->harga_acuan ? ($akhir > $k->harga_acuan ? 'Rata-rata di atas acuan' : 'Rata-rata di bawah acuan') : 'Tidak ada HET/HAP resmi' }}</p></div>
    </div>

    <div class="kartu lega">
        <div class="band-head" style="margin-bottom:0">
            <h3 class="title-lg" style="margin:0">Tren harga</h3>
            <nav class="tab" aria-label="Rentang waktu">
                @foreach ([30, 90, 180, 365] as $h)
                    <a href="?hari={{ $h }}" @class(['aktif' => $hari === $h])>{{ $h }} hari</a>
                @endforeach
            </nav>
        </div>
        <div class="legenda">
            <span><i style="background:var(--ink);height:3px"></i>Rata-rata semua pasar</span>
            @foreach ($tren['per_pasar'] as $i => $p)
                <span><i style="background:var(--series-{{ $i + 1 }})"></i>{{ $p['nama'] }}</span>
            @endforeach
        </div>
        <div class="grafik"><canvas id="g-tren" role="img" aria-label="Grafik tren harga {{ $k->nama }}"></canvas></div>
    </div>

    <section class="band-sm">
        <div class="band-head"><div><span class="eyebrow">Data</span><h2 style="font-size:32px;letter-spacing:-.5px">Harga per pasar</h2></div></div>
        <div class="tabel" style="max-height:520px">
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
</div>
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
