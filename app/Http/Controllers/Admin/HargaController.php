<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaHarian;
use App\Models\Komoditas;
use App\Models\Pasar;
use App\Support\Harga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Dasbor admin, input harga harian (enumerator), dan verifikasi (verifikator). */
class HargaController extends Controller
{
    /** Perubahan dari harga sebelumnya yang memicu tanda peringatan (%). */
    public const AMBANG_CEK = 20;

    public function dasbor(Request $request)
    {
        $user = $request->user();
        $hariIni = today()->toDateString();
        $jumlahKomoditas = Komoditas::aktif()->count();

        $progres = Pasar::aktif()->get()->map(function ($p) use ($hariIni) {
            $s = HargaHarian::where('pasar_id', $p->id)->whereDate('tanggal', $hariIni)
                ->groupBy('status')->selectRaw('status, COUNT(*) n')->pluck('n', 'status');

            return (object) ['pasar' => $p, 'status' => $s, 'total' => $s->sum()];
        });

        $antrean = HargaHarian::where('status', 'diajukan')
            ->groupBy('tanggal', 'pasar_id')->selectRaw('tanggal, pasar_id, COUNT(*) n')
            ->with('pasar:id,nama')->orderByDesc('tanggal')->limit(10)->get();

        $ditolak = HargaHarian::where('status', 'ditolak')
            ->when($user->role === 'enumerator', fn ($q) => $q->where('pasar_id', $user->pasar_id))
            ->groupBy('tanggal', 'pasar_id')->selectRaw('tanggal, pasar_id, COUNT(*) n')
            ->with('pasar:id,nama')->orderByDesc('tanggal')->limit(10)->get();

        return view('admin.dasbor', compact('progres', 'antrean', 'ditolak', 'jumlahKomoditas', 'hariIni'));
    }

    public function form(Request $request)
    {
        $user = $request->user();
        $tanggal = $request->date('tanggal')?->toDateString() ?? today()->toDateString();
        abort_if($tanggal > today()->toDateString(), 422, 'Tanggal tidak boleh melebihi hari ini.');

        $pasarId = $user->role === 'enumerator' ? $user->pasar_id : ($request->integer('pasar') ?: Pasar::aktif()->value('id'));
        abort_unless($pasarId, 403, 'Akun enumerator ini belum ditugaskan ke pasar. Hubungi admin.');

        $isian = HargaHarian::where('pasar_id', $pasarId)->whereDate('tanggal', $tanggal)->get()->keyBy('komoditas_id');
        $sebelum = Harga::tanggalSebelum($tanggal, $pasarId);

        return view('admin.input', [
            'tanggal' => $tanggal,
            'pasar' => Pasar::find($pasarId),
            'daftarPasar' => $user->role === 'enumerator' ? collect() : Pasar::aktif()->get(),
            'komoditas' => Komoditas::aktif()->get(),
            'isian' => $isian,
            'sebelum' => $sebelum,
            'hargaSebelum' => Harga::rataRata($sebelum, $pasarId),
            'ambang' => self::AMBANG_CEK,
        ]);
    }

