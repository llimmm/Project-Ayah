@extends('layouts.publik')
@section('judul', $b->judul)
@section('deskripsi', $b->ringkasan)

@section('isi')
<section class="kepala-hal">
    <div class="wrap">
        <div class="artikel">
            <div class="jejak"><a href="{{ route('berita') }}">Berita & kegiatan</a> / {{ \App\Models\Berita::KATEGORI[$b->kategori] ?? $b->kategori }}</div>
            <span class="eyebrow">{{ tgl($b->terbit_pada, true) }}@if ($b->penulis) · {{ $b->penulis->name }}@endif</span>
            <h1>{{ $b->judul }}</h1>
            @if ($b->ringkasan)<p class="lead">{{ $b->ringkasan }}</p>@endif
        </div>
    </div>
</section>

<div class="wrap">
    <article class="artikel">
        @if ($b->gambar)<img src="{{ asset('storage/' . $b->gambar) }}" alt="" style="width:100%;border-radius:24px;margin-bottom:32px">@endif
        <div class="isi">
            @foreach (preg_split('/\R{2,}/', trim((string) $b->isi)) as $par)
                @if (trim($par) !== '')<p>{!! nl2br(e($par)) !!}</p>@endif
            @endforeach
        </div>
        @if ($b->lampiran)
            <p style="margin-top:32px"><a class="btn" href="{{ asset('storage/' . $b->lampiran) }}" target="_blank" rel="noopener">Unduh lampiran (PDF)</a></p>
        @endif
    </article>

    @if ($lain->isNotEmpty())
    <section class="band">
        <div class="band-head"><div><span class="eyebrow">Baca juga</span><h2>Kabar lainnya</h2></div></div>
        <div class="grid g3">
            @foreach ($lain as $i => $l)
                <a class="fitur {{ ['lavender', 'peach', 'krem'][$i % 3] }}" href="{{ route('berita.baca', $l->slug) }}">
                    <span class="label">{{ \App\Models\Berita::KATEGORI[$l->kategori] ?? $l->kategori }} · {{ tgl($l->terbit_pada) }}</span>
                    <h3 class="title-lg" style="margin:0">{{ $l->judul }}</h3>
                </a>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
