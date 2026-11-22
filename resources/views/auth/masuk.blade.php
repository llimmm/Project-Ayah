<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Masuk · SIPANTAU Harga KSB</title>
    <link rel="preload" href="{{ asset('fonts/inter-latin-wght.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2">
</head>
<body>
<div class="masuk">
    <div class="kiri">
        <form method="post" action="{{ route('login') }}">
            @csrf
            <a href="{{ route('beranda') }}" class="merek" style="margin-bottom:48px"><x-ilustrasi jenis="logo" /><span><b>SIPANTAU Harga</b><span>Kabupaten Sumbawa Barat</span></span></a>
            <span class="eyebrow">Panel pengelola</span>
            <h1>Masuk</h1>
            <p class="muted" style="margin-bottom:32px">Untuk enumerator, verifikator, dan admin. Akun dibuat oleh admin TPID.</p>
            @if ($errors->any())<div class="catatan salah">{{ $errors->first() }}</div>@endif
            <div class="form-baris">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>
            <div class="form-baris">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>
            <div class="form-baris"><label class="cek"><input type="checkbox" name="ingat" value="1"> Ingat saya di perangkat ini</label></div>
            <button class="btn" style="width:100%">Masuk</button>
            <p class="body-sm" style="margin:24px 0 0"><a href="{{ route('beranda') }}">Kembali ke dashboard publik</a></p>
        </form>
    </div>
    <div class="kanan">
        <div style="max-width:440px;width:100%">
            <div class="ilustrasi" style="background:#244848"><x-ilustrasi jenis="pasar" /></div>
            <p>Setiap harga yang diinput hari ini membantu TPID bergerak lebih cepat.</p>
        </div>
    </div>
</div>
</body>
</html>
