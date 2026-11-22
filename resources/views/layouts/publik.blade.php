<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Beranda') · SIPANTAU Harga Kabupaten Sumbawa Barat</title>
    <meta name="description" content="@yield('deskripsi', 'Dashboard pemantauan harga bahan pokok dan pengendalian inflasi daerah Kabupaten Sumbawa Barat.')">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1">
</head>
<body>
<header class="top">
    <div class="wrap">
        <a href="{{ route('beranda') }}" class="logo"><b>SIPANTAU HARGA</b><span>TPID Kabupaten Sumbawa Barat</span></a>
        <nav class="nav" aria-label="Menu utama">
            @foreach ([
                'beranda' => 'Beranda', 'harga' => 'Harga Harian', 'disparitas' => 'Disparitas',
                'iph' => 'IPH', 'inflasi' => 'Inflasi', 'berita' => 'Berita & Kegiatan', 'panduan' => 'Panduan',
            ] as $r => $l)
                <a href="{{ route($r) }}" @class(['aktif' => request()->routeIs($r, $r . '.*', $r === 'harga' ? 'komoditas' : '-')])>{{ $l }}</a>
            @endforeach
            <a href="{{ auth()->check() ? route('admin.dasbor') : route('login') }}" class="nav-masuk">{{ auth()->check() ? 'Panel pengelola' : 'Masuk' }}</a>
        </nav>
    </div>
</header>

<main>
    <div class="wrap">
        @yield('isi')
    </div>
</main>

<footer>
    <div class="wrap">
        <div>
            <b>Tim Pengendalian Inflasi Daerah (TPID) Kabupaten Sumbawa Barat</b><br>
            Data harga dikumpulkan enumerator di pasar tradisional dan diverifikasi sebelum ditayangkan.
        </div>
        <div>
            IPH: BPS dan Kemendagri · Inflasi: Berita Resmi Statistik BPS<br>
            <a href="{{ route('panduan') }}">Cara membaca data</a>
        </div>
    </div>
</footer>
<script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
<script src="{{ asset('js/grafik.js') }}?v=1"></script>
@stack('js')
</body>
</html>