    public function simpan(Request $request)
    {
        $user = $request->user();
        // "13.500" atau "Rp 13.500" diterima sebagai 13500.
        $request->merge(['harga' => collect($request->input('harga', []))
            ->map(fn ($v) => $v === null ? null : (preg_replace('/\D/', '', (string) $v) ?: null))->all()]);

        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'pasar_id' => ['required', 'exists:pasar,id'],
            'harga' => ['array'],
            'harga.*' => ['nullable', 'integer', 'min:100', 'max:10000000'],
            'aksi' => ['required', 'in:draft,ajukan'],
        ], [
            'harga.*.integer' => 'Harga harus angka bulat tanpa titik/koma.',
            'harga.*.min' => 'Harga minimal Rp100.',
        ]);

        if ($user->role === 'enumerator' && (int) $data['pasar_id'] !== (int) $user->pasar_id) {
            abort(403, 'Enumerator hanya boleh menginput pasar tugasnya.');
        }

        $status = $data['aksi'] === 'ajukan' ? 'diajukan' : 'draft';
        $terkunci = 0;

        DB::transaction(function () use ($data, $user, $status, &$terkunci) {
            $aktif = Komoditas::aktif()->pluck('id')->all();
            foreach ($data['harga'] ?? [] as $kid => $harga) {
                if (! in_array((int) $kid, $aktif, true)) {
                    continue;
                }
                $baris = HargaHarian::firstOrNew([
                    'tanggal' => $data['tanggal'], 'pasar_id' => $data['pasar_id'], 'komoditas_id' => $kid,
                ]);
                // Data yang sudah disetujui hanya boleh diubah admin.
                if ($baris->exists && $baris->status === 'disetujui' && $user->role !== 'admin') {
                    $terkunci++;
                    continue;
                }
                if ($harga === null || $harga === '') {
                    if ($baris->exists && $baris->status !== 'disetujui') {
                        $baris->delete();
                    }
                    continue;
                }
                $baris->fill([
                    'harga' => $harga,
                    'status' => $baris->status === 'disetujui' ? 'disetujui' : $status,
                    'diinput_oleh' => $user->id,
                    'catatan' => $baris->status === 'ditolak' ? $baris->catatan : null,
                ])->save();
            }
        });

        $pesan = $status === 'diajukan' ? 'Data dikirim ke verifikator.' : 'Draft tersimpan.';
        if ($terkunci) {
            $pesan .= " $terkunci harga yang sudah disetujui tidak diubah.";
        }

        return redirect()->route('admin.input', ['tanggal' => $data['tanggal'], 'pasar' => $data['pasar_id']])->with('ok', $pesan);
    }

    public function antrean()
    {
        $grup = HargaHarian::whereIn('status', ['diajukan'])
            ->groupBy('tanggal', 'pasar_id')->selectRaw('tanggal, pasar_id, COUNT(*) n, MAX(updated_at) terakhir')
            ->with('pasar:id,nama')->orderByDesc('tanggal')->paginate(20);

        return view('admin.antrean', compact('grup'));
    }

    public function periksa(string $tanggal, Pasar $pasar)
    {
        $baris = HargaHarian::with('komoditas', 'penginput:id,name')->where('pasar_id', $pasar->id)->whereDate('tanggal', $tanggal)
            ->get()->sortBy(fn ($b) => $b->komoditas->urutan);
        $sebelum = Harga::tanggalSebelum($tanggal, $pasar->id);
        $hargaSebelum = Harga::rataRata($sebelum, $pasar->id);
        $pembanding = Harga::rataRata($tanggal); // rata-rata pasar lain yang sudah disetujui

        return view('admin.periksa', compact('tanggal', 'pasar', 'baris', 'sebelum', 'hargaSebelum', 'pembanding') + ['ambang' => self::AMBANG_CEK]);
    }

    public function putuskan(Request $request, string $tanggal, Pasar $pasar)
    {
        $data = $request->validate([
            'id' => ['required', 'array', 'min:1'],
            'id.*' => ['integer'],
            'keputusan' => ['required', 'in:setujui,tolak'],
            'catatan' => ['nullable', 'required_if:keputusan,tolak', 'string', 'max:255'],
        ], ['catatan.required_if' => 'Tulis alasan penolakan supaya enumerator tahu apa yang harus diperbaiki.', 'id.required' => 'Pilih minimal satu baris.']);

        $n = HargaHarian::whereIn('id', $data['id'])->where('pasar_id', $pasar->id)->whereDate('tanggal', $tanggal)
            ->where('status', 'diajukan')
            ->update([
                'status' => $data['keputusan'] === 'setujui' ? 'disetujui' : 'ditolak',
                'catatan' => $data['keputusan'] === 'tolak' ? $data['catatan'] : null,
                'diverifikasi_oleh' => $request->user()->id,
                'diverifikasi_pada' => now(),
            ]);

        $sisa = HargaHarian::where('pasar_id', $pasar->id)->whereDate('tanggal', $tanggal)->where('status', 'diajukan')->exists();
        $pesan = "$n harga " . ($data['keputusan'] === 'setujui' ? 'disetujui dan tampil di publik.' : 'ditolak dan dikembalikan ke enumerator.');

        return $sisa
            ? back()->with('ok', $pesan)
            : redirect()->route('admin.antrean')->with('ok', $pesan);
    }
}
