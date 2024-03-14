<div>
    <div class="card">
        <div class="card-body">
            <div class="card-title d-flex justify-content-between">
                <h3>عدم حضور</h3>
                <div>
                    <button type="button" wire:click="addCounter('number')"
                        class="btn btn-info rounded-pill text-center my-3 my-sm-0">
                        <span wire:loading.remove wire:target="addCounter('number')"> <span
                                class="d-flex align-items-center"><i class="fa fa-plus fa-lg me-1" aria-hidden="true"></i>
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
                        <div class="row border-bottom mb-3">
                            <h4>غیر فعال سازی یک روز</h4>
                            <p> برای غیر فعال سازی یک روز، تاریخ شروع را در روز مورد نظر قرار داده و روز گزینه ذخیره
                                کلیک کنید.</p>
                        </div>
                        <div class="row border-bottom mt-1 mb-3">
                            <h4>غیر فعال سازی چندین روز</h4>
                            <p>برای غیر فعال سازی چندین روز ، میتوانید تاریخ شروع و پایان را انتخاب کنید و روز گزینه
                                ذخیره کلیک کنید.</p>
                        </div>
                        <div class="row border-bottom mt-1 mb-3">
                            <h4>غیر فعال سازی چندین بازه زمانی</h4>
                            <p>برای غیر فعال سازی چندین بازه زمانی ، میتوانید روز گزینه اضافه کردن کلیک کنید و هر
                                تعداد
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
                                        wire:model='absentee.number.{{ $i }}' data-dateType='start'
                                        data-counter="{{ $i + 1 }}" absenteeholder="انتخاب تاریخ شروع">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="exampleInputPassword2">
                                        تاریخ پایان
                                    </label>
                                    <input type="text" class="form-control datePicker"
                                        id="absenteenumber-{{ $i }}"
                                        wire:model='absentee.number.{{ $i }}' data-dateType='end'
                                        data-counter="{{ $i + 1 }}" absenteeholder="انتخاب تاریخ شروع">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
            <div class="row">
                <div class="d-flex justify-content-between">
                    <button class="btn btn-light" type="button" wire:click='prevStep'>
                        <span wire:loading.remove wire:target='prevStep'>
                            <i class="fa fa-arrow-right fa-2x" aria-hidden="true"></i>
                        </span>
                        <span wire:loading wire:target='prevStep' class="spinner-border spinner-border-sm"
                            role="status" aria-hidden="true"></span>
                    </button>
                    <button class="btn btn-success" type="button" wire:click='lunchconfirmModal'>
                        <span wire:loading.remove wire:target='lunchconfirmModal'>ثبت ساعت</span>
                        <span wire:loading wire:target='lunchconfirmModal' class="spinner-border spinner-border-sm"
                            role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
