<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">ثبت عدم حضور</h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="card">
        <div class="card-body">
            <div class="card-title d-flex justify-content-between">
                <h3>عدم حضور</h3>
                <div>
                    <button type="button" wire:click="addCounter('number')"
                        class="btn btn-info rounded-pill text-center my-3 my-sm-0">
                        <span wire:loading.remove wire:target="addCounter('number')"> <span
                                class="d-flex align-items-center"><i class="fa fa-plus fa-lg me-1"
                                    aria-hidden="true"></i>
                                <span class="ms-1">اضافه کردن تاریخ</span></span></span>
                        <span wire:loading wire:target="addCounter('number')">
                            <div class="spinner-border spinner-border-sm" role="status">
                            </div>
                        </span>
                    </button>
                    @if ($counter['number'] > 1)
                        <button type="button" wire:click="removeCounter('number')"
                            class="btn btn-danger rounded-pill text-center">
                            <span wire:loading.remove wire:target="removeCounter('number')">
                                <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه</span>
                            </span>
                            <span wire:loading wire:target="removeCounter('number')">
                                <div class="spinner-border spinner-border-sm" role="status">
                                </div>
                            </span>
                        </button>
                    @endif
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-9"></div>
                <div class="col-3 text-end">
                    <a class="btn btn-warning" data-bs-toggle="collapse" href="#collapseExample" role="button"
                        aria-expanded="false" aria-controls="collapseExample">
                        راهنما
                    </a>
                </div>
                <div class="collapse" id="collapseExample">
                    <div class="card card-body">
                        <div class="row border-bottom">
                            <h4>غیر فعال سازی یک روز</h4>
                            <p> برای غیر فعال سازی یک روز، تاریخ شروع را در روز مورد نظر قرار داده و روز گزینه ذخیره
                                کلیک کنید.</p>
                        </div>
                        <div class="row border-bottom mt-1">
                            <h4>غیر فعال سازی چندین روز</h4>
                            <p>برای غیر فعال سازی چندین روز ، میتوانید تاریخ شروع و پایان را انتخاب کنید و روز گزینه
                                ذخیره کلیک کنید.</p>
                        </div>
                        <div class="row border-bottom mt-1">
                            <h4>غیر فعال سازی چندین بازه زمانی</h4>
                            <p>برای غیر فعال سازی چندین بازه زمانی ، میتوانید روز گزینه اضافه کردن کلیک کنید و هر تعداد
                                بازه زمانی که مورد نیاز هست اضافه و روز گزینه ذخیره کلیک کنید.</p>
                        </div>
                    </div>
                </div>
            </div>
            @for ($i = 0; $i < $counter['number']; $i++)
                <div class="row">
                    <div class="col-12 mt-2">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="exampleInputPassword2">
                                        تاریخ شروع
                                    </label>
                                    <input type="text" class="form-control datePicker"
                                        id="absenteenumber-{{ $i }}"
                                        wire:model='absentee.number.{{ $i }}'
                                         data-dateType='start' data-counter="{{ $i + 1 }}" absenteeholder="انتخاب تاریخ شروع">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="exampleInputPassword2">
                                        تاریخ پایان
                                    </label>
                                    <input type="text" class="form-control datePicker"
                                        id="absenteenumber-{{ $i }}"
                                        wire:model='absentee.number.{{ $i }}'
                                        data-dateType='end' data-counter="{{ $i + 1 }}" absenteeholder="انتخاب تاریخ شروع">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
            <div class="row">
                <div class="col-9"></div>
                <div class="col-3 text-end">
                    <button class="btn btn-success" wire:click='storeDay'>ذخیره اطلاعات</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header border-bottom">
            <h3 class="card-title"> انتخاب پزشک</h3>
            <div class="card-options">
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advanceSearch"
                    aria-expanded="false" aria-controls="advanceSearch">
                    جست و جوی پیشرفته
                </button>
                @if (isset($search['id']) || isset($search['mobile']) || isset($search['first_name']) || isset($search['last_name']))
                    <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                        wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="mb-5 collapse {{ $searchPanel }}" id="advanceSearch" wire:ignore>
                <form class="form-horizontal example" autocomplete="off">
                    <div class="row mb-4">
                        <label for="search-id" class="col-md-2 form-label">ایدی</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-id" wire:model="search.id" absenteeholder="ایدی پزشک"
                                type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">نام پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.first_name"
                                absenteeholder="نام " type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">نام خانوادگی پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.last_name"
                                absenteeholder="نام خانوادگی" type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">موبایل پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.mobile"
                                absenteeholder="شماره تماس" type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">نام بخش</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.service"
                                absenteeholder="شماره تماس" type="text">
                        </div>
                    </div>

                    <button class="btn btn-primary" type="button" wire:click="startSearch"
                        wire:loading.class="bg-gray btn-loading disabled">جست و
                        جو
                    </button>
                </form>
            </div>
            <div class="d-flex justify-content-center">

            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            function js() {
                $('.datePicker').each(function() {
                    if (!$(this).data('persianDatepickerInitialized')) {
                        var inp = $(this);
                        $(this).persianDatepicker({
                            initialValue: false,
                            format: 'L',
                            autoClose: true,
                            onSelect: function(unix) {
                                if(inp.data('dateType') == 'start') {
                                    @this.set('dates.' + inp.data('counter')+'.start', inp.val());
                                }else{
                                    @this.set('dates.' + inp.data('counter')+'.end', inp.val());

                                }
                            }
                        });
                        $(this).data('persianDatepickerInitialized', true); // Mark initialization
                    }
                });
            }
            js();
            Livewire.on('jsloader', function() {
                setInterval(() => {
                    js();
                }, 1000);
            })
        });
    </script>
@endpush
