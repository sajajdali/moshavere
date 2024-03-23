<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">شما در حال افزودن نوبت برای بخش "" و پزشک "" هستید </h1>
        </div>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#customDate"
                aria-expanded="false" aria-controls="customDate">افزودن نوبت برای تاریخ انتخابی</button>
    </div>
    <div class="collapse" id="customDate" wire:ignore.self>
        <div class="row">
            <div class="card custom-card client-card border">
                <div class="card-body">
                    <div class="row mt-4">
                        <div class="row mb-2">
                            <div class="col-md-2 pt-2">
                                <label class="text-primary">انتخاب تاریخ:</label>
                            </div>
                            <div class="col-md-10">
                                <div class="input-group mb-3">
                                    <input type="text"  class="form-control" placeholder="انتخاب کنید!!"
                                           id="customDateInput" aria-describedby="basic-addon3">
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-end">
                            <div class="col-2">
                                <button class="btn btn-info" wire:click='GotoSpecificDay'>
                                    <span wire:loading.remove wire:tartget='GotoSpecificDay'> مشاهده زمان های خالی</span>
                                    <span wire:loading wire:tartget='GotoSpecificDay' class="spinner-border spinner-border-sm" role="status"
                                          aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- each day card --}}
        <div class="col-md-4 col-sm-12">
            <div class="card custom-card client-card border">
                <div class="card-body">
                    <div class="client-card-top">
                        <div class="d-flex">
                            <div class="rounded-circle align-self-start mb-0">
                            </div>
                            <div class="flex-fill my-1">
                                <h5 class="d-flex align-items-center">
                                    <i class="fa fa-calendar-check-o me-1 mb-1" aria-hidden="true"></i>
                                    نوبت های روز<strong class="text-danger mx-1"> شنبه </strong>
                                    <span class="badge rounded-pill bg-light">02/01/13</span>
                                </h5>

                            </div>
                        </div>
                        {{-- each time for day --}}
                        <span class="badge rounded-pill bg-success-gradient my-1 w-100 text-white hover-zoom"
                              style="font-size: 14px !important ; cursor: pointer;">
                            {{-- TODO::change the Date to dynamic property that is the date of the day --}}
                            <div   wire:click='addAppointment({{'Date'}})'>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span>ساعت 10:10 تا 10:15</span>
                            </div>
                            {{-- <span wire:loading  wire:click='addAppointment({{'Date'}})' class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> --}}
                        </span>
                        {{-- each time for day --}}

                        <span class="badge rounded-pill bg-success-gradient my-1 w-100 text-white hover-zoom"
                              style="font-size: 14px !important ; cursor: pointer;">
                            <div    wire:click='addAppointment({{'Date'}})'>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span>ساعت 10:15 تا 10:20</span>
                            </div>
                            {{-- <span wire:loading   wire:click='addAppointment({{'Date'}})' class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> --}}
                        </span>
                        <span class="badge rounded-pill bg-success-gradient my-1 w-100 text-white hover-zoom"
                              style="font-size: 14px !important ; cursor: pointer;">
                            <div    wire:click='addAppointment({{'Date'}})'>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span>ساعت 10:30 تا 10:35</span>
                            </div>
                            {{-- <span wire:loading   wire:click='addAppointment({{'Date'}})' class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> --}}
                        </span>

                    </div>
                </div>
            </div>
        </div>
        {{-- each day card --}}
        {{-- each day card --}}
        <div class="col-md-4 col-sm-12">
            <div class="card custom-card client-card border">
                <div class="card-body">
                    <div class="client-card-top">
                        <div class="d-flex">
                            <div class="rounded-circle align-self-start mb-0">
                            </div>
                            <div class="flex-fill my-1">
                                <h5 class="d-flex align-items-center">
                                    <i class="fa fa-calendar-check-o me-1 mb-1" aria-hidden="true"></i>
                                    نوبت های روز<strong class="text-danger mx-1"> یکشنبه </strong>
                                    <span class="badge rounded-pill bg-light">02/01/14</span>
                                </h5>

                            </div>
                        </div>
                        {{-- each time for day --}}
                        <span class="badge rounded-pill bg-success-gradient my-1 w-100 text-white hover-zoom"
                              style="font-size: 14px !important ; cursor: pointer;">
                            <div    wire:click='addAppointment({{'Date'}})'>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span>ساعت 10:10 تا 10:15</span>
                            </div>
                            {{-- <span wire:loading   wire:click='addAppointment({{'Date'}})' class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> --}}
                        </span>
                        {{-- each time for day --}}

                        <span class="badge rounded-pill bg-success-gradient my-1 w-100 text-white hover-zoom"
                              style="font-size: 14px !important ; cursor: pointer;">
                            <div    wire:click='addAppointment({{'Date'}})'>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span>ساعت 10:15 تا 10:20</span>
                            </div>
                            {{-- <span wire:loading   wire:click='addAppointment({{'Date'}})' class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> --}}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        {{-- each day card --}}
        {{-- each day card --}}
        <div class="col-md-4 col-sm-12">
            <div class="card custom-card client-card border">
                <div class="card-body">
                    <div class="client-card-top">
                        <div class="d-flex">
                            <div class="rounded-circle align-self-start mb-0">
                            </div>
                            <div class="flex-fill my-1">
                                <h5 class="d-flex align-items-center">
                                    <i class="fa fa-calendar-check-o me-1 mb-1" aria-hidden="true"></i>
                                    نوبت های روز<strong class="text-danger mx-1"> دوشنبه </strong>
                                    <span class="badge rounded-pill bg-light">02/01/15</span>
                                </h5>

                            </div>
                        </div>
                        {{-- each time for day --}}
                        <span class="badge rounded-pill bg-success-gradient my-1 w-100 text-white hover-zoom"
                              style="font-size: 14px !important ; cursor: pointer;">
                            <div    wire:click='addAppointment({{'Date'}})'>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span>ساعت 10:10 تا 10:15</span>
                            </div>
                            {{-- <span wire:loading    wire:click='addAppointment({{'Date'}})' class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> --}}
                        </span>
                        {{-- each time for day --}}

                        <span class="badge rounded-pill bg-success-gradient my-1 w-100 text-white hover-zoom"
                              style="font-size: 14px !important ; cursor: pointer;">
                            <div    wire:click='addAppointment({{'Date'}})'>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span>ساعت 10:15 تا 10:20</span>
                            </div>
                            {{-- <span wire:loading   wire:click='addAppointment({{'Date'}})' class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> --}}
                        </span>
                        <span class="badge rounded-pill bg-success-gradient my-1 w-100 text-white hover-zoom"
                              style="font-size: 14px !important ; cursor: pointer;">
                            <div    wire:click='addAppointment({{'Date'}})'>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span>ساعت 10:30 تا 10:35</span>
                            </div>
                            {{-- <span wire:loading   wire:click='addAppointment({{'Date'}})' class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span> --}}
                        </span>

                    </div>
                </div>
            </div>
        </div>
        {{-- each day card --}}

    </div>
</div>

@push('scripts')
    <script>
        $('#customDateInput').persianDatepicker({
            initialValue: false,
            format: 'L',
            autoClose: true,
            onSelect: function(unix) {
            @this.set('specificDayDate',$('#customDateInput').val());
            }
        });
    </script>
@endpush
