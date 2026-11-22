@extends('layouts.admin')
@section('judul', 'Verifikasi')

@section('isi')
<div class="kepala-panel">
    <div>
        <h1>Antrean verifikasi</h1>
        <p>Isian harga yang sudah diajukan enumerator, dikelompokkan per pasar dan tanggal.</p>
    </div>
</div>

@if ($grup->isEmpty())
    <div class="catatan ok">Semua isian sudah diverifikasi.</div>
@else
<div class="tabel">
    <table>
        <thead><tr><th>Tanggal</th><th>Pasar</th><th class="r">Jumlah harga</th><th>Diajukan terakhir</th><th></th></tr></thead>
        <tbody>
        @foreach ($grup as $g)
            @php($t = \Illuminate\Support\Carbon::parse($g->tanggal)->toDateString())
            <tr>
                <td>{{ tgl($t, true) }}</td>
                <td>{{ $g->pasar->nama }}</td>
                <td class="r num">{{ $g->n }}</td>
                <td class="caption muted">{{ \Illuminate\Support\Carbon::parse($g->terakhir)->diffForHumans() }}</td>
                <td class="r"><a class="btn kecil" href="{{ route('admin.periksa', [$t, $g->pasar_id]) }}">Periksa</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@include('admin._paginasi', ['p' => $grup])
@endif
@endsection
