<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Beranda') · SIPANTAU Harga Kabupaten Sumbawa Barat</title>
    <meta name="description" content="@yield('deskripsi', 'Dashboard pemantauan harga bahan pokok dan pengendalian inflasi daerah Kabupaten Sumbawa Barat.')">
    <meta name="theme-color" content="#fffaf0">
    <link rel="preload" href="{{ asset('fonts/inter-latin-wght.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2">
</head>
<body>
@php($menu = ['beranda' => 'Beranda', 'harga' => 'Harga Harian', 'disparitas' => 'Disparitas', 'iph' => 'IPH', 'inflasi' => 'Inflasi', 'berita' => 'Berita', 'panduan' => 'Panduan'])
@php($aktif = fn ($r) => request()->routeIs($r, $r . '.*') || ($r === 'harga' && request()->routeIs('komoditas')))
<header class="top" id="top">
    <div class="wrap">
        <a href="{{ route('beranda') }}" class="merek">
            <x-ilustrasi jenis="logo" />
            <span><b>SIPANTAU Harga</b><span>Kabupaten Sumbawa Barat</span></span>
        </a>
        <nav class="menu" aria-label="Menu utama">
            @foreach ($menu as $r => $l)
                <a href="{{ route($r) }}" @class(['aktif' => $aktif($r)])>{{ $l }}</a>
            @endforeach
        </nav>
        <div class="aksi-top">
            @auth
                <a class="btn" href="{{ route('admin.dasbor') }}">Panel pengelola</a>
            @else
                <a class="btn teks" href="{{ route('login') }}">Masuk</a>
                <a class="btn" href="{{ route('harga') }}">Cek harga hari ini</a>
            @endauth
        </div>
        <details class="burger">
            <summary class="btn kedua kecil" aria-label="Buka menu">Menu</summary>
        </details>
    </div>
    <nav class="menu-hp" aria-label="Menu ponsel">
        @foreach ($menu as $r => $l)<a href="{{ route($r) }}">{{ $l }}</a>@endforeach
        <a href="{{ auth()->check() ? route('admin.dasbor') : route('login') }}">{{ auth()->check() ? 'Panel pengelola' : 'Masuk pengelola' }}</a>
    </nav>
</header>

<main>
    @yield('isi')
</main>

<footer class="kaki">
    <div class="wrap">
        <div class="kolom">
            <div>
                <a href="{{ route('beranda') }}" class="merek" style="margin-bottom:16px"><x-ilustrasi jenis="logo" /><span><b>SIPANTAU Harga</b><span>Kabupaten Sumbawa Barat</span></span></a>
                <p style="max-width:42ch">Dashboard Tim Pengendalian Inflasi Daerah (TPID) Kabupaten Sumbawa Barat. Harga dikumpulkan enumerator di pasar tradisional dan diverifikasi sebelum ditayangkan.</p>
            </div>
            <div>
                <h4>Data</h4>
                <ul>
                    <li><a href="{{ route('harga') }}">Harga harian</a></li>
                    <li><a href="{{ route('disparitas') }}">Disparitas antarpasar</a></li>
                    <li><a href="{{ route('iph') }}">Indikator Perubahan Harga</a></li>
                    <li><a href="{{ route('inflasi') }}">Inflasi NTB</a></li>
                </ul>
            </div>
            <div>
                <h4>Informasi</h4>
                <ul>
                    <li><a href="{{ route('berita') }}">Berita & kegiatan</a></li>
                    <li><a href="{{ route('panduan') }}">Cara membaca data</a></li>
                    <li><a href="{{ route('login') }}">Masuk pengelola</a></li>
                </ul>
            </div>
            <div>
                <h4>Sumber</h4>
                <ul>
                    <li>Harga: enumerator pasar KSB</li>
                    <li>IPH: BPS dan Kemendagri</li>
                    <li>Inflasi: BRS BPS Provinsi NTB</li>
                </ul>
            </div>
        </div>
    </div>
    <x-ilustrasi jenis="bukit" />
    <div style="background:#d9a637">
        <div class="wrap hak" style="color:#3a2a08">© {{ now()->year }} TPID Kabupaten Sumbawa Barat</div>
    </div>
</footer>
<script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
<script src="{{ asset('js/grafik.js') }}?v=2"></script>
<script>
// Garis bawah navigasi muncul setelah digulir.
addEventListener('scroll', () => document.getElementById('top').classList.toggle('tergulir', scrollY > 8), { passive: true });
</script>
@stack('js')
</body>
</html>
