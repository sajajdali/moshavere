<div>
    <div class="card">
        <div class="card-header border-bottom">
            <h3>روز های حضور</h3>
        </div>
        <div class="row mt-3">
            <div class="card-body">
                <p>در این قسمت روز هایی که پزشک در مطب حضور دارد را انتخاب و سپس ساعت هار مربوط به هر روز را در آن
                    وارد
                    بکنید!</p>
                <form wire:submit='addDayForDoctor' id="setting">
                    <div class="form-row">
                        {{-- saturday --}}
                        <div class="col-12 mt-3">
                            <div class="main-toggle-group d-flex align-items-center ms-0">
                                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self id="saturday"
                                    data-bs-toggle="collapse" href="#saturdayTimeCollaps" role="button"
                                    aria-expanded="false" aria-controls="saturdayTimeCollaps">
                                    <span></span>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted m-0">شنبه</p>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-12 mt-2" id="saturdayTimeCollaps" wire:ignore.self>
                            <div class="card card-body">
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
                                            <input wire:model='timeFrame.saturday.{{ $i }}.start'
                                                type="time" class="form-control" id="input-time">
                                        </div>
                                        <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                                ساعت:</label>
                                            <input wire:model='timeFrame.saturday.{{ $i }}.end'
                                                type="time" class="form-control" id="input-time">
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
                                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self id="sunday"
                                    data-bs-toggle="collapse" href="#sundayTimeCollaps" role="button"
                                    aria-expanded="false" aria-controls="sundayTimeCollaps">
                                    <span></span>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted m-0">یکشنبه</p>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-12 mt-2" id="sundayTimeCollaps" wire:ignore.self>
                            <div class="card card-body">
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
                                            <input wire:model='timeFrame.sunday.{{ $i }}.start'
                                                type="time" class="form-control" id="input-time">
                                        </div>
                                        <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                                ساعت:</label>
                                            <input wire:model='timeFrame.sunday.{{ $i }}.end'
                                                type="time" class="form-control" id="input-time">
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
                                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self id="monday"
                                    data-bs-toggle="collapse" href="#mondayTimeCollaps" role="button"
                                    aria-expanded="false" aria-controls="mondayTimeCollaps">
                                    <span></span>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted m-0">دوشنبه</p>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-12 mt-2" id="mondayTimeCollaps" wire:ignore.self>
                            <div class="card card-body">
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">تعیین زمان حضور برای دو شنبه</p>
                                    <div>
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="اضافه کردن بازه ی زمانی" wire:click="addCounter('monday')"
                                            class="btn btn-info rounded-pill text-center">
                                            <span wire:loading.remove wire:target="addCounter('monday')"> <span
                                                    class="d-flex align-item-center"><i
                                                        class="fa fa-2x fa-plus-circle" aria-hidden="true"></i>
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
                                            <input wire:model='timeFrame.monday.{{ $i }}.start'
                                                type="time" class="form-control" id="input-time">
                                        </div>
                                        <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                                ساعت:</label>
                                            <input wire:model='timeFrame.monday.{{ $i }}.end'
                                                type="time" class="form-control" id="input-time">
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
                                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self id="tuesday"
                                    data-bs-toggle="collapse" href="#tuesdayTimeCollaps" role="button"
                                    aria-expanded="false" aria-controls="tuesdayTimeCollaps">
                                    <span></span>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted m-0">سه شنبه</p>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-12 mt-2" id="tuesdayTimeCollaps" wire:ignore.self>
                            <div class="card card-body">
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">تعیین زمان حضور برای سه شنبه</p>
                                    <div>
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="اضافه کردن بازه ی زمانی" wire:click="addCounter('tuesday')"
                                            class="btn btn-info rounded-pill text-center">
                                            <span wire:loading.remove wire:target="addCounter('tuesday')"> <span
                                                    class="d-flex align-item-center"><i
                                                        class="fa fa-2x fa-plus-circle" aria-hidden="true"></i>
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
                                            <input wire:model='timeFrame.tuesday.{{ $i }}.start'
                                                type="time" class="form-control" id="input-time">
                                        </div>
                                        <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                                ساعت:</label>
                                            <input wire:model='timeFrame.tuesday.{{ $i }}.end'
                                                type="time" class="form-control" id="input-time">
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
                                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self id="wednesday"
                                    data-bs-toggle="collapse" href="#wednesdayTimeCollaps" role="button"
                                    aria-expanded="false" aria-controls="wednesdayTimeCollaps">
                                    <span></span>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted m-0">چهارشنبه</p>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-12 mt-2" id="wednesdayTimeCollaps" wire:ignore.self>
                            <div class="card card-body">
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">تعیین زمان حضور برای چهارشنبه</p>
                                    <div>
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="اضافه کردن بازه ی زمانی" wire:click="addCounter('wednesday')"
                                            class="btn btn-info rounded-pill text-center">
                                            <span wire:loading.remove wire:target="addCounter('wednesday')"> <span
                                                    class="d-flex align-item-center"><i
                                                        class="fa fa-2x fa-plus-circle" aria-hidden="true"></i>
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
                                            <input wire:model='timeFrame.wednesday.{{ $i }}.start'
                                                type="time" class="form-control" id="input-time">
                                        </div>
                                        <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                                ساعت:</label>
                                            <input wire:model='timeFrame.wednesday.{{ $i }}.end'
                                                type="time" class="form-control" id="input-time">
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
                                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self id="thursday"
                                    data-bs-toggle="collapse" href="#thursdayTimeCollaps" role="button"
                                    aria-expanded="false" aria-controls="thursdayTimeCollaps">
                                    <span></span>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted m-0">چهارشنبه</p>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-12 mt-2" id="thursdayTimeCollaps" wire:ignore.self>
                            <div class="card card-body">
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">تعیین زمان حضور برای چهارشنبه</p>
                                    <div>
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="اضافه کردن بازه ی زمانی" wire:click="addCounter('thursday')"
                                            class="btn btn-info rounded-pill text-center">
                                            <span wire:loading.remove wire:target="addCounter('thursday')"> <span
                                                    class="d-flex align-item-center"><i
                                                        class="fa fa-2x fa-plus-circle" aria-hidden="true"></i>
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
                                            <input wire:model='timeFrame.thursday.{{ $i }}.start'
                                                type="time" class="form-control" id="input-time">
                                        </div>
                                        <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                                ساعت:</label>
                                            <input wire:model='timeFrame.thursday.{{ $i }}.end'
                                                type="time" class="form-control" id="input-time">
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
                                <div class="toggle toggle-lg toggle-primary my-1 off" wire:ignore.self id="friday"
                                    data-bs-toggle="collapse" href="#fridayTimeCollaps" role="button"
                                    aria-expanded="false" aria-controls="fridayTimeCollaps">
                                    <span></span>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted m-0">جمعه</p>
                                </div>
                            </div>
                        </div>
                        <div class="collapse col-12 mt-2" id="fridayTimeCollaps" wire:ignore.self>
                            <div class="card card-body">
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">تعیین زمان حضور برای جمعه</p>
                                    <div>
                                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="اضافه کردن بازه ی زمانی" wire:click="addCounter('friday')"
                                            class="btn btn-info rounded-pill text-center">
                                            <span wire:loading.remove wire:target="addCounter('friday')"> <span
                                                    class="d-flex align-item-center"><i
                                                        class="fa fa-2x fa-plus-circle" aria-hidden="true"></i>
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
                                            <input wire:model='timeFrame.friday.{{ $i }}.start'
                                                type="time" class="form-control" id="input-time">
                                        </div>
                                        <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                                ساعت:</label>
                                            <input wire:model='timeFrame.friday.{{ $i }}.end'
                                                type="time" class="form-control" id="input-time">
                                        </div>
                                    </div>
                                @endfor

                            </div>
                        </div>

                        {{-- friday --}}
                    </div>

                </form>
            </div>
        </div>
   
    </div>
</div>
