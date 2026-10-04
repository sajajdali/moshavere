{{-- Saturday-to-Friday hours of one setting; expects $days from SpecialSectionSetting::weekSchedule() --}}
<div class="presence-week">
    @foreach ($days as $day)
        <div class="presence-day {{ $day['ranges'] ? '' : 'is-closed' }} {{ $day['today'] ? 'is-today' : '' }}">
            <span class="presence-day-name">
                {{ $day['name'] }}
                @if ($day['today'])
                    <small class="presence-today">امروز</small>
                @endif
            </span>
            @forelse ($day['ranges'] as $range)
                <span class="presence-range">{{ $range }}</span>
            @empty
                <span class="presence-range">تعطیل</span>
            @endforelse
        </div>
    @endforeach
</div>
