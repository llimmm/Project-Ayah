@if ($p->hasPages())
<nav class="paginasi" aria-label="Halaman">
    @if (! $p->onFirstPage())<a href="{{ $p->previousPageUrl() }}">Sebelumnya</a>@endif
    @foreach ($p->getUrlRange(max(1, $p->currentPage() - 2), min($p->lastPage(), $p->currentPage() + 2)) as $n => $url)
        @if ($n === $p->currentPage())<span class="aktif">{{ $n }}</span>@else<a href="{{ $url }}">{{ $n }}</a>@endif
    @endforeach
    @if ($p->hasMorePages())<a href="{{ $p->nextPageUrl() }}">Berikutnya</a>@endif
    <span class="muted" style="border:0;background:none">{{ $p->total() }} data</span>
</nav>
@endif
