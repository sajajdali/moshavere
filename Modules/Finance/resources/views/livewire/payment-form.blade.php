<div>
    <div class="modal fade" id="financePaymentModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content fin-page">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-credit-card me-1" aria-hidden="true"></i>
                        {{ $paymentId ? 'ویرایش پرداخت' : 'ثبت پرداخت دستی' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        {{-- patient --}}
                        <div class="mb-4">
                            <label class="form-label">بیمار <span class="text-danger">*</span></label>
                            @if ($patient)
                                <div class="d-flex align-items-center justify-content-between border rounded-phil p-2 px-3" style="background: var(--fin-soft)">
                                    <span><i class="fa fa-user-o me-2 text-muted"></i><strong>{{ $patient->fullName ?: $patient->mobile }}</strong>
                                        <span class="text-muted ms-2" dir="ltr">{{ $patient->mobile }}</span></span>
                                    @unless ($patientLocked)
                                        <button type="button" class="btn btn-sm btn-light" wire:click="clearPatient">تغییر</button>
                                    @endunless
                                </div>
                            @else
                                <input type="text" class="form-control" wire:model.live.debounce.400ms="patientSearch"
                                    placeholder="نام یا موبایل بیمار را بنویسید..." autocomplete="off">
                                @if ($results->isNotEmpty())
                                    <div class="list-group mt-1">
                                        @foreach ($results as $result)
                                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between"
                                                wire:click="selectPatient({{ $result->id }})" wire:key="patient-result-{{ $result->id }}">
                                                <span>{{ $result->fullName ?: 'بدون نام' }}</span>
                                                <span class="text-muted" dir="ltr">{{ $result->mobile }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif (mb_strlen(trim($patientSearch)) >= 2)
                                    <div class="fin-amount-hint">بیماری پیدا نشد.</div>
                                @endif
                            @endif
                            @error('userId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">نوع</label>
                                <select class="form-select" wire:model="form.type">
                                    @foreach ($types as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">مبلغ (تومان) <span class="text-danger">*</span></label>
                                {{-- the digits are grouped by three while typing; the component removes the commas --}}
                                <input type="text" inputmode="numeric" dir="ltr" style="text-align: left" class="form-control @error('form.amount') is-invalid @enderror"
                                    x-data="{
                                        format(el) {
                                            const persian = '۰۱۲۳۴۵۶۷۸۹', arabic = '٠١٢٣٤٥٦٧٨٩';
                                            const toLatin = (value) => value
                                                .replace(/[۰-۹]/g, (c) => persian.indexOf(c))
                                                .replace(/[٠-٩]/g, (c) => arabic.indexOf(c));
                                            // how many digits are before the cursor: the cursor stays after the same digit
                                            const digitsBefore = toLatin(el.value.slice(0, el.selectionStart ?? el.value.length)).replace(/\D/g, '').length;
                                            const digits = toLatin(el.value).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
                                            el.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                            let position = 0, seen = 0;
                                            while (position < el.value.length && seen < digitsBefore) {
                                                if (el.value[position] !== ',') seen++;
                                                position++;
                                            }
                                            el.setSelectionRange(position, position);
                                        }
                                    }"
                                    x-on:input.capture="format($el)"
                                    wire:model.live.debounce.300ms="form.amount" placeholder="مثال: 500,000" autocomplete="off">
                                @error('form.amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">روش پرداخت</label>
                                <div class="fin-method-choices">
                                    @foreach ($methods as $value => $label)
                                        @php $icon = \Modules\Finance\Enum\FinancePaymentMethod::from($value)->icon(); @endphp
                                        <div class="fin-method-choice {{ ($form['method'] ?? '') === $value ? 'is-active' : '' }}"
                                            wire:click="$set('form.method', '{{ $value }}')" wire:key="method-{{ $value }}">
                                            <i class="fa {{ $icon }}" aria-hidden="true"></i>{{ $label }}
                                        </div>
                                    @endforeach
                                </div>
                                @error('form.method') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">دلیل پرداخت</label>
                                <select class="form-select" wire:model="form.purpose_id">
                                    <option value="">بدون دلیل مشخص</option>
                                    @foreach ($purposes as $purpose)
                                        <option value="{{ $purpose->id }}">{{ $purpose->title }}</option>
                                    @endforeach
                                </select>
                                @error('form.purpose_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">شماره پیگیری / رسید</label>
                                <input type="text" dir="ltr" style="text-align: left" class="form-control" wire:model="form.reference_number"
                                    placeholder="اختیاری" autocomplete="off">
                                @error('form.reference_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">تاریخ پرداخت <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('form.paid_date') is-invalid @enderror" data-jdp
                                    wire:model="form.paid_date" placeholder="1405/01/01" autocomplete="off">
                                @error('form.paid_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ساعت</label>
                                <input type="text" dir="ltr" style="text-align: left" class="form-control @error('form.paid_time') is-invalid @enderror"
                                    wire:model="form.paid_time" placeholder="14:30" autocomplete="off">
                                @error('form.paid_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">مربوط به نوبت</label>
                                {{-- select2: its options are the patient's appointments, sent by the `finance-appointments` event (see the script) --}}
                                <div wire:ignore>
                                    <select class="form-select" id="financeAppointmentSelect" disabled>
                                        <option value="">ابتدا بیمار را انتخاب کنید</option>
                                    </select>
                                </div>
                                @error('form.appointment_user_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">پزشک</label>
                                {{-- select2 builds its own markup: Livewire must not morph it; the script below keeps it in sync with form.doctor_id --}}
                                <div wire:ignore>
                                    <select class="form-select" id="financeDoctorSelect">
                                        <option value="">بدون پزشک مشخص</option>
                                        @foreach ($doctors as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->fullName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('form.doctor_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">توضیحات</label>
                                <textarea class="form-control" rows="2" wire:model="form.description" placeholder="مثال: بیعانه برای نوبت هفتهٔ آینده"></textarea>
                                @error('form.description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save"><i class="fa fa-check me-1"></i> {{ $paymentId ? 'ذخیره تغییرات' : 'ثبت پرداخت' }}</span>
                            <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1"></span> در حال ذخیره...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @once
        @push('scripts')
            <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
            <style>
                #financePaymentModal .select2-container { width: 100% !important; }
            </style>
            <script>
                $(document).ready(function() {
                    var modalEl = document.getElementById('financePaymentModal');

                    // the doctor select: searchable, inside the modal so its list is not hidden behind it
                    var $doctor = $('#financeDoctorSelect');
                    $doctor.select2({
                        dir: 'rtl',
                        width: '100%',
                        minimumResultsForSearch: 0,
                        dropdownParent: $(modalEl),
                    });
                    $doctor.on('change', function() {
                        @this.set('form.doctor_id', $(this).val() || '');
                    });
                    // the appointment select: searchable, filled with the appointments of the chosen patient
                    var $appointment = $('#financeAppointmentSelect');
                    $appointment.select2({
                        dir: 'rtl',
                        width: '100%',
                        minimumResultsForSearch: 0,
                        dropdownParent: $(modalEl),
                    });
                    $appointment.on('change', function() {
                        @this.set('form.appointment_user_id', $(this).val() || '');
                    });
                    Livewire.on('finance-appointments', function(data) {
                        var selected = String(data.selected || '');
                        $appointment.empty().append(new Option(data.hasPatient ? 'بدون نوبت مشخص' : 'ابتدا بیمار را انتخاب کنید', '', false, false));
                        (data.options || []).forEach(function(option) {
                            $appointment.append(new Option(option.text, option.id, false, option.id === selected));
                        });
                        $appointment.prop('disabled', ! data.hasPatient).val(selected).trigger('change.select2');
                    });

                    // the form gives a value too (editing a payment, or the doctor of the chosen appointment)
                    @this.$watch('form.doctor_id', function(value) {
                        value = value || '';
                        if (($doctor.val() || '') !== String(value)) {
                            $doctor.val(String(value)).trigger('change.select2');
                        }
                    });
                    Livewire.on('finance-form-show', function() {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    });
                    Livewire.on('finance-form-hide', function() {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    });
                });
            </script>
        @endpush
    @endonce
</div>
