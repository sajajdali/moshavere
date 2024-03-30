<div>
    <!-- PAGE -->
    <div class="page">
        <div>
            <!-- CONTAINER OPEN -->
            <div class="col col-login mx-auto text-center">
                <a href="{{url('index')}}" class="text-center">
                    <img src="{{asset('assets/images/brand/logo.png')}}" class="header-brand-img" alt="">
                </a>
            </div>
            <div class="container-login100">
                <div class="wrap-login100 p-0">
                    <div class="card-body">
                        @if($status == 'showMessage')
                            <div>
                                <div class="alert alert-info" role="alert">
                                    <span class="alert-inner--text"> {{$message}}</span>
                                </div>
                                <a href="https://amiri.selakteb.com/appointment/{{$appointmentUser->id}}"
                                   class="login100-form-btn btn-primary">
                                    بازگشت به اپلیکیشن
                                </a>
                            </div>
                        @elseif($status == 'successful')
                            <div>
                                <div class="alert alert-success" role="alert">
                                    <span class="alert-inner--text"> پرداخت شا با موفقیت انجام شد</span>
                                </div>
                                <a href="https://amiri.selakteb.com/appointment/{{$appointmentUser->id}}"
                                   class="login100-form-btn btn-primary">
                                    بازگشت به اپلیکیشن
                                </a>
                            </div>
                        @elseif($status == 'failed')
                            <div>
                                <div class="alert alert-danger" role="alert">
                                    <span class="alert-inner--text"> پرداخت شا با موفقیت انجام نشد</span>
                                </div>
                                <a href="https://webapp.jesmino.com/appointment/{{$appointmentUser->id}}"
                                   class="login100-form-btn btn-primary">
                                    بازگشت به اپلیکیشن
                                </a>
                            </div>
                        @else

                            <form class="login100-form validate-form ">
                                <div class="text-center mb-4">
                                    <img src="{{asset('default/admin/logo.png')}}" alt="lockscreen image"
                                         class="avatar avatar-xxl brround mb-2">
                                    <h4>پر داخت آنلاین برای نوبت شماره {{$appointmentUser->tracking_code}}</h4>
                                </div>

                                @if($appointmentUser->status == \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_SUCCESSFUL)
                                    <div>
                                        <div class="alert alert-success" role="alert">
                                            <span class="alert-inner--text"> پرداخت قبلا انجام شده است</span>
                                        </div>
                                        <a href="https://amiri.selakteb.com/appointment/{{$appointmentUser->id}}"
                                           class="login100-form-btn btn-primary">
                                            بازگشت به اپلیکیشن
                                        </a>
                                    </div>
                                @else
                                    <div class="container-login100-form-btn">
                                        <button type="button" class="login100-form-btn btn-primary"
                                                wire:loading.class="bg-gray btn-loading disabled"
                                                wire:click="successfulPayment"
                                        >
                                            پرداخت موفقت
                                        </button>
                                    </div>
                                    <div class="container-login100-form-btn">
                                        <button type="button" class="login100-form-btn btn-danger"
                                                wire:loading.class="bg-gray btn-loading disabled"
                                                wire:click="paymentFailed">
                                            پرداخت نا موفق
                                        </button>
                                    </div>
                                @endif


                                <div class="text-center pt-2">
                                    <!-- <span class="txt1">
                                        I Forgot
                                    </span> -->

                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <!-- CONTAINER CLOSED -->
        </div>
    </div>
    <!-- End PAGE -->
</div>
@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('app.recaptcha.site_key') }}"></script>

    <script>
        function resetCaptcha() {
            grecaptcha.ready(function () {
                grecaptcha.execute('{{ config('app.recaptcha.site_key') }}', {action: 'login'}).then(function (token) {
                @this.set('recaptcha', token)
                    ;
                });
            });
        }

        Livewire.on('resetReCaptcha', () => {
            resetCaptcha();
        });

        $(document).ready(function () {
            resetCaptcha();
        });
    </script>
@endpush
