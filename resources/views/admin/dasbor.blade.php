@extends('layouts.admin')
@section('judul', 'Dasbor')

@section('isi')
@php($u = auth()->user())
<div class="kepala">
    <div>
        <h1>Dasbor</h1>
        <p>{{ tgl($hariIni, true) }}</p>
    </div>
    <a class="btn" href="{{ route('admin.input') }}">Input harga hari ini</a>
</div>

@if ($ditolak->isNotEmpty())
<div class="catatan salah">
    <b>Ada isian yang ditolak verifikator.</b> Buka, perbaiki, lalu ajukan lagi.
    <ul>
        @foreach ($ditolak as $d)
            <li><a href="{{ route('admin.input', ['tanggal' => \Illuminate\Support\Carbon::parse($d->tanggal)->toDateString(), 'pasar' => $d->pasar_id]) }}">{{ $d->pasar->nama }}, {{ tgl($d->tanggal) }}</a> ({{ $d->n }} harga)</li>
        @endforeach
    </ul>
</div>
@endif

<section>
    <h2>Progres input hari ini</h2>
    <p class="muted kecil">Jumlah komoditas aktif: {{ $jumlahKomoditas }}. Progres dihitung dari isian di setiap pasar, apa pun statusnya.</p>
    <div class="grid g3">
        @foreach ($progres as $p)
            @if ($u->role !== 'enumerator' || $u->pasar_id === $p->pasar->id)
            <div class="kartu">
                <div style="display:flex;justify-content:space-between;gap:8px">
                    <div><b>{{ $p->pasar->nama }}</b><span class="sub kecil muted" style="display:block">Kec. {{ $p->pasar->kecamatan }}</span></div>
                    <span class="num">{{ $p->total }}/{{ $jumlahKomoditas }}</span>
                </div>
                <div class="progres"><i style="width:{{ $jumlahKomoditas ? min(100, $p->total / $jumlahKomoditas * 100) : 0 }}%"></i></div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px">
                    @forelse ($p->status as $s => $n)
                        <span class="lencana {{ $s }}">{{ ucfirst($s) }} {{ $n }}</span>
                    @empty
                        <span class="lencana abu">Belum diisi</span>
                    @endforelse
                </div>
                <p style="margin:10px 0 0"><a class="kecil" href="{{ route('admin.input', ['pasar' => $p->pasar->id]) }}">Buka isian</a></p>
            </div>
            @endif
        @endforeach
    </div>
</section>

@if ($u->punya('admin', 'verifikator'))
<section class="seksi">
    <div class="kepala"><h2>Menunggu verifikasi</h2><a href="{{ route('admin.antrean') }}" class="kecil">Semua antrean</a></div>
    @if ($antrean->isEmpty())
        <p class="muted">Tidak ada isian yang menunggu verifikasi.</p>
    @else
        <div class="tabel-wrap">
            <table>
                <thead><tr><th>Tanggal</th><th>Pasar</th><th class="r">Jumlah harga</th><th></th></tr></thead>
                <tbody>
                @foreach ($antrean as $a)
                    @php($t = \Illuminate\Support\Carbon::parse($a->tanggal)->toDateString())
                    <tr><td>{{ tgl($t) }}</td><td>{{ $a->pasar->nama }}</td><td class="r num">{{ $a->n }}</td>
                        <td class="r"><a class="btn kecil" href="{{ route('admin.periksa', [$t, $a->pasar_id]) }}">Periksa</a></td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endif
@endsection
