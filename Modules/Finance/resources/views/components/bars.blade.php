{{-- $items: collection of {label, count, total}; $color: '' | green | amber --}}
@php
    $max = max(1, (int) $items->map(fn ($i) => abs($i->total))->max());
@endphp
@forelse ($items as $item)
    <div class="fin-bar-row">
        <span class="fin-bar-label" title="{{ $item->label }}">{{ $item->label }}</span>
        <div class="fin-bar-track"><div class="fin-bar-fill {{ $color ?? '' }}" style="width: {{ max(2, round(abs($item->total) / $max * 100)) }}%"></div></div>
        <span class="fin-bar-value">{{ number_format($item->total) }} <small class="text-muted">({{ $item->count }})</small></span>
    </div>
@empty
    <div class="fin-empty" style="padding: 20px 0">داده ای وجود ندارد</div>
@endforelse
