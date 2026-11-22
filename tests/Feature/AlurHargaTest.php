<?php

namespace Tests\Feature;

use App\Models\HargaHarian;
use App\Models\Komoditas;
use App\Models\Pasar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurHargaTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_halaman_publik_terbuka_tanpa_data(): void
    {
        foreach (['/', '/harga', '/disparitas', '/iph', '/inflasi', '/berita', '/panduan', '/masuk'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_halaman_publik_dan_panel_dengan_data_demo(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);
        foreach (['/', '/harga', '/harga?pasar=1', '/harga/1?hari=90', '/disparitas', '/iph', '/inflasi?wilayah=Kota+Bima', '/berita', '/harga?unduh=csv', '/iph?unduh=csv', '/inflasi?unduh=csv', '/disparitas?unduh=csv'] as $url) {
            $this->get($url)->assertOk();
        }
        $admin = $this->user('admin@inflasi.ksb');
        foreach (['/admin', '/admin/input', '/admin/verifikasi', '/admin/data/iph', '/admin/data/inflasi', '/admin/data/berita', '/admin/data/komoditas', '/admin/data/pasar', '/admin/data/pengguna', '/admin/data/komoditas/baru', '/admin/data/pengguna/1', '/admin/data/berita/baru'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_login_dan_salah_sandi(): void
    {
        $this->post('/masuk', ['email' => 'admin@inflasi.ksb', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post('/masuk', ['email' => 'admin@inflasi.ksb', 'password' => 'ksb-ganti-segera'])->assertRedirect('/admin');
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $this->user('admin@inflasi.ksb')->update(['aktif' => false]);
        $this->post('/masuk', ['email' => 'admin@inflasi.ksb', 'password' => 'ksb-ganti-segera'])->assertSessionHasErrors('email');
    }

    public function test_alur_input_ajukan_setujui_lalu_tampil_publik(): void
    {
        $enum = $this->user('enum.taliwang@inflasi.ksb');
        $beras = Komoditas::where('nama', 'Beras Medium')->first();
        $tgl = today()->toDateString();

        // Belum disetujui: publik tidak menampilkan harga.
        $this->actingAs($enum)->post('/admin/input', [
            'tanggal' => $tgl, 'pasar_id' => $enum->pasar_id, 'aksi' => 'ajukan',
            'harga' => [$beras->id => 'Rp 13.750'],
        ])->assertRedirect();
        $this->assertDatabaseHas('harga_harian', ['komoditas_id' => $beras->id, 'harga' => 13750, 'status' => 'diajukan']);
        auth()->logout();
        $this->get('/harga')->assertDontSee('Rp13.750');

        $verif = $this->user('verifikator@inflasi.ksb');
        $id = HargaHarian::first()->id;
        $this->actingAs($verif)->get("/admin/verifikasi/$tgl/{$enum->pasar_id}")->assertOk()->assertSee('Beras Medium');
        $this->actingAs($verif)->post("/admin/verifikasi/$tgl/{$enum->pasar_id}", ['id' => [$id], 'keputusan' => 'setujui'])->assertRedirect();
        $this->assertDatabaseHas('harga_harian', ['id' => $id, 'status' => 'disetujui', 'diverifikasi_oleh' => $verif->id]);

        auth()->logout();
        $this->get('/harga')->assertSee('Rp13.750');

        // Sudah disetujui: enumerator tidak bisa mengubah lagi.
        $this->actingAs($enum)->post('/admin/input', [
            'tanggal' => $tgl, 'pasar_id' => $enum->pasar_id, 'aksi' => 'ajukan', 'harga' => [$beras->id => 99999],
        ]);
        $this->assertDatabaseHas('harga_harian', ['id' => $id, 'harga' => 13750]);
    }

    public function test_tolak_wajib_alasan_dan_kembali_ke_enumerator(): void
    {
        $enum = $this->user('enum.taliwang@inflasi.ksb');
        $verif = $this->user('verifikator@inflasi.ksb');
        $kid = Komoditas::first()->id;
        $tgl = today()->toDateString();
        $this->actingAs($enum)->post('/admin/input', ['tanggal' => $tgl, 'pasar_id' => $enum->pasar_id, 'aksi' => 'ajukan', 'harga' => [$kid => 5000]]);
        $id = HargaHarian::first()->id;

        $this->actingAs($verif)->post("/admin/verifikasi/$tgl/{$enum->pasar_id}", ['id' => [$id], 'keputusan' => 'tolak'])->assertSessionHasErrors('catatan');
        $this->actingAs($verif)->post("/admin/verifikasi/$tgl/{$enum->pasar_id}", ['id' => [$id], 'keputusan' => 'tolak', 'catatan' => 'Salah ketik, cek ulang'])->assertRedirect();
        $this->assertDatabaseHas('harga_harian', ['id' => $id, 'status' => 'ditolak']);
        $this->actingAs($enum)->get('/admin')->assertSee('ditolak verifikator');
        $this->actingAs($enum)->get('/admin/input')->assertSee('Salah ketik, cek ulang');
    }

    public function test_enumerator_hanya_boleh_pasar_tugasnya(): void
    {
        $enum = $this->user('enum.taliwang@inflasi.ksb');
        $lain = Pasar::where('id', '!=', $enum->pasar_id)->first();
        $this->actingAs($enum)->post('/admin/input', [
            'tanggal' => today()->toDateString(), 'pasar_id' => $lain->id, 'aksi' => 'draft', 'harga' => [Komoditas::first()->id => 5000],
        ])->assertForbidden();
        $this->assertDatabaseCount('harga_harian', 0);
    }

    public function test_tanggal_masa_depan_ditolak(): void
    {
        $enum = $this->user('enum.taliwang@inflasi.ksb');
        $this->actingAs($enum)->post('/admin/input', [
            'tanggal' => today()->addDay()->toDateString(), 'pasar_id' => $enum->pasar_id, 'aksi' => 'draft', 'harga' => [1 => 5000],
        ])->assertSessionHasErrors('tanggal');
    }

    public function test_batas_akses_per_peran(): void
    {
        $enum = $this->user('enum.taliwang@inflasi.ksb');
        $verif = $this->user('verifikator@inflasi.ksb');

        $this->get('/admin')->assertRedirect('/masuk');
        foreach (['/admin/verifikasi', '/admin/data/iph', '/admin/data/pengguna'] as $url) {
            $this->actingAs($enum)->get($url)->assertForbidden();
        }
        $this->actingAs($verif)->get('/admin/data/iph')->assertOk();
        foreach (['/admin/data/pengguna', '/admin/data/komoditas', '/admin/data/pasar/baru'] as $url) {
            $this->actingAs($verif)->get($url)->assertForbidden();
        }
        $this->actingAs($verif)->post('/admin/data/pengguna', ['name' => 'X', 'email' => 'x@x.id', 'role' => 'admin', 'password' => 'rahasia123'])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'x@x.id']);
    }

    public function test_crud_iph_dan_duplikat_minggu_ditolak(): void
    {
        $verif = $this->user('verifikator@inflasi.ksb');
        $data = ['tahun' => 2026, 'bulan' => 11, 'minggu_ke' => 2, 'perubahan_iph' => '-1.25', 'komoditas_andil' => 'Cabai Rawit Merah, Bawang Merah', 'sumber' => 'Rakor Kemendagri'];
        $this->actingAs($verif)->post('/admin/data/iph', $data)->assertRedirect('/admin/data/iph');
        $this->actingAs($verif)->post('/admin/data/iph', $data)->assertSessionHasErrors('minggu_ke');
        $this->assertDatabaseCount('iph_mingguan', 1);
        $this->get('/iph')->assertSee('Cabai Rawit Merah');
    }

    public function test_admin_tidak_bisa_menonaktifkan_diri_sendiri(): void
    {
        $admin = $this->user('admin@inflasi.ksb');
        $this->actingAs($admin)->put("/admin/data/pengguna/{$admin->id}", ['name' => 'A', 'email' => $admin->email, 'role' => 'admin', 'aktif' => 0])->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->aktif);
    }

    public function test_hapus_komoditas_berriwayat_hanya_dinonaktifkan(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $admin = $this->user('admin@inflasi.ksb');
        $k = Komoditas::first();
        $this->actingAs($admin)->delete("/admin/data/komoditas/{$k->id}")->assertRedirect();
        $this->assertFalse($k->fresh()->aktif);
        $this->assertDatabaseHas('harga_harian', ['komoditas_id' => $k->id]);
    }
}
