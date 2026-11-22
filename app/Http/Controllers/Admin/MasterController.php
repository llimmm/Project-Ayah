<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Inflasi;
use App\Models\IphMingguan;
use App\Models\Komoditas;
use App\Models\Pasar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * CRUD sederhana untuk data master dan data rilis (IPH, inflasi, berita).
 * Satu controller, satu definisi per jenis data di $jenis, supaya form dan tabel seragam.
 */
class MasterController extends Controller
{
    public const KHUSUS_ADMIN = ['komoditas', 'pasar', 'pengguna'];

    private function jenis(string $j): array
    {
        $def = [
            'komoditas' => [
                'model' => Komoditas::class, 'judul' => 'Komoditas', 'urut' => ['urutan', 'asc'],
                'kolom' => ['urutan' => 'No', 'nama' => 'Nama', 'kategori' => 'Kategori', 'satuan' => 'Satuan', 'harga_acuan' => 'HET/HAP', 'masuk_iph' => 'IPH', 'aktif' => 'Aktif'],
                'field' => [
                    'nama' => ['Nama komoditas', 'text'],
                    'kategori' => ['Kategori', 'text', 'Contoh: Beras, Cabai, Bawang, Daging & Telur'],
                    'satuan' => ['Satuan', 'text', 'kg, liter, sisir, ekor'],
                    'harga_acuan' => ['Harga acuan HET/HAP (Rp)', 'number', 'Kosongkan bila tidak ada harga acuan resmi'],
                    'urutan' => ['Urutan tampil', 'number'],
                    'masuk_iph' => ['Termasuk komoditas IPH', 'bool'],
                    'aktif' => ['Aktif (tampil di form input dan publik)', 'bool'],
                ],
                'aturan' => fn ($id) => [
                    'nama' => ['required', 'string', 'max:100', Rule::unique('komoditas')->ignore($id)],
                    'kategori' => ['required', 'string', 'max:50'],
                    'satuan' => ['required', 'string', 'max:30'],
                    'harga_acuan' => ['nullable', 'integer', 'min:0'],
                    'urutan' => ['nullable', 'integer', 'min:0'],
                ],
            ],
            'pasar' => [
                'model' => Pasar::class, 'judul' => 'Pasar', 'urut' => ['nama', 'asc'],
                'kolom' => ['nama' => 'Nama', 'kecamatan' => 'Kecamatan', 'alamat' => 'Alamat', 'aktif' => 'Aktif'],
                'field' => [
                    'nama' => ['Nama pasar', 'text'],
                    'kecamatan' => ['Kecamatan', 'select', null, ['Brang Ene', 'Brang Rea', 'Jereweh', 'Maluk', 'Poto Tano', 'Sekongkang', 'Seteluk', 'Taliwang']],
                    'alamat' => ['Alamat', 'text'],
                    'aktif' => ['Aktif dipantau', 'bool'],
                ],
                'aturan' => fn ($id) => [
                    'nama' => ['required', 'string', 'max:100', Rule::unique('pasar')->ignore($id)],
                    'kecamatan' => ['required', 'string', 'max:50'],
                    'alamat' => ['nullable', 'string', 'max:255'],
                ],
            ],
            'pengguna' => [
                'model' => User::class, 'judul' => 'Pengguna', 'urut' => ['role', 'asc'],
                'kolom' => ['name' => 'Nama', 'email' => 'Email', 'role' => 'Peran', 'pasar_id' => 'Pasar', 'aktif' => 'Aktif'],
                'field' => [
                    'name' => ['Nama lengkap', 'text'],
                    'email' => ['Email (untuk login)', 'email'],
                    'role' => ['Peran', 'select', 'Admin: semua menu. Verifikator: verifikasi + rilis. Enumerator: input harga pasar tugasnya.', User::ROLE],
                    'pasar_id' => ['Pasar tugas (khusus enumerator)', 'select', null, fn () => Pasar::aktif()->pluck('nama', 'id')->all()],
                    'password' => ['Kata sandi', 'password', 'Minimal 8 karakter. Kosongkan saat mengedit bila tidak diganti.'],
                    'aktif' => ['Akun aktif', 'bool'],
                ],
                'aturan' => fn ($id) => [
                    'name' => ['required', 'string', 'max:100'],
                    'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($id)],
                    'role' => ['required', Rule::in(array_keys(User::ROLE))],
                    'pasar_id' => ['nullable', 'required_if:role,enumerator', 'exists:pasar,id'],
                    'password' => [$id ? 'nullable' : 'required', Password::min(8)],
                ],
            ],
            'iph' => [
                'model' => IphMingguan::class, 'judul' => 'IPH Mingguan', 'urut' => [['tahun', 'desc'], ['bulan', 'desc'], ['minggu_ke', 'desc']],
                'kolom' => ['tahun' => 'Tahun', 'bulan' => 'Bulan', 'minggu_ke' => 'Minggu', 'perubahan_iph' => 'Perubahan IPH (%)', 'komoditas_andil' => 'Komoditas andil', 'sumber' => 'Sumber'],
                'field' => [
                    'tahun' => ['Tahun', 'number'],
                    'bulan' => ['Bulan', 'select', null, array_combine(range(1, 12), array_map('bulan_nama', range(1, 12)))],
                    'minggu_ke' => ['Minggu ke-', 'select', null, [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5]],
                    'perubahan_iph' => ['Perubahan IPH (%)', 'decimal', 'Pakai titik untuk desimal, minus untuk penurunan. Contoh: -1.25'],
                    'komoditas_andil' => ['Komoditas pemberi andil', 'text', 'Urut dari andil terbesar, pisahkan dengan koma'],
                    'sumber' => ['Sumber', 'text', 'Contoh: Rilis BPS, Rakor Inflasi Kemendagri 12 Nov 2026'],
                ],
                'aturan' => fn ($id) => [
                    'tahun' => ['required', 'integer', 'between:2020,2100'],
                    'bulan' => ['required', 'integer', 'between:1,12'],
                    'minggu_ke' => ['required', 'integer', 'between:1,5', Rule::unique('iph_mingguan')->where(fn ($q) => $q->where('tahun', request('tahun'))->where('bulan', request('bulan')))->ignore($id)],
                    'perubahan_iph' => ['required', 'numeric', 'between:-100,100'],
                    'komoditas_andil' => ['nullable', 'string', 'max:255'],
                    'sumber' => ['required', 'string', 'max:255'],
                ],
                'pesan' => ['minggu_ke.unique' => 'IPH untuk minggu ini sudah ada. Edit data yang ada.'],
            ],
            'inflasi' => [
                'model' => Inflasi::class, 'judul' => 'Inflasi Bulanan', 'urut' => [['tahun', 'desc'], ['bulan', 'desc'], ['wilayah', 'asc']],
                'kolom' => ['wilayah' => 'Wilayah', 'tahun' => 'Tahun', 'bulan' => 'Bulan', 'ihk' => 'IHK', 'mtm' => 'MtM', 'ytd' => 'YtD', 'yoy' => 'YoY'],
                'field' => [
                    'wilayah' => ['Wilayah', 'select', 'Sumbawa Barat bukan kota IHK, jadi dipakai inflasi NTB dan kota IHK terdekat', array_combine(Inflasi::WILAYAH, Inflasi::WILAYAH)],
                    'tahun' => ['Tahun', 'number'],
                    'bulan' => ['Bulan', 'select', null, array_combine(range(1, 12), array_map('bulan_nama', range(1, 12)))],
                    'ihk' => ['IHK', 'decimal'],
                    'mtm' => ['Inflasi month-to-month (%)', 'decimal'],
                    'ytd' => ['Inflasi year-to-date (%)', 'decimal'],
                    'yoy' => ['Inflasi year-on-year (%)', 'decimal'],
                ],
                'aturan' => fn ($id) => [
                    'wilayah' => ['required', Rule::in(Inflasi::WILAYAH)],
                    'tahun' => ['required', 'integer', 'between:2020,2100'],
                    'bulan' => ['required', 'integer', 'between:1,12', Rule::unique('inflasi')->where(fn ($q) => $q->where('tahun', request('tahun'))->where('wilayah', request('wilayah')))->ignore($id)],
                    'ihk' => ['nullable', 'numeric', 'between:0,1000'],
                    'mtm' => ['nullable', 'numeric', 'between:-100,100'],
                    'ytd' => ['nullable', 'numeric', 'between:-100,100'],
                    'yoy' => ['nullable', 'numeric', 'between:-100,100'],
                ],
                'pesan' => ['bulan.unique' => 'Data inflasi wilayah ini untuk bulan tersebut sudah ada.'],
            ],
            'berita' => [
                'model' => Berita::class, 'judul' => 'Berita & Kegiatan', 'urut' => ['created_at', 'desc'],
                'kolom' => ['judul' => 'Judul', 'kategori' => 'Kategori', 'terbit_pada' => 'Terbit'],
                'field' => [
                    'judul' => ['Judul', 'text'],
                    'kategori' => ['Kategori', 'select', null, Berita::KATEGORI],
                    'ringkasan' => ['Ringkasan (tampil di kartu)', 'textarea'],
                    'isi' => ['Isi', 'textarea-lg', 'Pisahkan paragraf dengan baris kosong'],
                    'gambar' => ['Gambar sampul (JPG/PNG/WebP, maks 2 MB)', 'image'],
                    'lampiran' => ['Lampiran (PDF, maks 10 MB)', 'file'],
                    'terbit_pada' => ['Tanggal terbit', 'datetime', 'Kosongkan untuk menyimpan sebagai draft'],
                ],
                'aturan' => fn ($id) => [
                    'judul' => ['required', 'string', 'max:200'],
                    'kategori' => ['required', Rule::in(array_keys(Berita::KATEGORI))],
                    'ringkasan' => ['nullable', 'string', 'max:500'],
                    'isi' => ['nullable', 'string'],
                    'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                    'lampiran' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
                    'terbit_pada' => ['nullable', 'date'],
                ],
            ],
        ];

        abort_unless(isset($def[$j]), 404);
        abort_if(in_array($j, self::KHUSUS_ADMIN, true) && request()->user()?->role !== 'admin', 403, 'Menu ini khusus admin.');

        return $def[$j] + ['kunci' => $j, 'pesan' => $def[$j]['pesan'] ?? []];
    }

