@extends('layouts.publik')
@section('judul', $b->judul)
@section('deskripsi', $b->ringkasan)

@section('isi')
<article class="artikel">
    <p class="kecil"><a href="{{ route('berita') }}">Berita & kegiatan</a> / {{ \App\Models\Berita::KATEGORI[$b->kategori] ?? $b->kategori }}</p>
    <h1>{{ $b->judul }}</h1>
    <p class="muted kecil">{{ tgl($b->terbit_pada, true) }}@if ($b->penulis) · {{ $b->penulis->name }}@endif</p>
    @if ($b->gambar)<img src="{{ asset('storage/' . $b->gambar) }}" alt="" style="width:100%;border-radius:6px;margin:12px 0">@endif
    @if ($b->ringkasan)<p style="font-size:1.1rem"><b>{{ $b->ringkasan }}</b></p>@endif
    <div class="isi">
        @foreach (preg_split('/\R{2,}/', trim((string) $b->isi)) as $par)
            @if (trim($par) !== '')<p>{!! nl2br(e($par)) !!}</p>@endif
        @endforeach
    </div>
    @if ($b->lampiran)
        <p><a class="btn garis" href="{{ asset('storage/' . $b->lampiran) }}" target="_blank" rel="noopener">Unduh lampiran (PDF)</a></p>
    @endif
</article>

@if ($lain->isNotEmpty())
<section class="seksi">
    <h2>Lainnya</h2>
    <div class="grid g3">
        @foreach ($lain as $l)
            <a class="berita-kartu kartu" href="{{ route('berita.baca', $l->slug) }}">
                <span class="kecil muted">{{ tgl($l->terbit_pada) }}</span>
                <h3>{{ $l->judul }}</h3>
            </a>
        @endforeach
    </div>
</section>
@endif
@endsection
