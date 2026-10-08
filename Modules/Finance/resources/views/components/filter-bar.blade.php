{{-- $show: the filters of the page, $options: doctors / purposes / methods --}}
<div class="fin-card">
    <div class="fin-filters">
        <div class="fin-ranges">
            @foreach (['today' => 'امروز', 'week' => '7 روز اخیر', 'month' => 'این ماه', 'last_month' => 'ماه قبل', 'year' => 'امسال', 'all' => 'همه زمان ها'] as $key => $label)
                <button type="button" class="fin-range" wire:click="setRange('{{ $key }}')">{{ $label }}</button>
            @endforeach
        </div>
        <div class="fin-filter-grid">
            @if (in_array('search', $show))
                <div>
                    <label>جستجوی بیمار (نام یا موبایل)</label>
                    <input type="text" class="form-control" wire:model.live.debounce.500ms="filter.search" placeholder="جستجو...">
                </div>
            @endif
            <div>
                <label>از تاریخ</label>
                <input type="text" class="form-control" data-jdp wire:model.live="filter.from" placeholder="1405/01/01" autocomplete="off">
            </div>
            <div>
                <label>تا تاریخ</label>
                <input type="text" class="form-control" data-jdp wire:model.live="filter.to" placeholder="1405/12/29" autocomplete="off">
            </div>
            @if (in_array('source', $show))
                <div>
                    <label>منبع پرداخت</label>
                    <select class="form-select" wire:model.live="filter.source">
                        <option value="">همه</option>
                        <option value="system">سیستمی (تراکنش ها)</option>
                        <option value="manual">ثبت دستی</option>
                    </select>
                </div>
            @endif
            @if (in_array('type', $show))
                <div>
                    <label>نوع</label>
                    <select class="form-select" wire:model.live="filter.type">
                        <option value="">دریافت و بازگشت وجه</option>
                        <option value="payment">فقط دریافت</option>
                        <option value="refund">فقط بازگشت وجه</option>
                    </select>
                </div>
            @endif
            @if (in_array('method', $show))
                <div>
                    <label>روش پرداخت</label>
                    <select class="form-select" wire:model.live="filter.method">
                        <option value="">همه</option>
                        @foreach ($options['methods'] as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (in_array('purpose', $show))
                <div>
                    <label>دلیل پرداخت</label>
                    <select class="form-select" wire:model.live="filter.purpose_id">
                        <option value="">همه</option>
                        @foreach ($options['purposes'] as $purpose)
                            <option value="{{ $purpose->id }}">{{ $purpose->title }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if (in_array('doctor', $show))
                <div>
                    <label>پزشک</label>
                    <select class="form-select" wire:model.live="filter.doctor_id">
                        <option value="">همه</option>
                        @foreach ($options['doctors'] as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->fullName }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        <div class="mt-3">
            <button type="button" class="btn btn-light btn-sm" wire:click="resetFilters"><i class="fa fa-times me-1"></i> پاک کردن فیلترها</button>
        </div>
    </div>
</div>
