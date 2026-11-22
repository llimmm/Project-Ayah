@php($u = auth()->user())
@php($antrean = in_array($u->role, ['admin', 'verifikator']) ? \App\Models\HargaHarian::where('status', 'diajukan')->select('tanggal', 'pasar_id')->distinct()->get()->count() : 0)
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('judul', 'Panel') · Pengelola SIPANTAU Harga KSB</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1">
</head>
<body>
<div class="panel">
    <aside class="samping">
        <a href="{{ route('admin.dasbor') }}" class="logo"><b>SIPANTAU HARGA</b><span>Panel pengelola</span></a>
        <a href="{{ route('admin.dasbor') }}" @class(['aktif' => request()->routeIs('admin.dasbor')])>Dasbor</a>
        <a href="{{ route('admin.input') }}" @class(['aktif' => request()->routeIs('admin.input')])>Input harga harian</a>
        @if ($u->punya('admin', 'verifikator'))
            <a href="{{ route('admin.antrean') }}" @class(['aktif' => request()->routeIs('admin.antrean', 'admin.periksa')])>Verifikasi @if ($antrean)<span class="hitung">{{ $antrean }}</span>@endif</a>
            <div class="judul-menu">Rilis data</div>
            @foreach (['iph' => 'IPH mingguan', 'inflasi' => 'Inflasi bulanan', 'berita' => 'Berita & kegiatan'] as $j => $l)
                <a href="{{ route('admin.master', $j) }}" @class(['aktif' => request()->route('j') === $j])>{{ $l }}</a>
            @endforeach
        @endif
        @if ($u->punya('admin'))
            <div class="judul-menu">Data master</div>
            @foreach (['komoditas' => 'Komoditas', 'pasar' => 'Pasar', 'pengguna' => 'Pengguna'] as $j => $l)
                <a href="{{ route('admin.master', $j) }}" @class(['aktif' => request()->route('j') === $j])>{{ $l }}</a>
            @endforeach
        @endif
        <div class="judul-menu">Situs</div>
        <a href="{{ route('beranda') }}" target="_blank">Lihat situs publik</a>
    </aside>
    <div class="isi-panel">
        <div class="bar-atas">
            <span>{{ $u->name }} · {{ \App\Models\User::ROLE[$u->role] ?? $u->role }}@if ($u->pasar) · {{ $u->pasar->nama }}@endif</span>
            <form method="post" action="{{ route('keluar') }}">@csrf<button class="btn garis kecil">Keluar</button></form>
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
