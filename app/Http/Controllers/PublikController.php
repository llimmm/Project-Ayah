<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Inflasi;
use App\Models\IphMingguan;
use App\Models\Komoditas;
use App\Models\Pasar;
use App\Support\Csv;
use App\Support\Harga;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublikController extends Controller
{
    public function beranda()
    {
        $tanggal = Harga::tanggalTerakhir();
        $ringkasan = Harga::ringkasan($tanggal);
        $iph = IphMingguan::orderByDesc('tahun')->orderByDesc('bulan')->orderByDesc('minggu_ke')->limit(12)->get()->reverse()->values();
        $inflasi = Inflasi::where('wilayah', 'Provinsi NTB')->orderByDesc('tahun')->orderByDesc('bulan')->first();

        $sorotan = $ringkasan['baris']->filter(fn ($r) => $r->ubah_harian !== null)->sortByDesc(fn ($r) => abs($r->ubah_harian))->take(4);

        $dari = $tanggal ? Carbon::parse($tanggal)->subDays(30)->toDateString() : null;
        $spark = $sorotan->mapWithKeys(fn ($r) => [$r->komoditas->id => Harga::tren($r->komoditas->id, $dari, $tanggal)['rata']]);

        return view('publik.beranda', [
            'r' => $ringkasan,
            'spark' => $spark,
            'iph' => $iph,
            'inflasi' => $inflasi,
            'sorotan' => $sorotan,
            'berita' => Berita::terbit()->limit(3)->get(),
            'jumlahPasar' => Pasar::aktif()->count(),
        ]);
    }

    public function harga(Request $request)
    {
        $pasarId = $request->integer('pasar') ?: null;
        $tanggal = $request->date('tanggal')?->toDateString();
        // Kalau tanggal yang dipilih belum ada data, pakai tanggal data terakhir sebelum itu.
        $tanggal = $tanggal
            ? (Harga::tanggalSebelum(Carbon::parse($tanggal)->addDay()->toDateString(), $pasarId) ?? $tanggal)
            : Harga::tanggalTerakhir($pasarId);

        $r = Harga::ringkasan($tanggal, $pasarId);

        if ($request->query('unduh') === 'csv') {
            return Csv::unduh("harga-ksb-$tanggal.csv", ['Komoditas', 'Satuan', 'Harga', 'Harga Sebelumnya', 'Ubah Harian (%)', 'Ubah Mingguan (%)', 'Harga Acuan'],
                $r['baris']->map(fn ($b) => [$b->komoditas->nama, $b->komoditas->satuan, $b->harga, $b->harga_kemarin, $b->ubah_harian, $b->ubah_mingguan, $b->komoditas->harga_acuan]));
        }

        return view('publik.harga', [
            'r' => $r,
            'pasar' => Pasar::aktif()->get(),
            'pasarId' => $pasarId,
            'kategori' => $r['baris']->groupBy(fn ($b) => $b->komoditas->kategori),
        ]);
    }

    public function komoditas(Request $request, Komoditas $komoditas)
    {
        abort_unless($komoditas->aktif, 404);
        $hari = in_array($request->integer('hari'), [30, 90, 180, 365]) ? $request->integer('hari') : 30;
        $sampai = Harga::tanggalTerakhir() ?? today()->toDateString();
        $dari = Carbon::parse($sampai)->subDays($hari)->toDateString();

        return view('publik.komoditas', [
            'k' => $komoditas,
            'tren' => Harga::tren($komoditas->id, $dari, $sampai),
            'hari' => $hari,
            'sampai' => $sampai,
            'semua' => Komoditas::aktif()->get(['id', 'nama']),
        ]);
    }

    public function disparitas(Request $request)
    {
        $tanggal = $request->date('tanggal')?->toDateString() ?? Harga::tanggalTerakhir();
        $data = Harga::disparitas($tanggal);
        $pasar = Pasar::aktif()->get();

        if ($request->query('unduh') === 'csv') {
            return Csv::unduh("disparitas-ksb-$tanggal.csv", array_merge(['Komoditas'], $pasar->pluck('nama')->all(), ['Terendah', 'Tertinggi', 'KV (%)']),
                $data->map(fn ($d) => array_merge([$d->komoditas->nama], $pasar->map(fn ($p) => $d->per_pasar[$p->id] ?? '')->all(), [$d->min, $d->max, $d->kv])));
        }

        return view('publik.disparitas', compact('tanggal', 'data', 'pasar'));
    }

    public function iph(Request $request)
    {
        $tahun = $request->integer('tahun') ?: null;
        $data = IphMingguan::when($tahun, fn ($q) => $q->where('tahun', $tahun))
            ->orderBy('tahun')->orderBy('bulan')->orderBy('minggu_ke')->get();

        if ($request->query('unduh') === 'csv') {
            return Csv::unduh('iph-ksb.csv', ['Tahun', 'Bulan', 'Minggu ke', 'Perubahan IPH (%)', 'Komoditas Andil', 'Sumber'],
                $data->map(fn ($d) => [$d->tahun, $d->bulan, $d->minggu_ke, $d->perubahan_iph, $d->komoditas_andil, $d->sumber]));
        }

        $andil = $data->flatMap(fn ($d) => array_filter(array_map('trim', explode(',', (string) $d->komoditas_andil))))
            ->countBy()->sortDesc()->take(8);

        return view('publik.iph', [
            'data' => $data,
            'tahun' => $tahun,
            'daftarTahun' => IphMingguan::distinct()->orderByDesc('tahun')->pluck('tahun'),
            'andil' => $andil,
        ]);
    }

    public function inflasi(Request $request)
    {
        $wilayah = in_array($request->query('wilayah'), Inflasi::WILAYAH, true) ? $request->query('wilayah') : 'Provinsi NTB';
        $data = Inflasi::where('wilayah', $wilayah)->orderBy('tahun')->orderBy('bulan')->get();

        if ($request->query('unduh') === 'csv') {
            return Csv::unduh('inflasi-' . str($wilayah)->slug() . '.csv', ['Wilayah', 'Tahun', 'Bulan', 'IHK', 'MtM (%)', 'YtD (%)', 'YoY (%)'],
                $data->map(fn ($d) => [$d->wilayah, $d->tahun, $d->bulan, $d->ihk, $d->mtm, $d->ytd, $d->yoy]));
        }

        $terakhir = $data->last();
        $semuaWilayah = $terakhir
            ? Inflasi::where('tahun', $terakhir->tahun)->where('bulan', $terakhir->bulan)->get()->keyBy('wilayah')
            : collect();

        return view('publik.inflasi', compact('data', 'wilayah', 'terakhir', 'semuaWilayah'));
    }

    public function berita(Request $request)
    {
        $kategori = array_key_exists($request->query('kategori'), Berita::KATEGORI) ? $request->query('kategori') : null;

        return view('publik.berita', [
            'berita' => Berita::terbit()->when($kategori, fn ($q) => $q->where('kategori', $kategori))->paginate(9)->withQueryString(),
            'kategori' => $kategori,
        ]);
    }

    public function bacaBerita(string $slug)
    {
        $b = Berita::terbit()->where('slug', $slug)->firstOrFail();

        return view('publik.baca', ['b' => $b, 'lain' => Berita::terbit()->whereKeyNot($b->id)->limit(3)->get()]);
    }

    public function panduan()
    {
        return view('publik.panduan');
    }
}
