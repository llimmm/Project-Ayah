{{-- Kepala halaman dalam: eyebrow, judul besar, lead, dan slot kanan opsional. --}}
<section class="kepala-hal">
    <div class="wrap">
        <div>
            @isset($jejak)<div class="jejak">{!! $jejak !!}</div>@endisset
            <span class="eyebrow">{{ $eyebrow }}</span>
            <h1>{{ $judul }}</h1>
            @isset($lead)<p class="lead">{{ $lead }}</p>@endisset
        </div>
        @isset($kanan)<div>{!! $kanan !!}</div>@endisset
    </div>
</section>
