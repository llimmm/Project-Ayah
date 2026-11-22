<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use App\Models\Pasar;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data master awal. Nama pasar dan harga acuan (HET/HAP) adalah CONTOH:
 * wajib dicek ulang dengan Dinas Perdagangan KSB sebelum dipakai resmi.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $pasar = [
            ['Pasar Taliwang', 'Taliwang'],
            ['Pasar Seteluk', 'Seteluk'],
            ['Pasar Maluk', 'Maluk'],
            ['Pasar Jereweh', 'Jereweh'],
            ['Pasar Brang Rea', 'Brang Rea'],
        ];
        foreach ($pasar as [$nama, $kec]) {
            Pasar::firstOrCreate(['nama' => $nama], ['kecamatan' => $kec]);
        }

        // [nama, kategori, satuan, harga acuan, masuk IPH]
        $komoditas = [
            ['Beras Medium', 'Beras', 'kg', 13500, true],
            ['Beras Premium', 'Beras', 'kg', 14900, true],
            ['Cabai Merah Keriting', 'Cabai', 'kg', null, true],
            ['Cabai Merah Besar', 'Cabai', 'kg', null, true],
            ['Cabai Rawit Merah', 'Cabai', 'kg', null, true],
            ['Bawang Merah', 'Bawang', 'kg', null, true],
            ['Bawang Putih Honan', 'Bawang', 'kg', null, true],
            ['Daging Ayam Ras', 'Daging & Telur', 'kg', null, true],
            ['Telur Ayam Ras', 'Daging & Telur', 'kg', null, true],
            ['Daging Sapi Murni', 'Daging & Telur', 'kg', null, true],
            ['Minyak Goreng Curah', 'Minyak & Gula', 'liter', null, true],
            ['Minyakita', 'Minyak & Gula', 'liter', 15700, true],
            ['Gula Pasir Curah', 'Minyak & Gula', 'kg', 17500, true],
            ['Tepung Terigu', 'Bahan Pokok Lain', 'kg', null, true],
            ['Kedelai Impor', 'Bahan Pokok Lain', 'kg', null, false],
            ['Jagung Pipilan Kering', 'Bahan Pokok Lain', 'kg', null, false],
            ['Ikan Tongkol', 'Ikan', 'kg', null, false],
            ['Ikan Kembung', 'Ikan', 'kg', null, false],
            ['Tomat', 'Sayur & Buah', 'kg', null, false],
            ['Kentang', 'Sayur & Buah', 'kg', null, false],
            ['Pisang', 'Sayur & Buah', 'sisir', null, true],
            ['Jeruk', 'Sayur & Buah', 'kg', null, true],
        ];
        foreach ($komoditas as $i => [$nama, $kat, $sat, $acuan, $iph]) {
            Komoditas::firstOrCreate(['nama' => $nama], [
                'kategori' => $kat, 'satuan' => $sat, 'harga_acuan' => $acuan,
                'masuk_iph' => $iph, 'urutan' => $i + 1,
            ]);
        }

        $pw = env('SEED_PASSWORD', 'ksb-ganti-segera');
        User::firstOrCreate(['email' => 'admin@inflasi.ksb'], ['name' => 'Admin TPID', 'role' => 'admin', 'password' => $pw]);
        User::firstOrCreate(['email' => 'verifikator@inflasi.ksb'], ['name' => 'Verifikator Disperindag', 'role' => 'verifikator', 'password' => $pw]);
        foreach (Pasar::orderBy('id')->get() as $p) {
            $slug = strtolower(str_replace(['Pasar ', ' '], ['', ''], $p->nama));
            User::firstOrCreate(['email' => "enum.$slug@inflasi.ksb"], [
                'name' => "Enumerator {$p->nama}", 'role' => 'enumerator', 'pasar_id' => $p->id, 'password' => $pw,
            ]);
        }
    }
}
