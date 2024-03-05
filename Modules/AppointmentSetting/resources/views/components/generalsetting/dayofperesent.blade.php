<div>
    <div class="card">
        <div class="card-header border-bottom">
            <h3>روز های حضور</h3>
        </div>
        <div class="row mt-3">
            <div class="card-body">
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
                    <div class="collapse @if (isset($this->form['visitType']['saturday']) && $this->form['visitType']['saturday']) show @endif col-12 mt-2" id="saturdayTimeCollaps" wire:ignore.self>
                        <div class="card card-body @if ($errors->has('form.timeFrame.saturday.*')) border border-danger @endif">
                            {{-- TODO::modify Error message --}}
                            {{-- <div class="alert alert-danger" role="alert"> درصورت فعال سازی روز لطفا ساعت حضور
                                    در
                                    روز را تعیین کنید! </div> --}}
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
                                    @if ($counter['saturday'] > 1)
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="حذف کردن بازه ی زمانی" wire:click="removeCounter('saturday')"
                                            class="btn btn-danger rounded-pill text-center">
                                            <span wire:loading.remove wire:target="removeCounter('saturday')">
                                                <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                                    زمانی</span>
                                            </span>
                                            <span wire:loading wire:target="removeCounter('saturday')">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                </div>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['saturday']; $i++)
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.saturday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.saturday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                </div>
                            @endfor

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
                    <div class="collapse  @if (isset($this->form['visitType']['sunday']) && $this->form['visitType']['sunday']) show @endif col-12 mt-2" id="sundayTimeCollaps" wire:ignore.self>
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
                                    @if ($counter['sunday'] > 1)
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="حذف کردن بازه ی زمانی" wire:click="removeCounter('sunday')"
                                            class="btn btn-danger rounded-pill text-center">
                                            <span wire:loading.remove wire:target="removeCounter('sunday')">
                                                <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                                    زمانی</span>
                                            </span>
                                            <span wire:loading wire:target="removeCounter('sunday')">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                </div>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['sunday']; $i++)
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.sunday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.sunday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                </div>
                            @endfor

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
                    <div class="collapse   @if (isset($this->form['visitType']['monday']) && $this->form['visitType']['monday']) show @endif col-12 mt-2" id="mondayTimeCollaps" wire:ignore.self>
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
                                    @if ($counter['monday'] > 1)
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="حذف کردن بازه ی زمانی" wire:click="removeCounter('monday')"
                                            class="btn btn-danger rounded-pill text-center">
                                            <span wire:loading.remove wire:target="removeCounter('monday')">
                                                <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                                    زمانی</span>
                                            </span>
                                            <span wire:loading wire:target="removeCounter('monday')">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                </div>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['monday']; $i++)
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.monday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.monday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                </div>
                            @endfor

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
                    <div class="collapse  @if (isset($this->form['visitType']['tuesday']) && $this->form['visitType']['tuesday']) show @endif  col-12 mt-2" id="tuesdayTimeCollaps" wire:ignore.self>
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
                                    @if ($counter['tuesday'] > 1)
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="حذف کردن بازه ی زمانی" wire:click="removeCounter('tuesday')"
                                            class="btn btn-danger rounded-pill text-center">
                                            <span wire:loading.remove wire:target="removeCounter('tuesday')">
                                                <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                                    زمانی</span>
                                            </span>
                                            <span wire:loading wire:target="removeCounter('tuesday')">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                </div>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['tuesday']; $i++)
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.tuesday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.tuesday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                </div>
                            @endfor

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
                    <div class="collapse @if (isset($this->form['visitType']['wednesday']) && $this->form['visitType']['wednesday']) show @endif col-12 mt-2" id="wednesdayTimeCollaps" wire:ignore.self>
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
                                    @if ($counter['wednesday'] > 1)
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="حذف کردن بازه ی زمانی" wire:click="removeCounter('wednesday')"
                                            class="btn btn-danger rounded-pill text-center">
                                            <span wire:loading.remove wire:target="removeCounter('wednesday')">
                                                <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                                    زمانی</span>
                                            </span>
                                            <span wire:loading wire:target="removeCounter('wednesday')">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                </div>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['wednesday']; $i++)
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.wednesday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.wednesday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                </div>
                            @endfor

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
                    <div class="collapse col-12 mt-2 @if (isset($this->form['visitType']['thursday']) && $this->form['visitType']['thursday']) show @endif" id="thursdayTimeCollaps" wire:ignore.self>
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
                                    @if ($counter['thursday'] > 1)
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="حذف کردن بازه ی زمانی" wire:click="removeCounter('thursday')"
                                            class="btn btn-danger rounded-pill text-center">
                                            <span wire:loading.remove wire:target="removeCounter('thursday')">
                                                <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                                    زمانی</span>
                                            </span>
                                            <span wire:loading wire:target="removeCounter('thursday')">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                </div>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['thursday']; $i++)
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.thursday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.thursday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                </div>
                            @endfor

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
                    <div class="collapse @if (isset($this->form['visitType']['friday']) && $this->form['visitType']['friday']) show @endif  col-12 mt-2" id="fridayTimeCollaps" wire:ignore.self>
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
                                    @if ($counter['friday'] > 1)
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="حذف کردن بازه ی زمانی" wire:click="removeCounter('friday')"
                                            class="btn btn-danger rounded-pill text-center">
                                            <span wire:loading.remove wire:target="removeCounter('friday')">
                                                <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                                    زمانی</span>
                                            </span>
                                            <span wire:loading wire:target="removeCounter('friday')">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                </div>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @for ($i = 0; $i < $counter['friday']; $i++)
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="input-time" class="form-label">از ساعت</label>
                                        <input wire:model='form.timeFrame.friday.{{ $i }}.start'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                    <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                            ساعت:</label>
                                        <input wire:model='form.timeFrame.friday.{{ $i }}.end'
                                            type="time" wire:ignore.self class="form-control" id="input-time">
                                    </div>
                                </div>
                            @endfor

                        </div>
                    </div>

                    {{-- friday --}}
                </div>
            </div>
        </div>

    </div>
</div>
