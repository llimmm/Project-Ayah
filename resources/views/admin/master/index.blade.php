@extends('layouts.admin')
@section('judul', $d['judul'])

@section('isi')
<div class="kepala">
    <div>
        <h1>{{ $d['judul'] }}</h1>
        @if ($d['kunci'] === 'iph')<p>Salin angka IPH resmi dari rilis BPS atau bahan Rakor Pengendalian Inflasi Kemendagri.</p>@endif
        @if ($d['kunci'] === 'inflasi')<p>Salin dari Berita Resmi Statistik BPS Provinsi NTB setiap awal bulan.</p>@endif
    </div>
    <a class="btn" href="{{ route('admin.master.baru', $d['kunci']) }}">Tambah {{ strtolower($d['judul']) }}</a>
</div>

@if (in_array($d['kunci'], ['komoditas', 'pasar', 'pengguna', 'berita']))
<form class="filter" method="get">
    <label>Cari <input name="cari" value="{{ request('cari') }}" placeholder="Ketik nama atau judul"></label>
    <button class="btn garis">Cari</button>
</form>
@endif

<div class="tabel-wrap">
    <table>
        <thead><tr>@foreach ($d['kolom'] as $l)<th>{{ $l }}</th>@endforeach<th></th></tr></thead>
        <tbody>
        @forelse ($data as $item)
            <tr>
                @foreach ($d['kolom'] as $f => $l)
                    @php($v = $item->$f)
                    <td @class(['num' => is_numeric($v)])>
                        @switch(true)
                            @case(is_bool($v) || in_array($f, ['aktif', 'masuk_iph']))
                                <span class="lencana {{ $v ? 'disetujui' : 'abu' }}">{{ $v ? 'Ya' : 'Tidak' }}</span>
                                @break
                            @case($f === 'harga_acuan')
                                {{ rupiah($v) }}
                                @break
                            @case($f === 'bulan')
                                {{ bulan_nama($v) }}
                                @break
                            @case(in_array($f, ['perubahan_iph', 'mtm', 'ytd', 'yoy']))
                                {{ persen($v) }}
                                @break
                            @case($f === 'role')
                                {{ \App\Models\User::ROLE[$v] ?? $v }}
                                @break
                            @case($f === 'pasar_id')
                                {{ $item->pasar?->nama ?? '-' }}
                                @break
                            @case($f === 'kategori' && $d['kunci'] === 'berita')
                                {{ \App\Models\Berita::KATEGORI[$v] ?? $v }}
                                @break
                            @case($f === 'terbit_pada')
                                @if ($v){{ tgl($v) }}@if ($v->isFuture()) <span class="lencana diajukan">Terjadwal</span>@endif @else<span class="lencana draft">Draft</span>@endif
                                @break
                            @default
                                {{ \Illuminate\Support\Str::limit((string) $v, 60) ?: '-' }}
                        @endswitch
                    </td>
                @endforeach
                <td class="r" style="white-space:nowrap">
                    <a class="btn garis kecil" href="{{ route('admin.master.ubah', [$d['kunci'], $item->id]) }}">Ubah</a>
                    <form method="post" action="{{ route('admin.master.hapus', [$d['kunci'], $item->id]) }}" style="display:inline" class="form-hapus">
                        @csrf @method('delete')
                        <button class="btn bahaya kecil">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($d['kolom']) + 1 }}" class="muted">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('admin._paginasi', ['p' => $data])
@endsection

@push('js')
<script>
// Konfirmasi hapus dua langkah tanpa dialog browser: klik pertama mengubah tombol, klik kedua menghapus.
document.querySelectorAll('.form-hapus').forEach((f) => f.addEventListener('submit', (e) => {
  const b = f.querySelector('button');
  if (!f.dataset.yakin) { e.preventDefault(); f.dataset.yakin = 1; b.textContent = 'Yakin hapus?'; setTimeout(() => { delete f.dataset.yakin; b.textContent = 'Hapus'; }, 4000); }
}));
</script>
@endpush
