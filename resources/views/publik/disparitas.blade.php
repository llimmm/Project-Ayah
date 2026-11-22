@extends('layouts.publik')
@section('judul', 'Disparitas Harga Antarpasar')

@section('isi')
<div class="kepala">
    <div>
        <h1>Disparitas harga antarpasar</h1>
        <p>Selisih harga komoditas yang sama di pasar berbeda pada {{ $tanggal ? tgl($tanggal, true) : '-' }}.</p>
    </div>
</div>

<form class="filter" method="get">
    <label>Tanggal <input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ today()->toDateString() }}"></label>
    <button class="btn">Tampilkan</button>
    @if ($data->isNotEmpty())
        <a class="btn garis" href="{{ request()->fullUrlWithQuery(['unduh' => 'csv', 'tanggal' => $tanggal]) }}">Unduh CSV</a>
    @endif
</form>

<div class="catatan">
    <b>Cara membaca KV (koefisien variasi).</b> KV adalah simpangan baku dibagi rata-rata, dalam persen.
    Makin besar KV, makin jauh beda harga antarpasar. KV di atas 10% ditandai untuk ditelusuri, misalnya karena biaya angkut atau pasokan yang tidak merata.
</div>

@if ($data->isEmpty())
    <div class="catatan warning">Tidak ada data terverifikasi pada tanggal ini.</div>
@else
<div class="tabel-wrap">
    <table>
        <thead>
        <tr>
            <th>Komoditas</th>
            @foreach ($pasar as $p)<th class="r">{{ $p->nama }}</th>@endforeach
            <th class="r">Selisih</th>
            <th class="r">KV</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($data->sortByDesc('kv') as $d)
            <tr>
                <td><a href="{{ route('komoditas', $d->komoditas) }}">{{ $d->komoditas->nama }}</a><span class="sub">per {{ $d->komoditas->satuan }}</span></td>
                @foreach ($pasar as $p)
                    @php($h = $d->per_pasar[$p->id] ?? null)
                    <td class="r num">
                        @if ($h === null)
                            <span class="muted">-</span>
                        @else
                            {{ rupiah($h) }}
                            @if ($h === $d->min && $d->selisih)<span class="sub">terendah</span>@elseif ($h === $d->max && $d->selisih)<span class="sub">tertinggi</span>@endif
                        @endif
                    </td>
                @endforeach
                <td class="r num">{{ rupiah($d->selisih) }}</td>
                <td class="r num">
                    {{ persen($d->kv, 2, false) }}
                    @if ($d->kv > 10)<br><span class="lencana warning">Perlu ditelusuri</span>@endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p class="sumber">Diurutkan dari disparitas terbesar. Tanda "-" berarti pasar tersebut tidak melaporkan harga komoditas itu pada tanggal ini.</p>
@endif
@endsection
