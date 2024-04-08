<div>
    <div wire:ignore.self class="modal fade" id="RegistrAnAppointment" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    ثبت نوبت
                </div>
                <div class="modal-body">
                    @error('userNotExists')
                        <div class="alert alert-danger" role="alert">
                            <i class="fa fa-exclamation-triangle  fa-xl me-1" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @enderror
                    @if ($step == 1)
                        <div class="row">
                            <label for="inputPassword" class=" col-form-label">ثبت نوبت با شماره همراه </label>
                            <input type="text" placeholder="09123456789" wire:ignore
                                class="form-control @error('form.number') is-invalid @enderror" id="inputPassword"
                                wire:model='form.number'>
                            @error('form.number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row mt-5">
                            <hr style="opacity: 0.5">
                        </div>

                        <div class="row mb-4">
                            <label for="parvande" class=" col-form-label">ثبت نوبت با شماره پرونده </label>
                            <input type="text"
                                class="form-control @error('form.document_number') is-invalid @enderror" id="parvande"
                                wire:ignore wire:model='form.document_number'>
                            @error('form.document_number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @elseif($step == 2)
                        <div class="row my-5">
                            <div class="col-md-4">
                                <a class="text-primary" data-bs-toggle="collapse" href="#userDataCollaps" role="button"
                                    aria-expanded="false" aria-controls="userDataCollaps">
                                    <span class="text-primary">
                                        <i class="fa fa-user text-primary me-1" aria-hidden="true"></i>
                                        <strong>مشخصات کاربری</strong>
                                    </span>
                                </a>
                            </div>
                            <div class="col-md-8">
                                <hr>
                            </div>
                            <div class="row collapse @if (isset($form['first_name']) && $form['last_name']) hide @else show @endif mt-2"
                                id="userDataCollaps">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="firs_name_addApp" class="form-label">نام کاربر</label>
                                        <input type="email"
                                            class="form-control @error('form.first_name') is-invalid @enderror "
                                            id="firs_name_addApp" wire:model='form.first_name'>
                                        @error('form.first_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name_addApp" class="form-label">نام خانوادگی کاربر</label>
                                        <input type="email"
                                            class="form-control  @error('form.last_name') is-invalid @enderror "
                                            id="last_name_addApp" wire:model='form.last_name'>
                                        @error('form.last_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12 row mt-2 mb-4">
                                    <label for="document_number_registeration" class="col-sm-3 col-form-label">شماره
                                        پرونده</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="document_number_registeration"
                                            wire:model='form.document_number'>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-md-4 mb-4">
                                <a class="text-primary" data-bs-toggle="collapse" href="#timingCollaps" role="button"
                                    aria-expanded="false" aria-controls="timingCollaps">
                                    <i class="fa fa-clock-o me-1 @error('form.time.from') text-danger @else text-primary @enderror"
                                        aria-hidden="true"></i>
                                    <strong
                                        class="@error('form.time.from') text-danger @else text-primary @enderror">زمان
                                        نوبت</strong>
                                </a>
                            </div>
                            <div class="col-md-8">
                                <hr>
                            </div>
                            <div class="collapse @if (!isset($form['time']['from'])) show @endif  @error('form.time.from')
                            show
                            @enderror row"
                                id="timingCollaps">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <input type="time" class="form-control" id="firs_name_addApp"
                                            wire:model='form.time.from' placeholder="ساعت شروع">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <input type="time" class="form-control" id="last_name_addApp"
                                            wire:model='form.time.until' placeholder="ساعت پایان">
                                    </div>
                                </div>
                            </div>
                            @error('form.time.from')
                                <div class="col-12 mt-2">
                                    <span class="text-danger">{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                        <div class="row my-5">
                            <div class="col-md-4">
                                <a class="text-primary" data-bs-toggle="collapse" href="#detialCollaps"
                                    role="button" aria-expanded="false" aria-controls="detialCollaps">
                                    <i class="fa fa-info-circle text-primary me-1" aria-hidden="true"></i>
                                    <strong>جزئیات نوبت</strong>
                                </a>
                            </div>
                            <div class="col-md-8">
                                <hr>
                            </div>
                            <div class="collapse row" id="detialCollaps">
                                <div class="col-md-12">
                                    <label for="exampleFormControlTextarea1" class="form-label">توضیحات</label>
                                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" wire:model='form.description'></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-md-4">
                                <a class="text-primary" data-bs-toggle="collapse" href="#apptypeCollaps"
                                    role="button" aria-expanded="false" aria-controls="apptypeCollaps">
                                    <i class="fa fa-random me-1 text-primary" aria-hidden="true"></i>
                                    <strong>نوع نوبت</strong>
                                </a>
                            </div>
                            <div class="col-md-8">
                                <hr>
                            </div>
                            <div class="row collapse show mt-2" id="apptypeCollaps">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" checked type="radio"
                                            wire:model='form.appType' value="main_app" name="appTypeRAdio"
                                            id="flexRadioDefault1">
                                        <label class="form-check-label" for="flexRadioDefault1">
                                            نوبت اصلی
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="appTypeRAdio"
                                            wire:model='form.appType' value="in_between" id="flexRadioDefault2">
                                        <label class="form-check-label" for="flexRadioDefault2">
                                            بین مریض
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-md-4">
                                <a class="text-primary" data-bs-toggle="collapse" href="#smsStatusCollaps"
                                    role="button" aria-expanded="false" aria-controls="smsStatusCollaps">
                                    <i class="fa fa-comments-o me-1 text-primary" aria-hidden="true"></i>
                                    <strong>ارسال پیامک</strong>
                                </a>
                            </div>
                            <div class="col-md-8">
                                <hr>
                            </div>
                            <div class="collapse row show mt-2" id="smsStatusCollaps">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" checked type="radio"
                                            wire:model='form.smsType' value="send" name="smsStatusType"
                                            id="smsStatusType1">
                                        <label class="form-check-label" for="smsStatusType1">
                                            پیامک ارسال شود
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="smsStatusType"
                                            wire:model='form.smsType' value="unsend" id="smsStatusType2">
                                        <label class="form-check-label" for="smsStatusType2">
                                            پیامک ارسال نشود
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($step == 3)
                        <div class="row my-5">
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                در ساعت انتخابی شما ، یک
                                نوبت ثبت شده است ، آیا مایل به ثبت نوبت هستید؟
                            </div>

                        </div>
                    @endif
                    <div class="modal-footer d-flex justify-content-between">
                        <div>
                            @if ($step > 1)
                                <button type="button" class="btn btn-gray" wire:click='privousStep'
                                    wire:loading.class='btn-loading bg-gray'>
                                    <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                            @endif
                        </div>
                        <div>
                            <button type="button" class="btn btn-success" wire:click='numberSet'
                                wire:loading.class='btn-loading bg-gray'>
                                @if ($step == 1)
                                    ادامه
                                @elseif($step == 2)
                                    ثبت
                                @elseif($step == 3)
                                    بله ثبت شود
                                @endif
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click='dismisModal'
                                data-bs-dismiss="modal">بیخیال</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
