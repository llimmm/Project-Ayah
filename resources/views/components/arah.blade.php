@props(['n' => null, 'label' => true])
@php($a = \App\Support\Harga::arah($n))
<span class="arah {{ $a }}" title="{{ ['naik' => 'Naik', 'turun' => 'Turun', 'stabil' => 'Stabil'][$a] }}">
    @if ($a === 'naik')
        <svg viewBox="0 0 12 12" aria-hidden="true"><path d="M6 2l4.5 6h-9z" fill="currentColor"/></svg>
    @elseif ($a === 'turun')
        <svg viewBox="0 0 12 12" aria-hidden="true"><path d="M6 10L1.5 4h9z" fill="currentColor"/></svg>
    @else
        <svg viewBox="0 0 12 12" aria-hidden="true"><rect x="2" y="5" width="8" height="2" rx="1" fill="currentColor"/></svg>
    @endif
    @if ($n === null)
        <span>-</span>
    @else
        <span>{{ persen($n) }}</span>
    @endif
    <span class="sr">{{ $a }}</span>
</span>
