@if ($baris->isEmpty())
    <div class="catatan">Tidak ada data harga terverifikasi untuk pilihan ini.</div>
@else
<div class="tabel-wrap">
    <table>
        <thead>
        <tr>
            <th>Komoditas</th>
            <th class="r">Harga</th>
            <th class="r">Sebelumnya</th>
            <th class="r">Ubah harian</th>
            <th class="r">Ubah 7 hari</th>
            <th class="r">Harga acuan</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($baris->groupBy(fn ($b) => $b->komoditas->kategori) as $kat => $isi)
            <tr class="grup"><td colspan="6">{{ $kat }}</td></tr>
            @foreach ($isi as $b)
                <tr>
                    <td><a href="{{ route('komoditas', $b->komoditas) }}">{{ $b->komoditas->nama }}</a><span class="sub">per {{ $b->komoditas->satuan }}</span></td>
                    <td class="r num"><b>{{ rupiah($b->harga) }}</b></td>
                    <td class="r num muted">{{ rupiah($b->harga_kemarin) }}</td>
                    <td class="r"><x-arah :n="$b->ubah_harian" /></td>
                    <td class="r"><x-arah :n="$b->ubah_mingguan" /></td>
                    <td class="r num">
                        @if ($b->komoditas->harga_acuan)
                            {{ rupiah($b->komoditas->harga_acuan) }}
                            @if ($b->di_atas_acuan)<br><span class="lencana naik">Di atas acuan</span>@endif
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>
@endif
