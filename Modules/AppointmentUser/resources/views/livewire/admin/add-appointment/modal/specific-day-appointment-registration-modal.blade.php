<div>
    <div wire:ignore.self class="modal fade" id="RegistrAnAppointment" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    ثبت نوبت
                </div>
                <div class="modal-body">
                    @if (isset($message))
                        <div class="alert alert-success" role="alert">
                            <i class="fa fa-check-square-o fa-xl me-1" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @endif
                    @if ($step == 1)
                        <div class="row">
                            <label for="inputPassword" class=" col-form-label">ثبت نوبت با شماره همراه </label>
                            <input type="text" placeholder="0912******"
                                class="form-control @error('setProp.number') is-invalid @enderror" id="inputPassword"
                                wire:model='setProp.number'>
                            @error('setProp.number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row mt-5">
                            <hr style="opacity: 0.5">
                        </div>

                        <div class="row mb-4">
                            <label for="parvande" class=" col-form-label">ثبت نوبت با شماره پرونده </label>
                            <input type="text" placeholder="1234"
                                class="form-control @error('setProp.document') is-invalid @enderror" id="parvande"
                                wire:model='setProp.document'>
                            @error('setProp.document')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @elseif($step == 2)
                        <div class="row my-5">
                            <div class="col-md-4">
                                <span class="text-primary">
                                    <i class="fa fa-user text-primary me-1" aria-hidden="true"></i>
                                    <strong>مشخصات کاربری</strong>
                                </span>
                            </div>
                            <div class="col-md-8">
                                <hr>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="firs_name_addApp" class="form-label">نام کاربر</label>
                                    <input type="email"
                                        class="form-control @error('setProp.first_name') is-invalid @enderror "
                                        id="firs_name_addApp" wire:model='setProp.first_name'>
                                    @error('setProp.first_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name_addApp" class="form-label">نام خانوادگی کاربر</label>
                                    <input type="email"
                                        class="form-control  @error('setProp.last_name') is-invalid @enderror "
                                        id="last_name_addApp" wire:model='setProp.last_name'>
                                    @error('setProp.last_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12 row mt-2 mb-4">
                                <label for="inputPassword" class="col-sm-3 col-form-label">شماره پرونده</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" id="inputPassword"
                                        wire:model='setProp.docNumber'>
                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-md-4 mb-4">
                                <a class="text-primary" data-bs-toggle="collapse" href="#timingCollaps" role="button"
                                    aria-expanded="false" aria-controls="timingCollaps">
                                    <i class="fa fa-clock-o text-primary me-1" aria-hidden="true"></i>
                                    <strong>زمان نوبت</strong>
                                </a>
                            </div>
                            <div class="col-md-8">
                                <hr>
                            </div>
                            <div class="collapse row" id="timingCollaps">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <input type="email" class="form-control" id="firs_name_addApp"
                                            placeholder="ساعت شروع">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <input type="email" class="form-control" id="last_name_addApp"
                                            placeholder="ساعت پایان">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row my-5">
                            <div class="col-md-4">
                                <a class="text-primary" data-bs-toggle="collapse" href="#detialCollaps" role="button"
                                    aria-expanded="false" aria-controls="detialCollaps">
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
                                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" wire:model='setProp.description'></textarea>
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
                                        <input class="form-check-input" checked type="radio" value="true"
                                            wire:model='setProp.appType' name="appTypeRAdio" id="flexRadioDefault1">
                                        <label class="form-check-label" for="flexRadioDefault1">
                                            نوبت اصلی
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="appTypeRAdio"
                                            value="fasle" wire:model='setProp.appType' id="flexRadioDefault2">
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
                                            wire:model='setProp.smsType' value="true" name="smsStatusType"
                                            id="smsStatusType1">
                                        <label class="form-check-label" for="smsStatusType1">
                                            پیامک ارسال شود
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="smsStatusType"
                                            wire:model='setProp.smsType' value="false" id="smsStatusType2">
                                        <label class="form-check-label" for="smsStatusType2">
                                            پیامک ارسال نشود
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" wire:click='numberSet'>ادامه</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بیخیال</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
