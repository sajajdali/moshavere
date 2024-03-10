<div>
    <div class="card-header border-bottom d-flex justify-content-between">
        <h3> تغییر ساعت حضور برای<span class="text-primary"> یک روز خاص </span></h3>
        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
            <div class="toggle toggle-lg toggle-primary my-1  customCheckbox @if (isset($form['cancel']['day'])) on  @else off @endif"
                data-id="cancel.status" wire:ignore.self data-bs-toggle="collapse" href="#specialDayTimeSetting"
                role="button" aria-expanded="false" aria-controls="specialDayTimeSetting">
                <span></span>
            </div>
        </div>
    </div>
    <div class="collapse @if (isset($form['cancel']['day'])) show @endif " id="specialDayTimeSetting" wire:ignore.self>
        <div class="card-body">
            @error('form.cancel.day')
                <div class="alert alert-danger" role="alert">
                    <p class="text-danger"> !!
                        {{--TODO:: alert Message  --}}
                    </p>
                </div>
            @enderror
            <div class="card card-body @if ($errors->has('form.timeFrame.saturday.*')) border border-danger @endif">
                <div class="row">
                    <div class="col-12 mb-4">
                        تاریخ روزی که مایل هستید ساعتی ، خارج از ساعت برنامه تعیین شده در قسمت بالا را داشته باشد انتخاب کنید
                    </div>
                    <div class="col-md-3 pt-2">
                        <label class="text-primary" for="basic-url">انتخاب تاریخ:</label>
                    </div>
                    <div class="col-md-9">
                        <div class="input-group mb-3">
                            <input type="text" wire:model='form.endAppointment.date'
                                class="form-control @error('form.endAppointment.date') is-invalid @enderror"
                                id="endDatePicker">
                        </div>
                    </div>
                    <div class="d-flex  mt-2">
                        <p class="text-muted"> <strong class="me-1"> نکته!! </strong> نوبت دهی بهت از تاریخ انتخابی غیر
                            فعال شود </p>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <p class="text-muted">تعیین بازه زمانی برای روز</p>
                    <div>
                        <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="اضافه کردن بازه ی زمانی" wire:click="specialDayaddCounter"
                            class="btn btn-info rounded-pill text-center">
                            <span wire:loading.remove wire:target="specialDayaddCounter"> <span
                                    class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                        aria-hidden="true"></i>
                                    <span class="ms-1">اضافه کردن
                                        ساعت</span></span></span>
                            <span wire:loading wire:target="specialDayaddCounter">
                                <div class="spinner-border spinner-border-sm" role="status">
                                </div>
                            </span>
                        </button>
                        @if ($form['specialTimeCounter'] > 1)
                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="حذف کردن بازه ی زمانی" wire:click="specialDayremoveCounter"
                                class="btn btn-danger rounded-pill text-center">
                                <span wire:loading.remove wire:target="specialDayremoveCounter">
                                    <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف بازه
                                        زمانی</span>
                                </span>
                                <span wire:loading wire:target="specialDayremoveCounter">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                    </div>
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
                @for ($i = 0; $i < $form['specialTimeCounter']; $i++)
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label for="input-time" class="form-label">از ساعت</label>
                            <input wire:model='form.timeFrame.saturday.{{ $i }}.start' type="time"
                                wire:ignore.self class="form-control" id="input-time">
                        </div>
                        <div class="col-12 col-md-6"> <label for="input-label" class="form-label">تا
                                ساعت:</label>
                            <input wire:model='form.timeFrame.saturday.{{ $i }}.end' type="time"
                                wire:ignore.self class="form-control" id="input-time">
                        </div>
                    </div>
                @endfor

            </div>
        </div>
    </div>
</div>
