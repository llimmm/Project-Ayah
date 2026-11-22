@php($u = auth()->user())
@php($antrean = in_array($u->role, ['admin', 'verifikator']) ? \App\Models\HargaHarian::where('status', 'diajukan')->select('tanggal', 'pasar_id')->distinct()->get()->count() : 0)
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#faf5e8">
    <title>@yield('judul', 'Panel') · Pengelola SIPANTAU Harga KSB</title>
    <link rel="preload" href="{{ asset('fonts/inter-latin-wght.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2">
</head>
<body>
<div class="panel">
    <aside class="samping">
        <a href="{{ route('admin.dasbor') }}" class="merek"><x-ilustrasi jenis="logo" /><span><b>SIPANTAU Harga</b><span>Panel pengelola</span></span></a>
        <a href="{{ route('admin.dasbor') }}" @class(['item', 'aktif' => request()->routeIs('admin.dasbor')])>Dasbor</a>
        <a href="{{ route('admin.input') }}" @class(['item', 'aktif' => request()->routeIs('admin.input')])>Input harga harian</a>
        @if ($u->punya('admin', 'verifikator'))
            <a href="{{ route('admin.antrean') }}" @class(['item', 'aktif' => request()->routeIs('admin.antrean', 'admin.periksa')])>Verifikasi @if ($antrean)<span class="hitung">{{ $antrean }}</span>@endif</a>
            <span class="eyebrow">Rilis data</span>
            @foreach (['iph' => 'IPH mingguan', 'inflasi' => 'Inflasi bulanan', 'berita' => 'Berita & kegiatan'] as $j => $l)
                <a href="{{ route('admin.master', $j) }}" @class(['item', 'aktif' => request()->route('j') === $j])>{{ $l }}</a>
            @endforeach
        @endif
        @if ($u->punya('admin'))
            <span class="eyebrow">Data master</span>
            @foreach (['komoditas' => 'Komoditas', 'pasar' => 'Pasar', 'pengguna' => 'Pengguna'] as $j => $l)
                <a href="{{ route('admin.master', $j) }}" @class(['item', 'aktif' => request()->route('j') === $j])>{{ $l }}</a>
            @endforeach
        @endif
        <div class="bawah">
            <a href="{{ route('beranda') }}" target="_blank" class="item">Lihat situs publik ↗</a>
        </div>
    </aside>
    <div class="isi-panel">
        <div class="bar-atas">
            <span>{{ $u->name }} · {{ \App\Models\User::ROLE[$u->role] ?? $u->role }}@if ($u->pasar) · {{ $u->pasar->nama }}@endif</span>
            <form method="post" action="{{ route('keluar') }}">@csrf<button class="btn kedua kecil">Keluar</button></form>
        </div>
        @if (session('ok'))<div class="catatan ok">{{ session('ok') }}</div>@endif
        @if ($errors->any())
            <div class="catatan salah"><b>Periksa lagi isian berikut:</b>
                <ul>@foreach (collect($errors->all())->unique()->take(8) as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('isi')
    </div>
</div>
@stack('js')
</body>
</html>
