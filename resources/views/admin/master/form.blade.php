@extends('layouts.admin')
@section('judul', ($item->exists ? 'Ubah ' : 'Tambah ') . $d['judul'])

@section('isi')
<div class="kepala">
    <div>
        <p class="kecil"><a href="{{ route('admin.master', $d['kunci']) }}">{{ $d['judul'] }}</a></p>
        <h1>{{ $item->exists ? 'Ubah' : 'Tambah' }} {{ strtolower($d['judul']) }}</h1>
    </div>
</div>

<form method="post" enctype="multipart/form-data" class="kartu" style="max-width:640px"
      action="{{ $item->exists ? route('admin.master.ubah', [$d['kunci'], $item->id]) : route('admin.master.simpan', $d['kunci']) }}">
    @csrf
    @if ($item->exists) @method('put') @endif

    @foreach ($d['field'] as $f => $def)
        @php([$label, $tipe] = $def)
        @php($bantu = $def[2] ?? null)
        @php($opsi = isset($def[3]) ? ($def[3] instanceof Closure ? ($def[3])() : $def[3]) : [])
        @php($nilai = old($f, $tipe === 'password' ? '' : $item->$f))
        <div class="form-baris">
            @if ($tipe === 'bool')
                <input type="hidden" name="{{ $f }}" value="0">
                <label class="cek"><input type="checkbox" name="{{ $f }}" value="1" @checked($nilai)> {{ $label }}</label>
            @else
                <label for="f-{{ $f }}">{{ $label }}</label>
                @switch($tipe)
                    @case('select')
                        <select id="f-{{ $f }}" name="{{ $f }}">
                            <option value="">Pilih</option>
                            @foreach ($opsi as $k => $l)
                                @php($k = array_is_list($opsi) ? $l : $k)
                                <option value="{{ $k }}" @selected((string) $nilai === (string) $k)>{{ $l }}</option>
                            @endforeach
                        </select>
                        @break
                    @case('textarea')
                        <textarea id="f-{{ $f }}" name="{{ $f }}">{{ $nilai }}</textarea>
                        @break
                    @case('textarea-lg')
                        <textarea id="f-{{ $f }}" name="{{ $f }}" style="min-height:260px">{{ $nilai }}</textarea>
                        @break
                    @case('image')
                    @case('file')
                        @if ($item->$f)<span class="kecil">Berkas saat ini: <a href="{{ asset('storage/' . $item->$f) }}" target="_blank">{{ basename($item->$f) }}</a>. Unggah baru untuk mengganti.</span>@endif
                        <input id="f-{{ $f }}" type="file" name="{{ $f }}" accept="{{ $tipe === 'image' ? 'image/jpeg,image/png,image/webp' : 'application/pdf' }}">
                        @break
                    @case('datetime')
                        <input id="f-{{ $f }}" type="datetime-local" name="{{ $f }}" value="{{ $nilai ? \Illuminate\Support\Carbon::parse($nilai)->format('Y-m-d\TH:i') : '' }}">
                        @break
                    @case('decimal')
                        <input id="f-{{ $f }}" type="number" step="0.01" name="{{ $f }}" value="{{ $nilai }}">
                        @break
                    @default
                        <input id="f-{{ $f }}" type="{{ $tipe }}" name="{{ $f }}" value="{{ $nilai }}" @if ($tipe === 'password') autocomplete="new-password" @endif>
                @endswitch
            @endif
            @if ($bantu)<span class="bantu">{{ $bantu }}</span>@endif
            @error($f)<span class="galat">{{ $message }}</span>@enderror
        </div>
    @endforeach

    <div style="display:flex;gap:10px;margin-top:8px">
        <button class="btn">Simpan</button>
        <a class="btn garis" href="{{ route('admin.master', $d['kunci']) }}">Batal</a>
    </div>
</form>
@endsection
