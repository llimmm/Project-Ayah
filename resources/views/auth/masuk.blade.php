<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Masuk · SIPANTAU Harga KSB</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1">
</head>
<body>
<div class="masuk">
    <form class="kartu" method="post" action="{{ route('login') }}">
        @csrf
        <div class="logo" style="margin-bottom:18px"><b>SIPANTAU HARGA</b><span>TPID Kabupaten Sumbawa Barat</span></div>
        <h1 style="font-size:1.3rem">Masuk pengelola</h1>
        <p class="muted kecil">Untuk enumerator, verifikator, dan admin. Akun dibuat oleh admin.</p>
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
        <button class="btn" style="width:100%;justify-content:center">Masuk</button>
        <p class="kecil" style="margin:16px 0 0"><a href="{{ route('beranda') }}">Kembali ke dashboard publik</a></p>
    </form>
</div>
</body>
</html>