    public function index(Request $request, string $j)
    {
        $d = $this->jenis($j);
        $q = $d['model']::query();
        foreach (is_array($d['urut'][0]) ? $d['urut'] : [$d['urut']] as [$kol, $arah]) {
            $q->orderBy($kol, $arah);
        }
        if ($cari = $request->query('cari')) {
            $kolomCari = array_key_first($d['kolom']) === 'urutan' ? 'nama' : array_key_first($d['kolom']);
            $q->where($kolomCari, 'like', "%$cari%");
        }
        if ($j === 'pengguna') {
            $q->with('pasar:id,nama');
        }

        return view('admin.master.index', ['d' => $d, 'data' => $q->paginate(25)->withQueryString()]);
    }

    public function form(string $j, ?int $id = null)
    {
        $d = $this->jenis($j);
        $item = $id ? $d['model']::findOrFail($id) : new $d['model'](['aktif' => true, 'tahun' => now()->year, 'bulan' => now()->month, 'sumber' => 'BPS / Kemendagri']);

        return view('admin.master.form', ['d' => $d, 'item' => $item]);
    }

    public function simpan(Request $request, string $j, ?int $id = null)
    {
        $d = $this->jenis($j);
        $item = $id ? $d['model']::findOrFail($id) : new $d['model'];
        $data = $request->validate(($d['aturan'])($id), $d['pesan']);

        foreach ($d['field'] as $f => $def) {
            if ($def[1] === 'bool') {
                $data[$f] = $request->boolean($f);
            }
            if (in_array($def[1], ['image', 'file'], true)) {
                unset($data[$f]);
                if ($request->hasFile($f)) {
                    $data[$f] = $request->file($f)->store("berita/$f", 'public');
                }
            }
        }
        if ($j === 'pengguna') {
            if (empty($data['password'])) {
                unset($data['password']);
            }
            if ($data['role'] !== 'enumerator') {
                $data['pasar_id'] = null;
            }
            if ($id === $request->user()->id && (! $data['aktif'] || $data['role'] !== 'admin')) {
                return back()->withInput()->withErrors(['role' => 'Anda tidak bisa menurunkan peran atau menonaktifkan akun sendiri.']);
            }
        }
        if ($j === 'komoditas') {
            $data['urutan'] ??= 0;
        }
        if ($j === 'berita') {
            $item->penulis_id ??= $request->user()->id;
            $item->slug ??= Str::slug($data['judul']) . '-' . Str::lower(Str::random(5));
        }

        $item->fill($data)->save();

        return redirect()->route('admin.master', $j)->with('ok', $d['judul'] . ' tersimpan.');
    }

    public function hapus(Request $request, string $j, int $id)
    {
        $d = $this->jenis($j);
        abort_if($j === 'pengguna' && $id === $request->user()->id, 422, 'Tidak bisa menghapus akun sendiri.');
        $item = $d['model']::findOrFail($id);

        if (in_array($j, ['komoditas', 'pasar'], true) && \App\Models\HargaHarian::where($j === 'pasar' ? 'pasar_id' : 'komoditas_id', $id)->exists()) {
            $item->update(['aktif' => false]);

            return back()->with('ok', "{$d['judul']} sudah punya riwayat harga, jadi dinonaktifkan (tidak dihapus) supaya data lama tetap utuh.");
        }

        $item->delete();

        return back()->with('ok', $d['judul'] . ' dihapus.');
    }
}
