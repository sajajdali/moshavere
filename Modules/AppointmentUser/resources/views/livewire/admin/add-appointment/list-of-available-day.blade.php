<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">شما در حال افزودن نوبت برای بخش <span
                    class="text-primary">{{ $fethData['service']?->title }}</span> و دکتر
                <span class="text-primary">{{ $fethData['doctor']?->full_name }}</span> هستید.
            </h1>
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
                                    <input type="text" class="form-control" placeholder="انتخاب کنید!!"
                                        id="customDateInput" aria-describedby="basic-addon3">
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-end">
                            <div class="col-2">
                                <button class="btn btn-info" wire:click='GotoSpecificDay'>
                                    <span wire:loading.remove wire:tartget='GotoSpecificDay'> مشاهده زمان های
                                        خالی</span>
                                    <span wire:loading wire:tartget='GotoSpecificDay'
                                        class="spinner-border spinner-border-sm" role="status"
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
        @if (isset($fethData['firstTreeAvailableAppointment']) && !empty($fethData['firstTreeAvailableAppointment']))
            @foreach ($fethData['firstTreeAvailableAppointment'] as $dateOfDay => $availableAppointments)
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
                                            نوبت های روز<strong class="text-danger mx-1">
                                                {{ verta($dateOfDay)->format('l') }} </strong>
                                            <span
                                                class="badge rounded-pill bg-light">{{ verta($dateOfDay)->format('y/m/d') }}</span>
                                        </h5>

                                    </div>
                                </div>
                                {{-- each time for day --}}
                                @foreach ($availableAppointments as $index => $eachDay)
                                    @continue($index > 2)
                                    <button type="button"
                                        wire:click="GotoAppointmentList('{{ $dateOfDay }}' ,'{{ $eachDay['from'] }}')"
                                        wire:loading.class='btn-loading bg-gray'
                                        wire:target="GotoAppointmentList('{{ $dateOfDay }}' ,'{{ $eachDay['from'] }}')"
                                        class="badge rounded-pill btn-success-gradient my-1 w-100 text-white hover-zoom"
                                        style="font-size: 15px !important ; cursor: pointer; ">
                                        <div>
                                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                                            <span> &nbsp;<strong>{{ substr($eachDay['from'], 0, -3) }}</strong>
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                                {{-- each time for day --}}
                                <div class="d-flex flex-column align-items-center justify-content-center my-1">
                                    <strong>.</strong>
                                    <strong>.</strong>
                                </div>
                                <span class="badge rounded-pill bg-info-gradient my-1 w-100 text-white hover-zoom"
                                    wire:loading.class='btn-loading bg-gray'
                                    wire:target="GotoAppointmentList('{{ $dateOfDay }}')"
                                    wire:click="GotoAppointmentList('{{ $dateOfDay }}')"
                                    style="font-size: 13px !important ; cursor: pointer;">
                                    <div>
                                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                                        <span>انتخاب ساعت دیگر</span>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>خطا!</strong> <a href="javascript:void(0)" class="alert-link fw-bold">نوبت خالی یافت نشد!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
            </div>
        @endif

        <div class="col-12">
            <div class="alert alert-primary alert-dismissible fade show shadow-lg rounded-3 mb-5" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        برای ثبت ساعت در روز انتخابی از گزینه ی <strong>افزودن نوبت برای تاریخ انتخابی</strong> استفاده
                        کنید
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $('#customDateInput').persianDatepicker({
            initialValue: false,
            format: 'L',
            autoClose: true,
            onSelect: function(unix) {
                @this.set('specificDayDate', $('#customDateInput').val());
            }
        });
    </script>
@endpush
