<div>
    <div class="card @error('form.timeFrame') border border-danger @enderror" x-data="{ customVisitTate: @entangle('hasSpecialTimeForVisit') }">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h3 class="d-flex align-items-center">
                <i class="fa fa-calendar fa-xl me-2 d-none d-sm-inline" aria-hidden="true"></i>
                <span>روز های حضور</span>
            </h3>
            <button type="button" class="btn btn-outline-info text-center"
                x-on:click="customVisitTate = !customVisitTate">
                افزودن زمان ویزیت اختصاصی
            </button>
        </div>
        <div class="row mt-3">
            <div class="card-body">
                @error('form.timeFrame')
                    <div class="alert alert-danger" role="alert">
                        <p class="text-danger"><strong>خطا!!</strong>لطفا حداقل برای یک روز زمان حضور تعیین کنید</p>
                    </div>
                @enderror
                @error('form.timeFrame.*')
                    <div class="alert alert-danger" role="alert">
                        <p class="text-danger"><strong>خطا!!</strong>در صورتی که یک روز را فعال میکنید ، باید برای آن ساعت
                            تعیین کنید</p>
                    </div>
                @enderror
                <p>در این قسمت روز هایی که پزشک در مطب حضور دارد را انتخاب و سپس ساعت هار مربوط به هر روز را در آن
                    وارد
                    بکنید!</p>
                <div class="form-row">
                    {{-- saturday --}}
                    <div class="col-12 mt-3">
                        <div class="main-toggle-group d-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (isset($this->form['visitType']['saturday']) && $this->form['visitType']['saturday']) on @else off @endif"
                                data-id="visitType.saturday" wire:ignore.self id="saturday" data-bs-toggle="collapse"
                                href="#saturdayTimeCollaps" role="button" aria-expanded="false"
                                aria-controls="saturdayTimeCollaps">
                                <span></span>
                            </div>
                            <div class="ms-2">
                                <p class="text-muted m-0">شنبه</p>
                            </div>
                        </div>
                    </div>
                    <div class="collapse @if (isset($this->form['visitType']['saturday']) && $this->form['visitType']['saturday']) show @endif col-12 mt-2"
                        id="saturdayTimeCollaps" wire:ignore.self>
                        <div class="card card-body @if ($errors->has('form.timeFrame.saturday.*')) border border-danger @endif">
                            <div class="d-flex justify-content-between">
                                <p class="text-muted">تعیین زمان حضور برای شنبه</p>
                                <div>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="اضافه کردن بازه ی زمانی" wire:click="addCounter('saturday')"
                                        class="btn btn-info rounded-pill text-center">
                                        <span wire:loading.remove wire:target="addCounter('saturday')"> <span
                                                class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                                    aria-hidden="true"></i>
                                                <span class="ms-1">اضافه کردن
                                                    ساعت</span></span></span>
                                        <span wire:loading wire:target="addCounter('saturday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="کپی این روز به سایر روزها (به جز جمعه)"
                                        wire:click="cloneDayToOthers('saturday')"
                                        class="btn btn-outline-primary rounded-pill text-center">
                                        <span wire:loading.remove wire:target="cloneDayToOthers('saturday')">
                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                            <span class="ms-1">کپی به سایر روزها</span>
                                        </span>
                                        <span wire:loading wire:target="cloneDayToOthers('saturday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['saturday']; $i++)
                                <div class="row align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.saturday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-5"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.saturday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        @if ($counter['saturday'] > 1)
                                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="حذف این بازه ی زمانی"
                                                wire:click="removeTimeRow('saturday', {{ $i }})"
                                                class="btn btn-danger rounded-pill text-center">
                                                <span wire:loading.remove
                                                    wire:target="removeTimeRow('saturday', {{ $i }})">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <span wire:loading
                                                    wire:target="removeTimeRow('saturday', {{ $i }})">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                    </div>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                            <div x-show="customVisitTate">
                                <div class="input-group mt-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">مدت زمان مورد نیاز ویزیت برای شنبه</span>
                                    </div>
                                    <input type="number"
                                        class="form-control  @error('form.specialVisitTime.saturday') is-invalid @enderror"
                                        id="howManydayBEfore" aria-describedby="basic-addon3"
                                        wire:model='form.specialVisitTime.saturday'>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="w-75" style="opacity: 0.5">
                    {{-- saturday --}}
                    {{-- sunday --}}
                    <div class="col-12">
                        <div class="main-toggle-group d-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($this->form['visitType']['sunday']) && $this->form['visitType']['sunday']) on @else off @endif"
                                data-id="visitType.sunday" wire:ignore.self id="sunday" data-bs-toggle="collapse"
                                href="#sundayTimeCollaps" role="button" aria-expanded="false"
                                aria-controls="sundayTimeCollaps">
                                <span></span>
                            </div>
                            <div class="ms-2">
                                <p class="text-muted m-0">یکشنبه</p>
                            </div>
                        </div>
                    </div>
                    <div class="collapse  @if (isset($this->form['visitType']['sunday']) && $this->form['visitType']['sunday']) show @endif col-12 mt-2"
                        id="sundayTimeCollaps" wire:ignore.self>
                        <div class="card card-body @if ($errors->has('form.timeFrame.sunday.*')) border border-danger @endif">
                            <div class="d-flex justify-content-between">
                                <p class="text-muted">تعیین زمان حضور برای یک شنبه</p>
                                <div>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="اضافه کردن بازه ی زمانی" wire:click="addCounter('sunday')"
                                        class="btn btn-info rounded-pill text-center">
                                        <span wire:loading.remove wire:target="addCounter('sunday')"> <span
                                                class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                                    aria-hidden="true"></i>
                                                <span class="ms-1">اضافه کردن
                                                    ساعت</span></span></span>
                                        <span wire:loading wire:target="addCounter('sunday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="کپی این روز به سایر روزها (به جز جمعه)"
                                        wire:click="cloneDayToOthers('sunday')"
                                        class="btn btn-outline-primary rounded-pill text-center">
                                        <span wire:loading.remove wire:target="cloneDayToOthers('sunday')">
                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                            <span class="ms-1">کپی به سایر روزها</span>
                                        </span>
                                        <span wire:loading wire:target="cloneDayToOthers('sunday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['sunday']; $i++)
                                <div class="row align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.sunday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-5"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.sunday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        @if ($counter['sunday'] > 1)
                                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="حذف این بازه ی زمانی"
                                                wire:click="removeTimeRow('sunday', {{ $i }})"
                                                class="btn btn-danger rounded-pill text-center">
                                                <span wire:loading.remove
                                                    wire:target="removeTimeRow('sunday', {{ $i }})">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <span wire:loading
                                                    wire:target="removeTimeRow('sunday', {{ $i }})">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                    </div>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endfor

                            <div x-show="customVisitTate">
                                <div class="input-group mt-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">مدت زمان مورد نیاز ویزیت برای یکشنبه</span>
                                    </div>
                                    <input type="number"
                                        class="form-control  @error('form.specialVisitTime.sunday') is-invalid @enderror"
                                        id="howManydayBEfore" aria-describedby="basic-addon3"
                                        wire:model='form.specialVisitTime.sunday'>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="w-75" style="opacity: 0.5">
                    {{-- sunday --}}
                    {{-- monday --}}
                    <div class="col-12">
                        <div class="main-toggle-group d-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (isset($this->form['visitType']['monday']) && $this->form['visitType']['monday']) on @else off @endif"
                                data-id="visitType.monday" wire:ignore.self id="monday" data-bs-toggle="collapse"
                                href="#mondayTimeCollaps" role="button" aria-expanded="false"
                                aria-controls="mondayTimeCollaps">
                                <span></span>
                            </div>
                            <div class="ms-2">
                                <p class="text-muted m-0">دوشنبه</p>
                            </div>
                        </div>
                    </div>
                    <div class="collapse   @if (isset($this->form['visitType']['monday']) && $this->form['visitType']['monday']) show @endif col-12 mt-2"
                        id="mondayTimeCollaps" wire:ignore.self>
                        <div class="card card-body @if ($errors->has('form.timeFrame.monday.*')) border border-danger @endif ">
                            <div class="d-flex justify-content-between">
                                <p class="text-muted">تعیین زمان حضور برای دو شنبه</p>
                                <div>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="اضافه کردن بازه ی زمانی" wire:click="addCounter('monday')"
                                        class="btn btn-info rounded-pill text-center">
                                        <span wire:loading.remove wire:target="addCounter('monday')"> <span
                                                class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                                    aria-hidden="true"></i>
                                                <span class="ms-1">اضافه کردن
                                                    ساعت</span></span></span>
                                        <span wire:loading wire:target="addCounter('monday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="کپی این روز به سایر روزها (به جز جمعه)"
                                        wire:click="cloneDayToOthers('monday')"
                                        class="btn btn-outline-primary rounded-pill text-center">
                                        <span wire:loading.remove wire:target="cloneDayToOthers('monday')">
                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                            <span class="ms-1">کپی به سایر روزها</span>
                                        </span>
                                        <span wire:loading wire:target="cloneDayToOthers('monday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['monday']; $i++)
                                <div class="row align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.monday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-5"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.monday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        @if ($counter['monday'] > 1)
                                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="حذف این بازه ی زمانی"
                                                wire:click="removeTimeRow('monday', {{ $i }})"
                                                class="btn btn-danger rounded-pill text-center">
                                                <span wire:loading.remove
                                                    wire:target="removeTimeRow('monday', {{ $i }})">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <span wire:loading
                                                    wire:target="removeTimeRow('monday', {{ $i }})">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                    </div>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                            <div x-show="customVisitTate">
                                <div class="input-group mt-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">مدت زمان مورد نیاز ویزیت برای دوشنبه</span>
                                    </div>
                                    <input type="number"
                                        class="form-control  @error('form.specialVisitTime.monday') is-invalid @enderror"
                                        id="howManydayBEfore" aria-describedby="basic-addon3"
                                        wire:model='form.specialVisitTime.monday'>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="w-75" style="opacity: 0.5">
                    {{-- monday --}}
                    {{-- tuesday --}}
                    <div class="col-12">
                        <div class="main-toggle-group d-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($this->form['visitType']['tuesday']) && $this->form['visitType']['tuesday']) on @else off @endif"
                                data-id="visitType.tuesday" wire:ignore.self id="tuesday" data-bs-toggle="collapse"
                                href="#tuesdayTimeCollaps" role="button" aria-expanded="false"
                                aria-controls="tuesdayTimeCollaps">
                                <span></span>
                            </div>
                            <div class="ms-2">
                                <p class="text-muted m-0">سه شنبه</p>
                            </div>
                        </div>
                    </div>
                    <div class="collapse  @if (isset($this->form['visitType']['tuesday']) && $this->form['visitType']['tuesday']) show @endif  col-12 mt-2"
                        id="tuesdayTimeCollaps" wire:ignore.self>
                        <div class="card card-body  @if ($errors->has('form.timeFrame.tuesday.*')) border border-danger @endif">
                            <div class="d-flex justify-content-between">
                                <p class="text-muted">تعیین زمان حضور برای سه شنبه</p>
                                <div>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="اضافه کردن بازه ی زمانی" wire:click="addCounter('tuesday')"
                                        class="btn btn-info rounded-pill text-center">
                                        <span wire:loading.remove wire:target="addCounter('tuesday')"> <span
                                                class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                                    aria-hidden="true"></i>
                                                <span class="ms-1">اضافه کردن
                                                    ساعت</span></span></span>
                                        <span wire:loading wire:target="addCounter('tuesday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="کپی این روز به سایر روزها (به جز جمعه)"
                                        wire:click="cloneDayToOthers('tuesday')"
                                        class="btn btn-outline-primary rounded-pill text-center">
                                        <span wire:loading.remove wire:target="cloneDayToOthers('tuesday')">
                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                            <span class="ms-1">کپی به سایر روزها</span>
                                        </span>
                                        <span wire:loading wire:target="cloneDayToOthers('tuesday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['tuesday']; $i++)
                                <div class="row align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.tuesday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-5"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.tuesday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        @if ($counter['tuesday'] > 1)
                                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="حذف این بازه ی زمانی"
                                                wire:click="removeTimeRow('tuesday', {{ $i }})"
                                                class="btn btn-danger rounded-pill text-center">
                                                <span wire:loading.remove
                                                    wire:target="removeTimeRow('tuesday', {{ $i }})">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <span wire:loading
                                                    wire:target="removeTimeRow('tuesday', {{ $i }})">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                    </div>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                            <div x-show="customVisitTate">
                                <div class="input-group mt-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">مدت زمان مورد نیاز ویزیت برای سه شنبه</span>
                                    </div>
                                    <input type="number"
                                        class="form-control  @error('form.specialVisitTime.tuesday') is-invalid @enderror"
                                        id="howManydayBEfore" aria-describedby="basic-addon3"
                                        wire:model='form.specialVisitTime.tuesday'>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="w-75" style="opacity: 0.5">
                    {{-- tuesday --}}
                    {{-- wednesday --}}
                    <div class="col-12">
                        <div class="main-toggle-group d-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($this->form['visitType']['wednesday']) && $this->form['visitType']['wednesday']) on @else off @endif"
                                data-id="visitType.wednesday" wire:ignore.self id="wednesday"
                                data-bs-toggle="collapse" href="#wednesdayTimeCollaps" role="button"
                                aria-expanded="false" aria-controls="wednesdayTimeCollaps">
                                <span></span>
                            </div>
                            <div class="ms-2">
                                <p class="text-muted m-0">چهارشنبه</p>
                            </div>
                        </div>
                    </div>
                    <div class="collapse @if (isset($this->form['visitType']['wednesday']) && $this->form['visitType']['wednesday']) show @endif col-12 mt-2"
                        id="wednesdayTimeCollaps" wire:ignore.self>
                        <div class="card card-body @if ($errors->has('form.timeFrame.wednesday.*')) border border-danger @endif">
                            <div class="d-flex justify-content-between">
                                <p class="text-muted">تعیین زمان حضور برای چهارشنبه</p>
                                <div>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="اضافه کردن بازه ی زمانی" wire:click="addCounter('wednesday')"
                                        class="btn btn-info rounded-pill text-center">
                                        <span wire:loading.remove wire:target="addCounter('wednesday')"> <span
                                                class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                                    aria-hidden="true"></i>
                                                <span class="ms-1">اضافه کردن
                                                    ساعت</span></span></span>
                                        <span wire:loading wire:target="addCounter('wednesday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="کپی این روز به سایر روزها (به جز جمعه)"
                                        wire:click="cloneDayToOthers('wednesday')"
                                        class="btn btn-outline-primary rounded-pill text-center">
                                        <span wire:loading.remove wire:target="cloneDayToOthers('wednesday')">
                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                            <span class="ms-1">کپی به سایر روزها</span>
                                        </span>
                                        <span wire:loading wire:target="cloneDayToOthers('wednesday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['wednesday']; $i++)
                                <div class="row align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.wednesday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-5"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.wednesday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        @if ($counter['wednesday'] > 1)
                                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="حذف این بازه ی زمانی"
                                                wire:click="removeTimeRow('wednesday', {{ $i }})"
                                                class="btn btn-danger rounded-pill text-center">
                                                <span wire:loading.remove
                                                    wire:target="removeTimeRow('wednesday', {{ $i }})">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <span wire:loading
                                                    wire:target="removeTimeRow('wednesday', {{ $i }})">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                    </div>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                            <div x-show="customVisitTate">
                                <div class="input-group mt-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">مدت زمان مورد نیاز ویزیت برای چهارشنبه</span>
                                    </div>
                                    <input type="number"
                                        class="form-control  @error('form.specialVisitTime.wednesday') is-invalid @enderror"
                                        id="howManydayBEfore" aria-describedby="basic-addon3"
                                        wire:model='form.specialVisitTime.wednesday'>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="w-75" style="opacity: 0.5">
                    {{-- wednesday --}}
                    {{-- thursday --}}
                    <div class="col-12">
                        <div class="main-toggle-group d-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1 @if (isset($this->form['visitType']['thursday']) && $this->form['visitType']['thursday']) on @else off @endif  customCheckbox"
                                data-id="visitType.thursday" wire:ignore.self id="thursday"
                                data-bs-toggle="collapse" href="#thursdayTimeCollaps" role="button"
                                aria-expanded="false" aria-controls="thursdayTimeCollaps">
                                <span></span>
                            </div>
                            <div class="ms-2">
                                <p class="text-muted m-0">پنج شنبه</p>
                            </div>
                        </div>
                    </div>
                    <div class="collapse col-12 mt-2 @if (isset($this->form['visitType']['thursday']) && $this->form['visitType']['thursday']) show @endif"
                        id="thursdayTimeCollaps" wire:ignore.self>
                        <div class="card card-body @if ($errors->has('form.timeFrame.thursday.*')) border border-danger @endif">
                            <div class="d-flex justify-content-between">
                                <p class="text-muted">تعیین زمان حضور برای پنجشنبه</p>
                                <div>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="اضافه کردن بازه ی زمانی" wire:click="addCounter('thursday')"
                                        class="btn btn-info rounded-pill text-center">
                                        <span wire:loading.remove wire:target="addCounter('thursday')"> <span
                                                class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                                    aria-hidden="true"></i>
                                                <span class="ms-1">اضافه کردن
                                                    ساعت</span></span></span>
                                        <span wire:loading wire:target="addCounter('thursday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="کپی این روز به سایر روزها (به جز جمعه)"
                                        wire:click="cloneDayToOthers('thursday')"
                                        class="btn btn-outline-primary rounded-pill text-center">
                                        <span wire:loading.remove wire:target="cloneDayToOthers('thursday')">
                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                            <span class="ms-1">کپی به سایر روزها</span>
                                        </span>
                                        <span wire:loading wire:target="cloneDayToOthers('thursday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['thursday']; $i++)
                                <div class="row align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.thursday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-5"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.thursday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        @if ($counter['thursday'] > 1)
                                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="حذف این بازه ی زمانی"
                                                wire:click="removeTimeRow('thursday', {{ $i }})"
                                                class="btn btn-danger rounded-pill text-center">
                                                <span wire:loading.remove
                                                    wire:target="removeTimeRow('thursday', {{ $i }})">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <span wire:loading
                                                    wire:target="removeTimeRow('thursday', {{ $i }})">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                    </div>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                            <div x-show="customVisitTate">
                                <div class="input-group mt-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">مدت زمان مورد نیاز ویزیت برای پنجشنبه</span>
                                    </div>
                                    <input type="number"
                                        class="form-control  @error('form.specialVisitTime.thursday') is-invalid @enderror"
                                        id="howManydayBEfore" aria-describedby="basic-addon3"
                                        wire:model='form.specialVisitTime.thursday'>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="w-75" style="opacity: 0.5">
                    {{-- thursday --}}
                    {{-- friday --}}
                    <div class="col-12 ">
                        <div class="main-toggle-group d-flex align-items-center ms-0">
                            <div class="toggle toggle-lg toggle-primary my-1 customCheckbox  @if (isset($this->form['visitType']['friday']) && $this->form['visitType']['friday']) on @else off @endif "
                                data-id="visitType.friday" wire:ignore.self id="friday" data-bs-toggle="collapse"
                                href="#fridayTimeCollaps" role="button" aria-expanded="false"
                                aria-controls="fridayTimeCollaps">
                                <span></span>
                            </div>
                            <div class="ms-2">
                                <p class="text-muted m-0">جمعه</p>
                            </div>
                        </div>
                    </div>
                    <div class="collapse @if (isset($this->form['visitType']['friday']) && $this->form['visitType']['friday']) show @endif  col-12 mt-2"
                        id="fridayTimeCollaps" wire:ignore.self>
                        <div class="card card-body @if ($errors->has('form.timeFrame.friday.*')) border border-danger @endif">
                            <div class="d-flex justify-content-between">
                                <p class="text-muted">تعیین زمان حضور برای جمعه</p>
                                <div>
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="اضافه کردن بازه ی زمانی" wire:click="addCounter('friday')"
                                        class="btn btn-info rounded-pill text-center">
                                        <span wire:loading.remove wire:target="addCounter('friday')"> <span
                                                class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                                    aria-hidden="true"></i>
                                                <span class="ms-1">اضافه کردن
                                                    ساعت</span></span></span>
                                        <span wire:loading wire:target="addCounter('friday')">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['friday']; $i++)
                                <div class="row align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.friday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-5"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.friday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        @if ($counter['friday'] > 1)
                                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="حذف این بازه ی زمانی"
                                                wire:click="removeTimeRow('friday', {{ $i }})"
                                                class="btn btn-danger rounded-pill text-center">
                                                <span wire:loading.remove
                                                    wire:target="removeTimeRow('friday', {{ $i }})">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </span>
                                                <span wire:loading
                                                    wire:target="removeTimeRow('friday', {{ $i }})">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                    </div>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                            <div x-show="customVisitTate">
                                <div class="input-group mt-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">مدت زمان مورد نیاز ویزیت برای جمعه</span>
                                    </div>
                                    <input type="number"
                                        class="form-control  @error('form.specialVisitTime.friday') is-invalid @enderror"
                                        id="howManydayBEfore" aria-describedby="basic-addon3"
                                        wire:model='form.specialVisitTime.friday'>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- friday --}}
                </div>
            </div>
        </div>

    </div>
</div>
