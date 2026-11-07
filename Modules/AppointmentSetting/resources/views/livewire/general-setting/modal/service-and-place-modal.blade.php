<div class="modal fade" id="ServiceAndPlaceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    wire:ignore.self aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header flex-wrap gap-2">
                <h5 class="modal-title" id="staticBackdropLabel">
                    @if ($step == 1)
                        <span>انتخاب مطب</span>
                    @elseif($step == 2)
                        <span>انتخاب بخش ها</span>
                        <small class="text-muted d-block mt-1" style="font-size: .8rem">
                            می‌توانید چند بخش را انتخاب کنید تا تنظیمات یکسان برای همه‌ی آن‌ها ثبت شود.
                        </small>
                    @endif
                </h5>
                <form class="modal-title" wire:submit.prevent="searchDocAndSection">
                    <div class="input-group">
                        <input type="text" class="form-control" wire:model='search'
                            placeholder="{{ $step == 1 ? 'عنوان مطب' : 'نام بخش' }}">
                        <button class="btn ripple btn-info text-fixed-white input-group-text border-0" type="submit">
                            جست و جو
                        </button>
                        @if (filled($search))
                            <button class="btn btn-secondary" type="button" wire:click='ignoreSearch'>نمایش همه</button>
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-body">
                {{-- wrapper must not carry display utilities (e.g. d-flex !important), otherwise wire:loading can't hide it --}}
                <div wire:loading.block wire:target="showRelatedSection,backToPlaces,searchDocAndSection,ignoreSearch">
                    <div class="text-center">
                        <span class="spinner-border text-primary my-5" role="status"></span>
                    </div>
                </div>
                @if ($step == 1)
                    <div class="row" wire:loading.remove wire:target="showRelatedSection,searchDocAndSection,ignoreSearch">
                        @foreach ($ServiceOrPlace as $place)
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <a href="#" wire:click.prevent="showRelatedSection({{ $place->id }})">
                                            <i class="fa fa-hospital-o fa-2x text-primary me-3" aria-hidden="true"></i>
                                            <span style="font-size: medium">{{ $place->title }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($step == 2)
                    @error('selectedServices')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror
                    <div wire:loading.remove wire:target="backToPlaces,searchDocAndSection,ignoreSearch">
                        @if ($grouped['standalone']->isEmpty() && empty($grouped['groups']))
                            <div class="alert alert-info">بخشی یافت نشد</div>
                        @endif

                        {{-- بخش های مستقل (بدون والد و بدون زیربخش) --}}
                        @if ($grouped['standalone']->isNotEmpty())
                            <section class="service-group">
                                <header class="service-group__header">
                                    <span class="service-group__title">
                                        <i class="fa fa-th-large text-primary" aria-hidden="true"></i>
                                        بخش ها
                                    </span>
                                </header>
                                <div class="row g-2">
                                    @foreach ($grouped['standalone'] as $service)
                                        @include('appointmentsetting::livewire.general-setting.modal.partials.service-item', ['service' => $service])
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        {{-- بخش های دارای زیربخش: هر والد یک گروه --}}
                        @foreach ($grouped['groups'] as $group)
                            @php
                                $groupIds = $group['children']->pluck('id')->all();
                                if ($group['parent']) {
                                    array_unshift($groupIds, $group['parent']->id);
                                }
                                $selectedInGroup = count(array_intersect($groupIds, $selectedServices));
                                $allInGroup = $selectedInGroup === count($groupIds);
                            @endphp
                            <section class="service-group service-group--nested @if ($selectedInGroup) has-selection @endif"
                                wire:key="svc-group-{{ $group['id'] }}">
                                <header class="service-group__header">
                                    <span class="service-group__title">
                                        <i class="fa fa-sitemap text-primary" aria-hidden="true"></i>
                                        {{ $group['title'] }}
                                        <span class="service-group__count">
                                            {{ $group['children']->count() }} زیربخش
                                            @if ($selectedInGroup)
                                                · <strong class="text-primary">{{ $selectedInGroup }} انتخاب شده</strong>
                                            @endif
                                        </span>
                                    </span>
                                    <button type="button" class="btn btn-sm {{ $allInGroup ? 'btn-outline-secondary' : 'btn-outline-primary' }}"
                                        wire:click="toggleGroup(@js($groupIds))">
                                        {{ $allInGroup ? 'حذف انتخاب همه' : 'انتخاب همه' }}
                                    </button>
                                </header>
                                <div class="row g-2">
                                    @if ($group['parent'])
                                        @include('appointmentsetting::livewire.general-setting.modal.partials.service-item', [
                                            'service' => $group['parent'],
                                            'isParentItem' => true,
                                        ])
                                    @endif
                                    @foreach ($group['children'] as $service)
                                        @include('appointmentsetting::livewire.general-setting.modal.partials.service-item', ['service' => $service])
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer d-flex flex-wrap justify-content-between gap-2">
                <div class="d-flex flex-wrap align-items-center gap-1">
                    @if ($step == 2 && !empty($selectedTitles))
                        <span class="text-muted me-1">انتخاب شده:</span>
                        @foreach ($selectedServices as $selId)
                            @if (isset($selectedTitles[$selId]))
                                <span class="badge bg-primary p-2">
                                    {{ $selectedTitles[$selId] }}
                                    <a href="#" class="text-white ms-1" wire:click.prevent="toggleService({{ $selId }})"
                                        aria-label="حذف">&times;</a>
                                </span>
                            @endif
                        @endforeach
                        <a href="#" class="ms-2 small" wire:click.prevent="clearSelection">پاک کردن</a>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if ($step == 2 && !empty($fetchData['multiPlace']))
                        <button type="button" class="btn btn-outline-secondary" wire:click='backToPlaces'>بازگشت به انتخاب مطب</button>
                    @endif
                    <button type="button" class="btn btn-secondary" wire:click='dismisMOdal'
                        data-bs-dismiss="modal">بیخیال</button>
                    @if ($step == 2)
                        <button type="button" class="btn btn-primary" wire:click="continueWithSelected"
                            wire:loading.class="btn-loading disabled" wire:target="continueWithSelected"
                            @disabled(empty($selectedServices))>
                            ادامه
                            @if (count($selectedServices) > 0)
                                با {{ count($selectedServices) }} بخش
                            @endif
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
    <style>
        /* گروه بندی بخش ها */
        .service-group { margin-bottom: 1.25rem; }
        .service-group__header { display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; margin-bottom: .6rem; }
        .service-group__title { display: inline-flex; align-items: center; flex-wrap: wrap; gap: .45rem; font-weight: 700; font-size: .95rem; }
        .service-group__count { font-weight: 400; font-size: .8rem; color: #8f8fa3; }
        .service-group--nested { border: 1px solid #e6e6f0; border-radius: 10px; padding: .85rem; background: #fafafd; border-inline-start: 4px solid #d6d3fb; transition: border-color .15s; }
        .service-group--nested.has-selection { border-inline-start-color: var(--primary-bg-color, #6c5ffc); }

        /* هر بخش قابل انتخاب */
        .service-select-item { display: flex; align-items: center; gap: .6rem; height: 100%; min-height: 52px; padding: .6rem .75rem; background: #fff; border: 1px solid #e6e6f0; border-radius: 8px; cursor: pointer; user-select: none; transition: border-color .15s, box-shadow .15s, background-color .15s; }
        .service-select-item:hover { border-color: var(--primary-bg-color, #6c5ffc); }
        .service-select-item:focus-visible { outline: 2px solid var(--primary-bg-color, #6c5ffc); outline-offset: 2px; }
        .service-select-item.selected { border-color: var(--primary-bg-color, #6c5ffc); box-shadow: 0 0 0 1px var(--primary-bg-color, #6c5ffc); background-color: rgba(108, 95, 252, .06); }
        .service-select-item.is-parent { border-style: dashed; }
        .service-select-item__body { display: flex; flex-direction: column; min-width: 0; }
        .service-select-item__title { font-size: .9rem; font-weight: 600; line-height: 1.5; overflow-wrap: anywhere; }
        .service-select-item__flag { font-size: .72rem; color: #e6a23c; margin-top: 2px; }
        .service-check-box { width: 20px; height: 20px; border: 2px solid #b9b9cc; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; background: #fff; transition: all .15s; }
        .service-check-box .fa { font-size: 12px; color: #fff; opacity: 0; transition: opacity .15s; }
        .service-select-item.selected .service-check-box { background: var(--primary-bg-color, #6c5ffc); border-color: var(--primary-bg-color, #6c5ffc); }
        .service-select-item.selected .service-check-box .fa { opacity: 1; }
    </style>
@endpush
