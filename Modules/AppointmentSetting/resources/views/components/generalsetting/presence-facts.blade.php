{{-- Short facts of one setting as soft chips; expects $facts from SpecialSectionSetting::summary() --}}
<div class="presence-facts">
    @if ($facts['active'])
        <span class="is-on"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>فعال</span>
    @else
        <span class="is-off"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>غیرفعال</span>
    @endif
    @if ($facts['duration'])
        <span><i class="fa-regular fa-clock" aria-hidden="true"></i>هر نوبت {{ $facts['duration'] }}</span>
    @endif
    @if ($facts['window'])
        <span><i class="fa-regular fa-calendar" aria-hidden="true"></i>نوبت‌دهی {{ $facts['window'] }}</span>
    @endif
    @if ($facts['visitTypes'])
        <span><i class="fa-solid fa-stethoscope" aria-hidden="true"></i>نوع ویزیت: {{ implode('، ', $facts['visitTypes']) }}</span>
    @endif
    @if ($facts['paymentOn'] && $facts['payments'])
        <span class="is-pay"><i class="fa-solid fa-credit-card" aria-hidden="true"></i>پرداخت آنلاین: {{ implode('، ', $facts['payments']) }}</span>
    @elseif ($facts['paymentOn'])
        <span class="is-pay"><i class="fa-solid fa-credit-card" aria-hidden="true"></i>پرداخت آنلاین فعال</span>
    @else
        <span><i class="fa-solid fa-credit-card" aria-hidden="true"></i>بدون پرداخت آنلاین</span>
    @endif
</div>
