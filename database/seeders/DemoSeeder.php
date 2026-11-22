<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\HargaHarian;
use App\Models\Inflasi;
use App\Models\IphMingguan;
use App\Models\Komoditas;
use App\Models\Pasar;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * DATA FIKTIF untuk demo/presentasi. Jangan dijalankan di server produksi.
 * php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    private array $dasar = [
        'Beras Medium' => 13200, 'Beras Premium' => 15000, 'Cabai Merah Keriting' => 42000,
        'Cabai Merah Besar' => 45000, 'Cabai Rawit Merah' => 55000, 'Bawang Merah' => 36000,
        'Bawang Putih Honan' => 40000, 'Daging Ayam Ras' => 38000, 'Telur Ayam Ras' => 28500,
        'Daging Sapi Murni' => 135000, 'Minyak Goreng Curah' => 17500, 'Minyakita' => 16500,
        'Gula Pasir Curah' => 18000, 'Tepung Terigu' => 12000, 'Kedelai Impor' => 11500,
        'Jagung Pipilan Kering' => 7000, 'Ikan Tongkol' => 35000, 'Ikan Kembung' => 40000,
        'Tomat' => 12000, 'Kentang' => 18000, 'Pisang' => 20000, 'Jeruk' => 25000,
    ];

    public function run(): void
    {
        mt_srand(2026);
        $admin = User::where('role', 'admin')->first();
        $verif = User::where('role', 'verifikator')->first();
        $enum = User::where('role', 'enumerator')->pluck('id', 'pasar_id');
        $rows = [];
        // Hari data terakhir dibiarkan "diajukan" supaya antrean verifikasi punya contoh.
        $hariAkhir = today()->isSunday() ? today()->subDay() : today();

        foreach (Komoditas::all() as $k) {
            $bergejolak = $k->kategori === 'Cabai' || in_array($k->nama, ['Bawang Merah', 'Tomat'], true);
            $vol = $bergejolak ? 0.04 : 0.008;
            foreach (Pasar::all() as $pi => $p) {
                $h = ($this->dasar[$k->nama] ?? 20000) * (1 + ($pi - 2) * 0.02);
                for ($d = 90; $d >= 0; $d--) {
                    $tgl = today()->subDays($d);
                    if ($tgl->isSunday()) {
                        continue;
                    }
                    $h *= 1 + (mt_rand(-1000, 1050) / 1000) * $vol;
                    $ajuan = $tgl->equalTo($hariAkhir);
                    $rows[] = [
                        'tanggal' => $tgl->toDateString(),
                        'pasar_id' => $p->id,
                        'komoditas_id' => $k->id,
                        'harga' => (int) (round($h / 500) * 500),
                        'status' => $ajuan ? 'diajukan' : 'disetujui',
                        'diinput_oleh' => $enum[$p->id] ?? $admin->id,
                        'diverifikasi_oleh' => $ajuan ? null : $verif->id,
                        'diverifikasi_pada' => $ajuan ? null : now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        foreach (array_chunk($rows, 1000) as $c) {
            HargaHarian::upsert($c, ['tanggal', 'pasar_id', 'komoditas_id'], ['harga', 'status']);
        }

        // IPH mingguan, 16 minggu terakhir
        $andil = ['Cabai Rawit Merah', 'Bawang Merah', 'Daging Ayam Ras', 'Beras Medium', 'Telur Ayam Ras', 'Cabai Merah Keriting'];
        for ($w = 16; $w >= 1; $w--) {
            $d = today()->startOfWeek()->subWeeks($w);
            IphMingguan::updateOrCreate(
                ['tahun' => $d->year, 'bulan' => $d->month, 'minggu_ke' => min(5, (int) ceil($d->day / 7))],
                [
                    'perubahan_iph' => round(mt_rand(-250, 300) / 100, 2),
                    'komoditas_andil' => implode(', ', collect($andil)->shuffle()->take(3)->all()),
                ]
            );
        }

        // Inflasi, 18 bulan terakhir
        foreach (Inflasi::WILAYAH as $wi => $wil) {
            $ihk = 104 + $wi;
            $ytd = 0;
            for ($m = 18; $m >= 1; $m--) {
                $b = today()->startOfMonth()->subMonths($m);
                $mtm = round(mt_rand(-40, 70) / 100, 2);
                $ihk = round($ihk * (1 + $mtm / 100), 2);
                $ytd = $b->month === 1 ? $mtm : round($ytd + $mtm, 2);
                Inflasi::updateOrCreate(
                    ['wilayah' => $wil, 'tahun' => $b->year, 'bulan' => $b->month],
                    ['ihk' => $ihk, 'mtm' => $mtm, 'ytd' => $ytd, 'yoy' => round(mt_rand(80, 320) / 100, 2)]
                );
            }
        }

        $berita = [
            ['Gerakan Pangan Murah serentak di delapan kecamatan', 'kegiatan'],
            ['Rapat koordinasi TPID KSB bahas stok beras jelang akhir tahun', 'berita'],
            ['Operasi pasar cabai rawit di Pasar Taliwang', 'kegiatan'],
            ['Roadmap Pengendalian Inflasi Daerah KSB 2025-2027', 'dokumen'],
        ];
        foreach ($berita as $i => [$judul, $kat]) {
            Berita::updateOrCreate(['slug' => Str::slug($judul)], [
                'judul' => "[DEMO] $judul",
                'kategori' => $kat,
                'penulis_id' => $admin->id,
                'ringkasan' => 'Contoh isi untuk demo. Ganti dengan rilis resmi TPID Kabupaten Sumbawa Barat.',
                'isi' => "Contoh isi untuk demo.\n\nGanti dengan rilis resmi TPID Kabupaten Sumbawa Barat.",
                'terbit_pada' => now()->subDays($i * 5),
            ]);
        }
    }
}
