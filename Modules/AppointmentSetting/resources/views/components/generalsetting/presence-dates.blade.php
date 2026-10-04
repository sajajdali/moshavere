{{-- Upcoming one-off dates with their own hours; expects $dates from SpecialSectionSetting::specialDates() --}}
@if ($dates)
    <div class="presence-dates">
        <strong><i class="fa-solid fa-calendar-day me-1" aria-hidden="true"></i>روزهای با ساعت ویژه:</strong>
        @foreach ($dates as $date)
            <span>{{ $date['date'] }} ({{ implode('، ', $date['ranges']) }})</span>
        @endforeach
    </div>
@endif
