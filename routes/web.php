<?php

use App\Http\Controllers\Admin\HargaController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublikController;
use Illuminate\Support\Facades\Route;

// ---- Publik ----
Route::controller(PublikController::class)->group(function () {
    Route::get('/', 'beranda')->name('beranda');
    Route::get('/harga', 'harga')->name('harga');
    Route::get('/harga/{komoditas}', 'komoditas')->name('komoditas');
    Route::get('/disparitas', 'disparitas')->name('disparitas');
    Route::get('/iph', 'iph')->name('iph');
    Route::get('/inflasi', 'inflasi')->name('inflasi');
    Route::get('/berita', 'berita')->name('berita');
    Route::get('/berita/{slug}', 'bacaBerita')->name('berita.baca');
    Route::get('/panduan', 'panduan')->name('panduan');
});

// ---- Login ----
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'form'])->name('login');
    Route::post('/masuk', [AuthController::class, 'masuk']);
});
Route::post('/keluar', [AuthController::class, 'keluar'])->middleware('auth')->name('keluar');

// ---- Panel pengelola ----
Route::prefix('admin')->name('admin.')->middleware(['auth', 'peran:admin,verifikator,enumerator'])->group(function () {
    Route::get('/', [HargaController::class, 'dasbor'])->name('dasbor');
    Route::get('/input', [HargaController::class, 'form'])->name('input');
    Route::post('/input', [HargaController::class, 'simpan']);

    Route::middleware('peran:admin,verifikator')->group(function () {
        Route::get('/verifikasi', [HargaController::class, 'antrean'])->name('antrean');
        Route::get('/verifikasi/{tanggal}/{pasar}', [HargaController::class, 'periksa'])->name('periksa')->where('tanggal', '\d{4}-\d{2}-\d{2}');
        Route::post('/verifikasi/{tanggal}/{pasar}', [HargaController::class, 'putuskan'])->where('tanggal', '\d{4}-\d{2}-\d{2}');

        // IPH, inflasi, berita: admin + verifikator. Komoditas, pasar, pengguna: admin saja (dicek di MasterController).
        Route::controller(MasterController::class)->prefix('data/{j}')->where(['j' => 'iph|inflasi|berita|komoditas|pasar|pengguna'])->group(function () {
            Route::get('/', 'index')->name('master');
            Route::get('/baru', 'form')->name('master.baru');
            Route::post('/', 'simpan')->name('master.simpan');
            Route::get('/{id}', 'form')->name('master.ubah')->whereNumber('id');
            Route::put('/{id}', 'simpan')->whereNumber('id');
            Route::delete('/{id}', 'hapus')->name('master.hapus')->whereNumber('id');
        });
    });
});
