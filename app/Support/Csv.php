<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class Csv
{
    /** Unduh CSV yang langsung terbaca rapi di Excel (BOM UTF-8, pemisah titik koma). */
    public static function unduh(string $nama, array $header, iterable $baris): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $baris) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, $header, ';');
            foreach ($baris as $b) {
                fputcsv($f, array_map(fn ($v) => is_float($v) ? str_replace('.', ',', (string) $v) : $v, (array) $b), ';');
            }
            fclose($f);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
