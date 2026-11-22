@extends('layouts.publik')
@section('judul', 'Berita & Kegiatan')

@section('isi')
<div class="kepala">
    <div>
        <h1>Berita & kegiatan TPID</h1>
        <p>Rilis, kegiatan pengendalian harga, dan dokumen TPID Kabupaten Sumbawa Barat.</p>
    </div>
    <div class="segmen">
        <a href="{{ route('berita') }}" @class(['aktif' => ! $kategori])>Semua</a>
        @foreach (\App\Models\Berita::KATEGORI as $k => $l)
            <a href="{{ route('berita', ['kategori' => $k]) }}" @class(['aktif' => $kategori === $k])>{{ $l }}</a>
        @endforeach
    </div>
</div>

<div class="grid g3">
    @forelse ($berita as $b)
        <a class="berita-kartu kartu" href="{{ route('berita.baca', $b->slug) }}">
            @if ($b->gambar)<img src="{{ asset('storage/' . $b->gambar) }}" alt="" loading="lazy">@endif
            <span class="kecil muted">{{ \App\Models\Berita::KATEGORI[$b->kategori] ?? $b->kategori }} · {{ tgl($b->terbit_pada) }}</span>
            <h3>{{ $b->judul }}</h3>
            @if ($b->ringkasan)<p class="muted kecil" style="margin:0">{{ \Illuminate\Support\Str::limit($b->ringkasan, 140) }}</p>@endif
        </a>
    @empty
        <p class="muted">Belum ada berita di kategori ini.</p>
    @endforelse
</div>
@include('admin._paginasi', ['p' => $berita])
@endsection
