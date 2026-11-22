@extends('layouts.admin')
@section('judul', 'Periksa isian')

@section('isi')
<div class="kepala-panel">
    <div>
        <p class="caption"><a href="{{ route('admin.antrean') }}">Antrean verifikasi</a></p>
        <h1>{{ $pasar->nama }}</h1>
        <p>{{ tgl($tanggal, true) }} · pembanding: data pasar ini {{ $sebelum ? tgl($sebelum) : '(belum ada)' }} dan rata-rata pasar lain yang sudah disetujui hari ini.</p>
    </div>
</div>

<div class="catatan">
    Baris berwarna kuning berubah lebih dari {{ $ambang }}% dari data sebelumnya atau dari rata-rata pasar lain. Cek dulu ke enumerator sebelum disetujui.
    Centang baris yang ingin diputuskan; secara bawaan semua baris yang diajukan sudah tercentang.
</div>

<form method="post">
    @csrf
    <div class="tabel">
        <table>
            <thead>
            <tr>
                <th><input type="checkbox" id="semua" checked aria-label="Pilih semua"></th>
                <th>Komoditas</th><th class="r">Harga diajukan</th><th class="r">Data sebelumnya</th><th class="r">Perubahan</th>
                <th class="r">Rata-rata pasar lain</th><th>Status</th><th>Diinput oleh</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($baris as $b)
                @php($lama = $hargaSebelum[$b->komoditas_id] ?? null)
                @php($ubah = \App\Support\Harga::ubah($b->harga, $lama))
                @php($banding = $pembanding[$b->komoditas_id] ?? null)
                @php($selisih = \App\Support\Harga::ubah($b->harga, $banding))
                @php($janggal = ($ubah !== null && abs($ubah) > $ambang) || ($selisih !== null && abs($selisih) > $ambang))
                <tr @if ($janggal) style="background:var(--waspada-soft)" @endif>
                    <td>@if ($b->status === 'diajukan')<input type="checkbox" name="id[]" value="{{ $b->id }}" checked class="pilih">@endif</td>
                    <td>{{ $b->komoditas->nama }}<span class="sub">per {{ $b->komoditas->satuan }}</span></td>
                    <td class="r num"><b>{{ rupiah($b->harga) }}</b></td>
                    <td class="r num muted">{{ rupiah($lama) }}</td>
                    <td class="r"><x-arah :n="$ubah" /></td>
                    <td class="r num">{{ rupiah($banding) }}@if ($selisih !== null)<span class="sub">{{ persen($selisih) }}</span>@endif</td>
                    <td><span class="lencana {{ $b->status }}">{{ ucfirst($b->status) }}</span>@if ($janggal) <span class="lencana waspada">Cek</span>@endif</td>
                    <td class="caption muted">{{ $b->penginput?->name ?? '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="aksi-bawah">
        <button class="btn" name="keputusan" value="setujui">Setujui yang dicentang</button>
        <input name="catatan" placeholder="Alasan penolakan (wajib bila menolak)" style="flex:1;min-width:240px" value="{{ old('catatan') }}" maxlength="255">
        <button class="btn bahaya" name="keputusan" value="tolak">Tolak yang dicentang</button>
    </div>
</form>
@endsection

@push('js')
<script>
document.getElementById('semua').addEventListener('change', (e) => document.querySelectorAll('.pilih').forEach((c) => c.checked = e.target.checked));
</script>
@endpush
