<?php

namespace App\Support;

use App\Models\HargaHarian;
use App\Models\Komoditas;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Perhitungan harga untuk dashboard. Hanya data berstatus "disetujui" yang dihitung.
 */
class Harga
{
    /** Ambang perubahan harian (%) yang dianggap "stabil". */
    public const AMBANG_STABIL = 1.0;

    public static function tanggalTerakhir(?int $pasarId = null): ?string
    {
        return HargaHarian::disetujui()
            ->when($pasarId, fn ($q) => $q->where('pasar_id', $pasarId))
            ->max('tanggal');
    }

    /** Tanggal data sebelum $tanggal yang punya data, untuk pembanding "kemarin". */
    public static function tanggalSebelum(string $tanggal, ?int $pasarId = null, int $mundurHari = 0): ?string
    {
        $batas = Carbon::parse($tanggal)->subDays($mundurHari);

        return HargaHarian::disetujui()
            ->when($pasarId, fn ($q) => $q->where('pasar_id', $pasarId))
            ->where('tanggal', $mundurHari ? '<=' : '<', $batas->toDateString())
            ->max('tanggal');
    }

    /** Rata-rata harga per komoditas pada satu tanggal: [komoditas_id => harga]. */
    public static function rataRata(?string $tanggal, ?int $pasarId = null): Collection
    {
        if (! $tanggal) {
            return collect();
        }

        return HargaHarian::disetujui()
            ->whereDate('tanggal', $tanggal)
            ->when($pasarId, fn ($q) => $q->where('pasar_id', $pasarId))
            ->groupBy('komoditas_id')
            ->selectRaw('komoditas_id, AVG(harga) as h')
            ->pluck('h', 'komoditas_id')
            ->map(fn ($v) => (int) round($v));
    }

    /**
     * Tabel ringkasan: harga hari ini vs data sebelumnya vs seminggu lalu.
     */
    public static function ringkasan(?string $tanggal, ?int $pasarId = null): array
    {
        $kemarin = $tanggal ? self::tanggalSebelum($tanggal, $pasarId) : null;
        $mingguLalu = $tanggal ? self::tanggalSebelum($tanggal, $pasarId, 7) : null;

        $now = self::rataRata($tanggal, $pasarId);
        $prev = self::rataRata($kemarin, $pasarId);
        $week = self::rataRata($mingguLalu, $pasarId);

        $baris = Komoditas::aktif()->get()->map(function ($k) use ($now, $prev, $week) {
            $h = $now[$k->id] ?? null;
            $p = $prev[$k->id] ?? null;
            $w = $week[$k->id] ?? null;
            $ubah = self::ubah($h, $p);

            return (object) [
                'komoditas' => $k,
                'harga' => $h,
                'harga_kemarin' => $p,
                'harga_minggu_lalu' => $w,
                'ubah_harian' => $ubah,
                'ubah_mingguan' => self::ubah($h, $w),
                'arah' => self::arah($ubah),
                'di_atas_acuan' => $h && $k->harga_acuan && $h > $k->harga_acuan,
            ];
        })->filter(fn ($r) => $r->harga !== null)->values();

        return [
            'tanggal' => $tanggal,
            'kemarin' => $kemarin,
            'minggu_lalu' => $mingguLalu,
            'baris' => $baris,
            'naik' => $baris->where('arah', 'naik')->count(),
            'turun' => $baris->where('arah', 'turun')->count(),
            'stabil' => $baris->where('arah', 'stabil')->count(),
            'di_atas_acuan' => $baris->where('di_atas_acuan', true)->count(),
        ];
    }

    /** Tren harian satu komoditas: rata-rata seluruh pasar + per pasar. */
    public static function tren(int $komoditasId, string $dari, string $sampai): array
    {
        $data = HargaHarian::disetujui()
            ->with('pasar:id,nama')
            ->where('komoditas_id', $komoditasId)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal')
            ->get(['tanggal', 'pasar_id', 'harga']);

        $label = $data->pluck('tanggal')->map->toDateString()->unique()->values();
        $rata = $data->groupBy(fn ($r) => $r->tanggal->toDateString())->map(fn ($g) => (int) round($g->avg('harga')));

        $perPasar = $data->groupBy('pasar_id')->map(function ($g) use ($label) {
            $byTgl = $g->keyBy(fn ($r) => $r->tanggal->toDateString());

            return [
                'nama' => $g->first()->pasar->nama,
                'data' => $label->map(fn ($t) => $byTgl[$t]->harga ?? null)->all(),
            ];
        })->values();

        return [
            'label' => $label->all(),
            'rata' => $label->map(fn ($t) => $rata[$t])->all(),
            'per_pasar' => $perPasar->all(),
            'min' => $data->min('harga'),
            'max' => $data->max('harga'),
        ];
    }

    /** Disparitas antar pasar pada satu tanggal. KV = simpangan baku / rata-rata x 100. */
    public static function disparitas(?string $tanggal): Collection
    {
        if (! $tanggal) {
            return collect();
        }
        $data = HargaHarian::disetujui()->whereDate('tanggal', $tanggal)->get(['komoditas_id', 'pasar_id', 'harga'])->groupBy('komoditas_id');

        return Komoditas::aktif()->get()->filter(fn ($k) => isset($data[$k->id]))->map(function ($k) use ($data) {
            $h = $data[$k->id]->pluck('harga', 'pasar_id');
            $avg = $h->avg();
            $sd = sqrt($h->map(fn ($v) => ($v - $avg) ** 2)->avg());

            return (object) [
                'komoditas' => $k,
                'per_pasar' => $h,
                'min' => $h->min(),
                'max' => $h->max(),
                'rata' => (int) round($avg),
                'selisih' => $h->max() - $h->min(),
                'kv' => $avg ? round($sd / $avg * 100, 2) : 0,
            ];
        })->values();
    }

    public static function ubah(?int $baru, ?int $lama): ?float
    {
        return $baru && $lama ? round(($baru - $lama) / $lama * 100, 2) : null;
    }

    public static function arah(?float $ubah): string
    {
        if ($ubah === null || abs($ubah) < self::AMBANG_STABIL) {
            return 'stabil';
        }

        return $ubah > 0 ? 'naik' : 'turun';
    }
}
