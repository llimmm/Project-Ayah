<?php

if (! function_exists('rupiah')) {
    function rupiah($n): string
    {
        return $n === null ? '-' : 'Rp' . number_format((float) $n, 0, ',', '.');
    }
}

if (! function_exists('persen')) {
    function persen($n, int $d = 2, bool $tanda = true): string
    {
        if ($n === null) {
            return '-';
        }
        $s = number_format(abs((float) $n), $d, ',', '.');
        if (! $tanda) {
            return $s . '%';
        }
        return ($n > 0 ? '+' : ($n < 0 ? '−' : '')) . $s . '%';
    }
}

if (! function_exists('bulan_nama')) {
    function bulan_nama(int $b): string
    {
        return ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$b] ?? '';
    }
}

if (! function_exists('bulan_singkat')) {
    function bulan_singkat(int $b): string
    {
        return ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][$b] ?? '';
    }
}

if (! function_exists('tgl')) {
    function tgl($d, bool $hari = false): string
    {
        if (! $d) {
            return '-';
        }
        $d = \Illuminate\Support\Carbon::parse($d);
        $h = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$d->dayOfWeek];
        return ($hari ? $h . ', ' : '') . $d->day . ' ' . bulan_nama($d->month) . ' ' . $d->year;
    }
}

if (! function_exists('angka')) {
    function angka($n, int $d = 2): string
    {
        return $n === null ? '-' : number_format((float) $n, $d, ',', '.');
    }
}
