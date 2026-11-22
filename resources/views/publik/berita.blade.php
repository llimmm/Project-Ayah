@extends('layouts.publik')
@section('judul', 'Berita & Kegiatan')

@section('isi')
@include('publik._kepala', ['eyebrow' => 'Berita & kegiatan', 'judul' => 'Kabar dari TPID', 'lead' => 'Rilis, kegiatan pengendalian harga, dan dokumen TPID Kabupaten Sumbawa Barat.'])

<div class="wrap">
<nav class="tab" style="margin-bottom:24px" aria-label="Kategori">
        <a href="{{ route('berita') }}" @class(['aktif' => ! $kategori])>Semua</a>
        @foreach (\App\Models\Berita::KATEGORI as $k => $l)
            <a href="{{ route('berita', ['kategori' => $k]) }}" @class(['aktif' => $kategori === $k])>{{ $l }}</a>
        @endforeach
    </nav>

<div class="grid g3">
    @php($warna = ['pink', 'teal', 'lavender', 'peach', 'ochre', 'krem'])
    @forelse ($berita as $i => $b)
        <a class="berita-kartu" href="{{ route('berita.baca', $b->slug) }}">
            <div class="sampul">
                @if ($b->gambar)
                    <img src="{{ asset('storage/' . $b->gambar) }}" alt="" loading="lazy">
                @else
                    <div class="fitur {{ $warna[$i % 6] }}" style="height:100%;border-radius:16px;justify-content:flex-end">
                        <span class="label">{{ \App\Models\Berita::KATEGORI[$b->kategori] ?? $b->kategori }}</span>
                    </div>
                @endif
            </div>
            <span class="caption muted">{{ \App\Models\Berita::KATEGORI[$b->kategori] ?? $b->kategori }} · {{ tgl($b->terbit_pada) }}</span>
            <h3>{{ $b->judul }}</h3>
            @if ($b->ringkasan)<p class="body-sm muted" style="margin:0">{{ \Illuminate\Support\Str::limit($b->ringkasan, 140) }}</p>@endif
        </a>
    @empty
        <p class="muted">Belum ada berita di kategori ini.</p>
    @endforelse
</div>
@include('admin._paginasi', ['p' => $berita])
</div>
@endsection
