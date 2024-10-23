<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">
                <span>تنظیمات یادآوری ها</span>
            </h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">
                            <strong>انتخاب بخش</strong>
                        </label>
                        <select class="form-control select2-show-search form-select" id="serviceSelet" wire:ignore.self
                            wire:key='{{ time() }}' data-placeholder="انتخاب کنید..">
                            <option value="null">همه بخش ها</option>
                            @foreach ($fetchData['services'] as $key => $service)
                                <option @if (isset($this->form['service']) && $this->form['service'] == $service->id) selected @endif value="{{ $service->id }}">
                                    {{ $service->title }}</option>
                            @endforeach
                        </select>
                        @error('form.service')
                            <div class="text-danger">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                {{-- line seperator --}}
                <div class="col-12 col-md-3 mt-md-5 mb-md-3">
                    <div
                        class="d-flex align-items-center @error('form.specificDoctors') text-danger @else text-primary @enderror">
                        <i class="fa fa-user-md fa-2x mb-1 me-2" aria-hidden="true"></i>
                        <h4 class="mt-1">انتخاب پزشک</h4>
                    </div>
                </div>
                <div class="col-12 col-md-9 mt-md-5 mb-md-3">
                    <hr class="@error('form.specificDoctors') bg-danger @enderror">
                </div>
                {{-- line seperator --}}

                {{-- doctor --}}
                <div class="col-12 mt-4">
                    <div class="row" wire:click='updateSpecificPRoperties'>
                        <div class="col-md-6">
                            <label class="rdiobox docradio" for="rdio-primary-unchecked">
                                <input name="rdio-secondary" type="radio" class="radio-primary"
                                    wire:model='form.doctors' value="all" id="rdio-primary-unchecked">
                                <span>تمام پزشکان</span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="rdiobox docradio" for="rdio-secondary-unchecked">
                                <input name="rdio-secondary" type="radio" class="radio-primary"
                                    wire:model='form.doctors' value="specificDoctor" id="rdio-secondary-unchecked">
                                <span>یک پزشک خاص</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 d-none" id="selectDoctorSelectBox" wire:ignore.self>
                    <div class="form-group">
                        <label class="form-label">انتخاب پزشک</label>
                        <select multiple class="form-control select2-show-search form-select" id="speciificDocSelect2"
                            wire:ignore.self data-placeholder="انتخاب کنید...">
                            <option label="انتخاب کنید..."></option>
                            @if (isset($fetchData['doctors']))
                                @foreach ($fetchData['doctors'] as $doctor)
                                    <option @if (isset($form['specificDoctors']) && in_array($doctor->id, $form['specificDoctors'])) selected @endif
                                        value="{{ $doctor->id }}">
                                        {{ $doctor->fullname }}</option>
                                @endforeach
                            @else
                                <option value="null" disabled>لطفا ابتدا پزشک به سیستم اضافه کنید!!</option>
                            @endif
                        </select>
                        @error('form.specificDoctors')
                            <div class="text-danger">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                {{-- line seperator --}}
                <div class="col-12 col-md-3 mt-5  mt-md-5 mb-md-3">
                    <div
                        class="d-flex align-items-center @if ($errors->hasAny('form.callAnnouncment', 'form.smsTemplateName', 'form.notificationText')) text-danger @else text-primary @endif">
                        <i class="fa fa-flag  fa-2x mb-1 me-2" aria-hidden="true"></i>
                        <h4 class="mt-1">انتخاب نوع ارسال</h4>
                    </div>
                </div>
                <div class="col-12 col-md-9 mt-md-5  mt-md-5 mb-md-3">
                    <hr class="@if ($errors->hasAny('form.callAnnouncment', 'form.smsTemplateName', 'form.notificationText')) bg-danger @endif">
                </div>
                {{-- send Type --}}
                <div class="col-12 mt-4">
                    <div class="row">
                        @foreach (Modules\Reminder\Enum\ReminderStatusEnum::cases() as $key => $value)
                            <div class="col-md-4">
                                <label class="rdiobox" for="sendType{{ $value->getWireModelName() }}">
                                    <input name="sendNotifType" type="radio" class="radio-secondary sendType"
                                        wire:model='form.sendType' value="{{ $value }}"
                                        value="{{ $value->getWireModelName() }}"
                                        id="sendType{{ $value->getWireModelName() }}">
                                    <span>{{ $value->getName() }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 mt-4" id="smsTemplateDiv" wire:ignore.self>
                    <label for="smsTemplate" class="form-label">نام قالب پیامکی</label>
                    <input wire:model='form.smsTemplateName'
                        class="form-control @error('form.smsTemplateName') is-invalid @enderror" id="smsTemplate"
                        placeholder="نام قالب پیامکی که در پنل پیامکی ثبت کردید" type="text">
                    @error('form.smsTemplateName')
                        <div class="text-danger">
                            <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-12 d-none" id="notificationTemplateDiv" wire:ignore.self>
                    <label for="validationTextarea" class="form-label">متن اعلان</label>
                    <textarea wire:model='form.notificationText' class="form-control" id="validationTextarea"
                        placeholder="متن اعلان را وارد کنید"></textarea>
                    <small class="text-gray">برای استفاده از متن متغیر، از %param1% استفاده کنید!</small>
                    @error('form.notificationText')
                        <div class="text-danger">
                            <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-12 mt-4 d-none" id="callAnnouncmentDiv" wire:ignore.self>
                    <label for="callTemp" class="form-label">نام قالب </label>
                    <input wire:model='form.callAnnouncment' class="form-control" id="callTemp"
                        placeholder="عنوان پیام تلفنی " type="text">
                    @error('form.callAnnouncment')
                        <div class="text-danger">
                            <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
                <div id="paramDiv" class="col-12 row">
                    <div class="col-12 col-md-3 mt-5  mt-md-5 mb-md-3">
                        <div class="d-flex align-items-center text-primary">
                            <i class="fa fa-random  fa-2x mb-1 me-2" aria-hidden="true"></i>
                            <h4 class="mt-1">انتخاب پارامتر ها</h4>
                        </div>
                    </div>
                    <div class="col-12 col-md-9 mt-md-5  mt-md-5 mb-md-3">
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">انتخاب پارامتر</label>
                            <div class="row">
                                <div class="col-1">
                                    <span class="badge bg-secondary rounded-phill mt-md-1">1</span>
                                </div>
                                <div class="col-11">
                                    <select class="form-control  form-select" wire:model='form.parametr.0'
                                        data-id="1" wire:ignore.self data-placeholder="انتخاب کنید...">
                                        <option label="انتخاب کنید..."></option>
                                        @foreach (Modules\Reminder\Enum\ReminderParametersEnum::cases() as $parameter)
                                            <option value="{{ $parameter }}">{{ $parameter->getName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    @for ($i = 0; $i < $fetchData['parametrCounter']; $i++)
                        <div class="row d-flex flex-column flex-md-row align-item-center mt-2">
                            <div class="col-md-1 mb-2 mb-md-0">
                                <span class="badge bg-secondary rounded-phill mt-md-1">{{ $i + 2 }}</span>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <select class="form-control form-select "
                                        wire:model='form.parametr.{{ $i + 1 }}' data-id="{{ $i }}"
                                        wire:ignore.self data-placeholder="انتخاب کنید...">
                                        <option label="انتخاب کنید..."></option>
                                        @foreach (Modules\Reminder\Enum\ReminderParametersEnum::cases() as $parameter)
                                            <option @if (isset($this->form['parametr']) && in_array($parameter->value, $this->form['parametr'])) selected @endif
                                                value="{{ $parameter->value }}">{{ $parameter->getName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2  mb-3 mb-md-0 text-end">
                                <button class="btn btn-danger " wire:loading.class='btn-loading btn-gray'
                                    wire:target='removeParam({{ $i }})'
                                    wire:click='removeParam({{ $i }})'>حذف
                                    پارامتر</button>
                            </div>
                        </div>
                    @endfor
                    <div class="col-md-12">
                        <button wire:click='addMoreParam' wire:loading.class='btn-loading bg-gray'
                            class="btn btn-light">افزودن پارامتر</button>
                    </div>
                </div>
                {{-- line seperator --}}
                <div class="col-12 col-md-3 mt-5  mt-md-5 mb-md-3">
                    <div
                        class="d-flex align-items-center   @error('form.timeSend') text-danger @else text-primary @enderror">
                        <i class="fa fa-clock-o fa-2x mb-1 me-2" aria-hidden="true"></i>
                        <h4 class="mt-1">زمان ارسال</h4>
                    </div>
                </div>
                <div class="col-12 col-md-9 mt-md-5  mt-md-5 mb-md-3">
                    <hr class="@error('form.timeSend') bg-danger @enderror">
                </div>
                {{-- line seperator --}}

                <div class="col-md-12 col-lg-6 mt-4">
                    <p>چند روز قبل از فرا رسیدن زمان نوبت</p>
                    <div class="col-md-12  ms-3 mt-4 mb-3">
                        <label class="rdiobox mb-2 dayInput" for="rdio-unchecked">
                            <input name="sendDay" wire:model='form.sendDate' value="sameDay" type="radio"
                                id="rdio-unchecked">
                            <span>در روز نوبت</span></label>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group dayInput">
                            <div class="input-group">
                                <div class="input-group-text bg-primary-transparent text-primary">
                                    <label class="rdiobox mb-0"><input name="sendDay" wire:model='form.sendDate'
                                            value="selectedDate"
                                            type="radio"id="numberOfBeforeVisitDate_radio"><span></span></label>
                                </div>
                                <input class="form-control dayInput" id="numberOfBeforeVisitDate_input" wire:ignore
                                    @if (!isset($form['send_at_specific_date']) && $form['sendDate'] == 'sameDay') disabled @else value="@if (isset($form['send_at_specific_date'])) {{ $form['send_at_specific_date'] }} @endif"
                                    @endif placeholder="چند روز قبل از فرا رسیدن روز نوبت"
                                type="text">
                                @error('form.specificDay')
                                    <div class="text-danger">
                                        <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mb-3 mt-4">
                    <label for="datetimepicker2">چند ساعت قبل از نوبت ارسال شود</label>
                    <div class="input-group col-md-6 ps-0">
                        <input class="form-control @error('form.timeSend') is-invalid @enderror"
                            @if (isset($form['timeSend']) && !empty($form['timeSend'])) value="{{ $form['timeSend'] }}" @endif
                            id="datetimepicker2" wire:model='form.timeSend' type="number">
                    </div>
                    <small class="text-gray">برای انتخاب روی ساعت کلیک کنید</small>
                    @error('form.timeSend')
                        <div class="text-danger">
                            <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-12 mt-3">
                    <div class="main-toggle-group d-flex align-items-center ms-0">
                        <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if( isset($form['active']) && $form['active'] == true) on @else off @endif"
                            data-id="visitType.inPerson">
                            <span></span>
                        </div>
                        <div class="ms-2">
                            <p class="text-muted m-0">فعال بودن یادآور</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 opacity-50">
                    <hr>
                </div>
                <div class="col-md-12 text-end">
                    <button wire:click='storeReminder' wire:loading.class='btn-loading bg-gray'
                        wire:target='storeReminder' class="btn btn-success"><strong>ذخیره ی یادآور</strong></button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2-show-search').select2();
            var doctors = "{{ $form['doctors'] }}";
            if (doctors == 'specificDoctor') {
                $('#selectDoctorSelectBox').removeClass('d-none');
            }
            Livewire.on('loadjs', function() {
                setTimeout(() => {
                    $('.select2-show-search').select2();
                }, 200);
            });
            $('#numberOfBeforeVisitDate_input').change(function() {
                @this.set('form.send_at_specific_date', $(this).val());
            });
            $('.customCheckbox').on('click', function() {
                if ($(this).hasClass('on')) {
                    @this.set('form.active', 1);
                } else {
                    @this.set('form.active', 0);
                }
            });
            $('.docradio').on('click', function(e) {
                if ($('#rdio-secondary-unchecked').is(':checked')) {
                    $('#selectDoctorSelectBox').fadeIn('d-none');
                    $('#selectDoctorSelectBox').removeClass('d-none');
                } else {
                    $('#selectDoctorSelectBox').fadeOut('d-none');
                    $('#selectDoctorSelectBox').addClass('d-none');
                }
            });
            $('.sendType').on('change', function(e) {
                var val = $(this).val();
                changeSendTypeStatus(val);
            });
            if ({{ isset($form['sendType']) }}) {
                val = "{{ $form['sendType'] }}";
                changeSendTypeStatus(val);
            };

            function changeSendTypeStatus(val) {
                if (val == '1') {
                    $('#smsTemplateDiv').fadeIn().removeClass('d-none');
                    $('#notificationTemplateDiv').fadeOut().addClass('d-none');
                    $('#callAnnouncmentDiv').fadeOut().addClass('d-none');
                    $('#paramDiv').fadeIn().removeClass('d-none');
                } else if (val == '2') {
                    $('#notificationTemplateDiv').fadeIn().removeClass('d-none');
                    $('#smsTemplateDiv').fadeOut().addClass('d-none');
                    $('#callAnnouncmentDiv').fadeOut().addClass('d-none');
                    $('#paramDiv').fadeIn().removeClass('d-none');
                } else {
                    $('#smsTemplateDiv, #notificationTemplateDiv').addClass('d-none');
                    $('#callAnnouncmentDiv').fadeIn().removeClass('d-none');
                    $('#paramDiv').fadeOut().addClass('d-none');
                }
            }
            $('.dayInput').on('click', function() {
                if ($('#numberOfBeforeVisitDate_radio').prop('checked')) {
                    $('#numberOfBeforeVisitDate_input').prop('disabled', false);
                } else {
                    $('#numberOfBeforeVisitDate_input').prop('disabled', true);
                }
            });
            $('#serviceSelet').on('change', function() {
                @this.set('form.service', $(this).val());
            });
            $('#speciificDocSelect2').on('change', function() {
                @this.set('form.specificDoctors', $(this).val());
            });
        });
    </script>
@endpush
