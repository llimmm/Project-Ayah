@extends('layouts.publik')
@section('judul', 'Beranda')

@section('isi')
@if (! $r['tanggal'])
    <div class="catatan">Belum ada data harga yang terverifikasi. Data akan tampil setelah enumerator menginput dan verifikator menyetujuinya.</div>
@else
<section class="pembuka">
    <div class="tanggal">Data {{ tgl($r['tanggal'], true) }} · {{ $jumlahPasar }} pasar dipantau</div>
    <h1>Harga {{ $r['naik'] }} komoditas naik, {{ $r['turun'] }} turun, dan {{ $r['stabil'] }} stabil dibanding data sebelumnya.</h1>
    <p>Rata-rata harga eceran di pasar tradisional Kabupaten Sumbawa Barat, dibandingkan dengan data {{ tgl($r['kemarin']) }}.
        Perubahan di bawah {{ persen(\App\Support\Harga::AMBANG_STABIL, 0, false) }} dihitung stabil.</p>
</section>

<section class="seksi grid g4" aria-label="Ringkasan">
    <div class="kartu stat">
        <h3>IPH minggu terakhir</h3>
        @php($iphAkhir = $iph->last())
        <div class="nilai">{{ $iphAkhir ? persen($iphAkhir->perubahan_iph) : '-' }}</div>
        <p class="ket">{{ $iphAkhir ? 'Minggu ke-' . $iphAkhir->minggu_ke . ' ' . bulan_nama($iphAkhir->bulan) . ' ' . $iphAkhir->tahun : 'Belum ada data' }}</p>
    </div>
    <div class="kartu stat">
        <h3>Inflasi NTB, tahunan (YoY)</h3>
        <div class="nilai">{{ $inflasi ? persen($inflasi->yoy, 2, false) : '-' }}</div>
        <p class="ket">{{ $inflasi ? bulan_nama($inflasi->bulan) . ' ' . $inflasi->tahun . ' · MtM ' . persen($inflasi->mtm) : 'Belum ada data' }}</p>
    </div>
    <div class="kartu stat">
        <h3>Di atas harga acuan (HET/HAP)</h3>
        <div class="nilai">{{ $r['di_atas_acuan'] }} <span class="muted kecil">komoditas</span></div>
        <p class="ket">Dari {{ $r['baris']->filter(fn ($b) => $b->komoditas->harga_acuan)->count() }} komoditas yang punya harga acuan</p>
    </div>
    <div class="kartu stat">
        <h3>Komoditas dipantau</h3>
        <div class="nilai">{{ $r['baris']->count() }}</div>
        <p class="ket">Naik {{ $r['naik'] }} · Turun {{ $r['turun'] }} · Stabil {{ $r['stabil'] }}</p>
    </div>
</section>

@if ($sorotan->isNotEmpty())
<section class="seksi">
    <h2>Pergerakan terbesar</h2>
    <div class="grid g4">
        @foreach ($sorotan as $s)
            <a class="kartu sorot" href="{{ route('komoditas', $s->komoditas) }}" style="color:inherit;text-decoration:none">
                <span class="nama">{{ $s->komoditas->nama }}</span>
                <span class="harga">{{ rupiah($s->harga) }}<span class="muted kecil">/{{ $s->komoditas->satuan }}</span></span>
                <x-arah :n="$s->ubah_harian" />
                <div style="height:44px;margin-top:8px"><canvas data-spark='@json($spark[$s->komoditas->id] ?? [])' data-arah="{{ $s->arah }}" aria-label="Tren 30 hari {{ $s->komoditas->nama }}" role="img"></canvas></div>
                <span class="kecil muted">30 hari terakhir</span>
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="seksi">
    <div class="kepala">
        <div>
            <h2>Harga rata-rata hari ini</h2>
            <p>Rata-rata semua pasar. Klik nama komoditas untuk melihat tren dan harga per pasar.</p>
        </div>
        <a class="btn garis" href="{{ route('harga') }}">Pilih pasar atau tanggal lain</a>
    </div>
    @include('publik._tabel_harga', ['baris' => $r['baris']])
</section>
@endif

<section class="seksi grid g2">
    <div class="kartu">
        <div class="kepala" style="margin-bottom:8px">
            <h2>Perubahan IPH mingguan</h2>
            <a href="{{ route('iph') }}" class="kecil">Lihat semua</a>
        </div>
        @if ($iph->isEmpty())
            <p class="muted">Belum ada data IPH.</p>
        @else
            <div class="legenda"><span><i class="kotak" style="background:var(--div-naik)"></i>Harga naik</span><span><i class="kotak" style="background:var(--div-turun)"></i>Harga turun</span></div>
            <div class="grafik pendek"><canvas id="g-iph" role="img" aria-label="Grafik perubahan IPH 12 minggu terakhir"></canvas></div>
            <p class="sumber">Sumber: BPS dan Kemendagri. IPH (Indikator Perubahan Harga) adalah proksi inflasi untuk kabupaten yang bukan kota IHK.</p>
        @endif
    </div>
    <div class="kartu">
        <div class="kepala" style="margin-bottom:8px">
            <h2>Berita & kegiatan TPID</h2>
            <a href="{{ route('berita') }}" class="kecil">Semua berita</a>
        </div>
        @forelse ($berita as $b)
            <a class="berita-kartu" href="{{ route('berita.baca', $b->slug) }}" style="padding:10px 0;border-bottom:1px solid var(--line)">
                <span class="kecil muted">{{ \App\Models\Berita::KATEGORI[$b->kategori] ?? $b->kategori }} · {{ tgl($b->terbit_pada) }}</span>
                <h3 style="margin:0">{{ $b->judul }}</h3>
            </a>
        @empty
            <p class="muted">Belum ada berita.</p>
        @endforelse
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
