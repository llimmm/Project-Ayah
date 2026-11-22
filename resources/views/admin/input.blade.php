@extends('layouts.admin')
@section('judul', 'Input harga harian')

@section('isi')
@php($u = auth()->user())
<div class="kepala-panel">
    <div>
        <h1>Input harga harian</h1>
        <p>{{ $pasar->nama }} · {{ tgl($tanggal, true) }}
            @if ($sebelum) · pembanding: data {{ tgl($sebelum) }} @endif</p>
    </div>
</div>

<form class="filter" method="get">
    <label>Tanggal <input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ today()->toDateString() }}" onchange="this.form.submit()"></label>
    @if ($daftarPasar->isNotEmpty())
        <label>Pasar
            <select name="pasar" onchange="this.form.submit()">
                @foreach ($daftarPasar as $p)<option value="{{ $p->id }}" @selected($p->id === $pasar->id)>{{ $p->nama }}</option>@endforeach
            </select>
        </label>
    @endif
    <noscript><button class="btn kedua">Buka</button></noscript>
</form>

<div class="catatan">
    Tulis harga eceran dalam rupiah tanpa titik (contoh: <b>13500</b>). Kosongkan bila komoditas tidak dijual hari itu.
    Kolom yang berubah lebih dari {{ $ambang }}% dari data sebelumnya akan diberi warna kuning, supaya dicek ulang sebelum diajukan.
    Data yang sudah <b>disetujui</b> terkunci{{ $u->role === 'admin' ? ', kecuali untuk admin' : '' }}.
</div>

<form method="post" action="{{ route('admin.input') }}" id="form-harga">
    @csrf
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
    <input type="hidden" name="pasar_id" value="{{ $pasar->id }}">
    <div class="tabel">
        <table>
            <thead><tr><th>Komoditas</th><th class="r">Data sebelumnya</th><th class="r">Harga hari ini (Rp)</th><th class="r">Perubahan</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($komoditas->groupBy('kategori') as $kat => $isi)
                <tr class="grup"><td colspan="5">{{ $kat }}</td></tr>
                @foreach ($isi as $k)
                    @php($b = $isian[$k->id] ?? null)
                    @php($kunci = $b && $b->status === 'disetujui' && $u->role !== 'admin')
                    <tr>
                        <td><label for="h{{ $k->id }}">{{ $k->nama }}</label><span class="sub">per {{ $k->satuan }}@if ($k->harga_acuan) · acuan {{ rupiah($k->harga_acuan) }}@endif</span></td>
                        <td class="r num muted">{{ rupiah($hargaSebelum[$k->id] ?? null) }}</td>
                        <td class="r">
                            <input class="harga" id="h{{ $k->id }}" name="harga[{{ $k->id }}]" inputmode="numeric" autocomplete="off"
                                   value="{{ old('harga.' . $k->id, $b?->harga) }}" data-lama="{{ $hargaSebelum[$k->id] ?? '' }}" @disabled($kunci)>
                        </td>
                        <td class="r"><span class="arah stabil" data-ubah="{{ $k->id }}">-</span></td>
                        <td>
                            @if ($b)
                                <span class="lencana {{ $b->status }}">{{ ucfirst($b->status) }}</span>
                                @if ($b->status === 'ditolak' && $b->catatan)<span class="sub" style="color:var(--naik)">{{ $b->catatan }}</span>@endif
                            @else
                                <span class="lencana abu">Belum diisi</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="aksi-bawah">
        <button class="btn kedua" name="aksi" value="draft">Simpan draft</button>
        <button class="btn" name="aksi" value="ajukan">Ajukan ke verifikator</button>
        <span class="caption muted" id="ringkas"></span>
    </div>
</form>
@endsection

@push('js')
<script>
(() => {
  const ambang = {{ $ambang }};
  const fmt = (n) => (n > 0 ? '+' : '') + n.toLocaleString('id-ID', { maximumFractionDigits: 1 }) + '%';
  const inputs = [...document.querySelectorAll('input.harga')];
  function cek(el) {
    const n = parseInt(el.value.replace(/\D/g, ''), 10), lama = parseInt(el.dataset.lama, 10);
    const out = document.querySelector('[data-ubah="' + el.id.slice(1) + '"]');
    el.classList.remove('janggal');
    if (!n || !lama) { out.textContent = '-'; out.className = 'arah stabil'; return; }
    const u = (n - lama) / lama * 100;
    out.textContent = fmt(u);
    out.className = 'arah ' + (Math.abs(u) < 1 ? 'stabil' : u > 0 ? 'naik' : 'turun');
    if (Math.abs(u) > ambang) el.classList.add('janggal');
  }
  function ringkas() {
    const isi = inputs.filter((i) => i.value.trim()).length, janggal = inputs.filter((i) => i.classList.contains('janggal')).length;
    document.getElementById('ringkas').textContent = isi + ' dari ' + inputs.length + ' terisi' + (janggal ? ' · ' + janggal + ' perlu dicek ulang' : '');
  }
  inputs.forEach((el) => { cek(el); el.addEventListener('input', () => { cek(el); ringkas(); }); });
  ringkas();
  // Enter pindah ke kolom berikutnya, bukan mengirim form.
  inputs.forEach((el, i) => el.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); (inputs[i + 1] || el).focus(); } }));
  document.getElementById('form-harga').addEventListener('submit', (e) => {
    const janggal = inputs.filter((i) => i.classList.contains('janggal')).length;
    if (e.submitter?.value === 'ajukan' && janggal && !document.getElementById('form-harga').dataset.yakin) {
      e.preventDefault();
      document.getElementById('ringkas').innerHTML = '<b style="color:var(--naik)">' + janggal + ' harga berubah lebih dari ' + ambang + '%. Periksa kolom kuning, lalu klik "Ajukan" sekali lagi bila sudah benar.</b>';
      document.getElementById('form-harga').dataset.yakin = '1';
    }
  });
})();
</script>
@endpush
