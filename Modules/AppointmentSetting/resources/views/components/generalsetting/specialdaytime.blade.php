<div class="@error('form.specialDaytimeValues.*') border border-danger @enderror">
    <div class="card-header border-bottom d-flex justify-content-between">
        <h3>
            <i class="fa fa-calendar-plus-o me-2 d-none d-sm-inline" aria-hidden="true"></i>
            <span>
                تغییر ساعت حضور برای<span class="text-primary"> یک روز خاص </span>
            </span>
        </h3>
        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
            <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($form['specialDaytimeValues']) && count($form['specialDaytimeValues']) >= 1) on  @else off @endif"
                data-id="specialDayTimeSetting" wire:ignore.self data-bs-toggle="collapse" href="#specialDayTimeSetting"
                role="button" aria-expanded="false" aria-controls="specialDayTimeSetting">
                <span></span>
            </div>
        </div>
    </div>
    <div class="collapse @if (isset($form['specialDaytimeValues']) && count($form['specialDaytimeValues']) >= 1) show @endif " id="specialDayTimeSetting" wire:ignore.self>
        <div class="card-body">
            @error('form.specialDaytimeValues.*')
                {{ $message }}
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger"> در صورت انتخاب تاریخ ، لازم هست که ساعت های مخصوص آن روز را نیز انتخاب کنید
                    </p>
                </div>
            @enderror
            <div class="card card-body">
                <div class="row">
                    <div class="col-md-8 mb-4">
                        تاریخ روزی که مایل هستید ساعتی ، خارج از ساعت برنامه تعیین شده در قسمت بالا را داشته باشد انتخاب
                        کنید
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="اضافه کردن روز" wire:click="addFormCounter('specialDaySetting')"
                                    class="btn btn-secondary  text-center">
                                    <span wire:loading.remove wire:target="addFormCounter('specialDaySetting')">
                                        <span class="d-flex align-items-center">
                                            <i class="fa fa-plus fa-xl " aria-hidden="true"></i>
                                            <span class="ms-1">اضافه کردن روز</span></span></span>
                                    <span wire:loading wire:target="addFormCounter('specialDaySetting')">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                        </div>
                                    </span>
                                </button>
                                @if ($form['specialDaySetting'] > 1)
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="حذف کردن روز" wire:click="removeFormCounter('specialDaySetting')"
                                        class="btn btn-danger  text-center">
                                        <span wire:loading.remove wire:target="removeFormCounter('specialDaySetting')">
                                            <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف روز</span>
                                        </span>
                                        <span wire:loading wire:target="removeFormCounter('specialDaySetting')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @for ($i = 0; $i < $form['specialDaySetting']; $i++)
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 pt-2">
                                <label class="text-primary" for="basic-url">انتخاب تاریخ:</label>
                            </div>
                            <div class="col-md-9">
                                <div class="input-group mb-3">
                                    <input type="text" wire:model='form.specialDaydateValues.{{ $i }}'  data-jdp data-name="form.specialDaydateValues.{{ $i }}"
                                        data-id="{{ $i }}" class="form-control specialDate">
                                </div>
                            </div>
                            <div class="d-flex mt-2">
                                <p class="text-muted"> <strong class="me-1"> نکته!! </strong> نوبت دهی بهت از
                                    تاریخ
                                    انتخابی
                                    غیر
                                    فعال شود </p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <p class="text-muted">تعیین بازه زمانی برای روز</p>
                            <div>
                                <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="اضافه کردن بازه ی زمانی"
                                    wire:click="addspecialDayTimeCounter('specialTimeCounter','{{ $i }}')"
                                    class="btn  btn-sm btn-info rounded-pill text-center">
                                    <span wire:loading.remove
                                        wire:target="addspecialDayTimeCounter('specialTimeCounter',{{ $i }})">
                                        <span class="d-flex align-item-center"><i class="fa fa-xl fa-plus-circle"
                                                aria-hidden="true"></i>
                                            <span class="ms-1">اضافه کردن
                                                ساعت</span></span></span>
                                    <span wire:loading
                                        wire:target="addspecialDayTimeCounter('specialTimeCounter',{{ $i }})">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                        </div>
                                    </span>
                                </button>
                                @if ($form['specialTimeCounter'][$i] > 1)
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="حذف کردن بازه ی زمانی"
                                        wire:click="removespecialDayTimeCounter('specialTimeCounter',{{ $i }})"
                                        class="btn btn-sm btn-danger rounded-pill text-center">
                                        <span wire:loading.remove
                                            wire:target="removespecialDayTimeCounter('specialTimeCounter',{{ $i }})">
                                            <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                                زمانی</span>
                                        </span>
                                        <span wire:loading
                                            wire:target="removespecialDayTimeCounter('specialTimeCounter',{{ $i }})">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @for ($j = $form['timeitrator'][$i]; $j < $form['specialTimeCounter'][$i]; $j++)
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <label for="input-time-s-{{ $j }}" class="form-label">از
                                        ساعت</label>
                                    <input
                                        wire:model='form.specialDaytimeValues.{{ $i }}.{{ $j }}.start'
                                        type="time" wire:ignore.self class="form-control"
                                        id="input-time-s-{{ $j }}">
                                </div>
                                <div class="col-12 col-md-6"> <label for="input-time-e-{{ $j }}"
                                        class="form-label">تا
                                        ساعت:</label>
                                    <input
                                        wire:model='form.specialDaytimeValues.{{ $i }}.{{ $j }}.end'
                                        type="time" wire:ignore.self class="form-control"
                                        id="input-time-e-{{$j}}">
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
