@extends('layouts.publik')
@section('judul', 'Beranda')

@section('isi')
<section class="hero">
    <div class="wrap">
        <div>
            <span class="eyebrow">
                @if ($r['tanggal']) Data {{ tgl($r['tanggal'], true) }} · {{ $jumlahPasar }} pasar dipantau @else Pemantauan harga pangan @endif
            </span>
            <h1>Harga pangan Sumbawa Barat, terpantau setiap hari.</h1>
            @if ($r['tanggal'])
                <p class="lead">Pada data terakhir, <b>{{ $r['naik'] }} komoditas naik</b>, {{ $r['turun'] }} turun, dan {{ $r['stabil'] }} stabil
                    dibanding {{ tgl($r['kemarin']) }}. Semua angka berasal dari enumerator di pasar tradisional dan sudah diverifikasi.</p>
            @else
                <p class="lead">Belum ada data harga yang terverifikasi. Data akan tampil setelah enumerator menginput dan verifikator menyetujuinya.</p>
            @endif
            <div class="tombol">
                <a class="btn" href="{{ route('harga') }}">Lihat harga hari ini</a>
                <a class="btn kedua" href="{{ route('panduan') }}">Cara membaca data</a>
            </div>
        </div>
        <div class="ilustrasi"><x-ilustrasi jenis="pasar" /></div>
    </div>
</section>

<section class="band">
    <div class="wrap">
        @php($iphAkhir = $iph->last())
        <div class="grid g4">
            <a class="fitur teal" href="{{ route('iph') }}">
                <span class="label">IPH minggu terakhir</span>
                <span class="angka">{{ $iphAkhir ? persen($iphAkhir->perubahan_iph) : '-' }}</span>
                <p class="ket">{{ $iphAkhir ? 'Minggu ke-' . $iphAkhir->minggu_ke . ' ' . bulan_nama($iphAkhir->bulan) . ' ' . $iphAkhir->tahun : 'Belum ada data' }}</p>
            </a>
            <a class="fitur pink" href="{{ route('harga') }}">
                <span class="label">Di atas harga acuan</span>
                <span class="angka">{{ $r['di_atas_acuan'] }} <small>komoditas</small></span>
                <p class="ket">dari {{ $r['baris']->filter(fn ($b) => $b->komoditas->harga_acuan)->count() }} komoditas yang punya HET/HAP</p>
            </a>
            <a class="fitur lavender" href="{{ route('inflasi') }}">
                <span class="label">Inflasi NTB (YoY)</span>
                <span class="angka">{{ $inflasi ? persen($inflasi->yoy, 2, false) : '-' }}</span>
                <p class="ket">{{ $inflasi ? bulan_nama($inflasi->bulan) . ' ' . $inflasi->tahun . ' · bulanan ' . persen($inflasi->mtm) : 'Belum ada data' }}</p>
            </a>
            <a class="fitur peach" href="{{ route('disparitas') }}">
                <span class="label">Komoditas dipantau</span>
                <span class="angka">{{ $r['baris']->count() }}</span>
                <p class="ket">Naik {{ $r['naik'] }} · Turun {{ $r['turun'] }} · Stabil {{ $r['stabil'] }}</p>
            </a>
        </div>
    </div>
</section>

@if ($sorotan->isNotEmpty())
<section class="band">
    <div class="wrap">
        <div class="band-head">
            <div>
                <span class="eyebrow">Sorotan</span>
                <h2>Pergerakan terbesar</h2>
                <p>Empat komoditas dengan perubahan harga paling besar dibanding data sebelumnya.</p>
            </div>
        </div>
        <div class="grid g4">
            @foreach ($sorotan as $s)
                <a class="kartu gerak" href="{{ route('komoditas', $s->komoditas) }}">
                    <span class="nama">{{ $s->komoditas->nama }}</span>
                    <span class="harga">{{ rupiah($s->harga) }}<small> /{{ $s->komoditas->satuan }}</small></span>
                    <x-arah :n="$s->ubah_harian" />
                    <div class="spark"><canvas data-spark='@json($spark[$s->komoditas->id] ?? [])' data-arah="{{ $s->arah }}" role="img" aria-label="Tren 30 hari {{ $s->komoditas->nama }}"></canvas></div>
                    <span class="caption muted">30 hari terakhir</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($r['tanggal'])
<section class="band">
    <div class="wrap">
        <div class="band-head">
            <div>
                <span class="eyebrow">Harga terbaru</span>
                <h2>Rata-rata semua pasar</h2>
                <p>Klik nama komoditas untuk melihat tren dan harga di tiap pasar.</p>
            </div>
            <a class="btn kedua" href="{{ route('harga') }}">Pilih pasar atau tanggal</a>
        </div>
        @include('publik._tabel_harga', ['baris' => $r['baris']])
    </div>
</section>
@endif

<section class="band">
    <div class="wrap grid g-7-5">
        <div class="kartu lega">
            <div class="band-head" style="margin-bottom:8px">
                <div><span class="eyebrow">Proksi inflasi</span><h3 class="title-lg" style="margin:0">Perubahan IPH mingguan</h3></div>
                <a href="{{ route('iph') }}" class="btn teks">Lihat semua</a>
            </div>
            @if ($iph->isEmpty())
                <p class="muted">Belum ada data IPH.</p>
            @else
                <div class="legenda"><span><i class="kotak" style="background:var(--div-naik)"></i>Harga naik</span><span><i class="kotak" style="background:var(--div-turun)"></i>Harga turun</span></div>
                <div class="grafik pendek"><canvas id="g-iph" role="img" aria-label="Grafik perubahan IPH 12 minggu terakhir"></canvas></div>
                <p class="sumber">Sumber: BPS dan Kemendagri. IPH dipakai sebagai proksi inflasi karena Sumbawa Barat bukan kota sampel IHK.</p>
            @endif
        </div>
        <div class="fitur ochre">
            <span class="label">Berita & kegiatan TPID</span>
            @forelse ($berita as $b)
                <a class="berita-item" href="{{ route('berita.baca', $b->slug) }}">
                    <span class="caption">{{ \App\Models\Berita::KATEGORI[$b->kategori] ?? $b->kategori }} · {{ tgl($b->terbit_pada) }}</span>
                    <p class="title-sm">{{ $b->judul }}</p>
                </a>
            @empty
                <p>Belum ada berita.</p>
            @endforelse
            <div style="margin-top:auto;padding-top:16px"><a class="btn di-warna" href="{{ route('berita') }}">Semua berita</a></div>
        </div>
    </div>
</section>

<section class="band">
    <div class="wrap">
        <div class="cta">
            <div>
                <h2>Bingung membaca IPH atau disparitas?</h2>
                <p>Panduan singkat menjelaskan asal data, arti naik-turun harga, harga acuan HET/HAP, dan cara mengunduh tabel untuk laporan.</p>
                <a class="btn" href="{{ route('panduan') }}">Buka panduan</a>
            </div>
            <x-ilustrasi jenis="buku" />
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
document.querySelectorAll('canvas[data-spark]').forEach((c) => {
  const css = getComputedStyle(document.documentElement);
  const w = { naik: css.getPropertyValue('--naik'), turun: css.getPropertyValue('--turun') }[c.dataset.arah];
  Grafik.spark(c, JSON.parse(c.dataset.spark), w);
});
@if ($iph->isNotEmpty())
Grafik.batangPersen(document.getElementById('g-iph'), @json($iph->map->label), @json($iph->pluck('perubahan_iph')));
@endif
</script>
@endpush
