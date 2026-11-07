@php $isSelected = in_array($service->id, $selectedServices); @endphp
<div class="{{ $col ?? 'col-sm-6 col-lg-4' }}" wire:key="svc-{{ $service->id }}">
    <div class="service-select-item @if ($isSelected) selected @endif @if (!empty($isParentItem)) is-parent @endif"
        role="checkbox" tabindex="0" aria-checked="{{ $isSelected ? 'true' : 'false' }}"
        wire:click="toggleService({{ $service->id }})"
        wire:keydown.space.prevent="toggleService({{ $service->id }})"
        wire:keydown.enter.prevent="toggleService({{ $service->id }})">
        <span class="service-check-box flex-shrink-0">
            <i class="fa fa-check" aria-hidden="true"></i>
        </span>
        <span class="service-select-item__body">
            <span class="service-select-item__title">
                {{ $service->title }}
                @if (!empty($isParentItem))
                    <small class="text-muted">(خود بخش)</small>
                @endif
            </span>
            @if (in_array($service->id, $servicesWithSpecialSetting))
                <span class="service-select-item__flag"
                    title="با ذخیره، تنظیم اختصاصی فعلی این بخش جایگزین می‌شود">
                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i> دارای تنظیم اختصاصی
                </span>
            @endif
        </span>
    </div>
</div>
