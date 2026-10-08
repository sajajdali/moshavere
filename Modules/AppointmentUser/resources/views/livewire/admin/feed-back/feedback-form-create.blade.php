<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">ایجاد فرم نظرسنجی</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('admin.appointment.feedback.forms') }}" class="btn btn-secondary">بازگشت به فرم های نظرسنجی</a>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    <div class="card custom-card">
        <div class="card-header border-bottom">
            <h3 class="card-title">اتصال نظرسنجی</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <label class="col-md-2 form-label">عنوان نظرسنجی</label>
                <div class="col-md-10">
                    <input type="text" wire:model="form.title"
                        class="form-control @error('form.title') is-invalid @enderror">
                    @error('form.title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
            @if ($doctors->count() > 1)
                <div class="row mb-4">
                    <label class="col-md-2 form-label">پزشک</label>
                    <div class="col-md-10">
                        {{-- select2 builds its own markup: Livewire must not morph it back --}}
                        <div wire:ignore class="position-relative" id="feedbackDoctorSelectWrap">
                            <select class="form-select select2-show-search" id="feedbackDoctorSelect">
                                <option value="">همه پزشکان</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((string) $form['doctor_id'] === (string) $doctor->id)>
                                        {{ $doctor->fullname }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('form.doctor_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            @endif
            @if ($services->count() > 1)
                <div class="row mb-4">
                    <label class="col-md-2 form-label">بخش</label>
                    <div class="col-md-10">
                        <select class="form-select" wire:model="form.service_id">
                            <option value="">همه بخش ها</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
            @if ($places->count() > 1)
                <div class="row mb-4">
                    <label class="col-md-2 form-label">مطب</label>
                    <div class="col-md-10">
                        <select class="form-select" wire:model="form.place_id">
                            <option value="">همه مطب ها</option>
                            @foreach ($places as $place)
                                <option value="{{ $place->id }}">{{ $place->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="form-active" wire:model="form.active">
                <label class="form-check-label" for="form-active">فعال</label>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header d-flex justify-content-between border-bottom">
            <h3 class="card-title">سوالات</h3>
            <button type="button" class="btn btn-primary" wire:click="addQuestion">
                <i class="fa fa-plus me-1"></i> افزودن سوال
            </button>
        </div>
        <div class="card-body">
            @error('questions') <div class="alert alert-danger">{{ $message }}</div> @enderror
            @foreach ($questions as $i => $question)
                <div class="border p-3 mb-3" style="border-radius: 10px;" wire:key="question-{{ $i }}">
                    <div class="d-flex justify-content-between mb-3">
                        <strong>سوال {{ $i + 1 }}</strong>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light" wire:click="moveQuestion({{ $i }}, -1)" @disabled($i === 0)><i class="fa fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-sm btn-light" wire:click="moveQuestion({{ $i }}, 1)" @disabled($i === count($questions) - 1)><i class="fa fa-arrow-down"></i></button>
                            <button type="button" class="btn btn-sm btn-danger" wire:click="removeQuestion({{ $i }})"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">متن سوال</label>
                            <input type="text" wire:model="questions.{{ $i }}.title"
                                class="form-control @error('questions.' . $i . '.title') is-invalid @enderror">
                            @error('questions.' . $i . '.title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">نوع پاسخ</label>
                            <select class="form-select" wire:model.live="questions.{{ $i }}.type">
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if (in_array($question['type'], $optionTypes, true))
                        <div class="mb-3">
                            <label class="form-label">گزینه ها (هر گزینه در یک خط)</label>
                            <textarea rows="4" wire:model="questions.{{ $i }}.options"
                                class="form-control @error('questions.' . $i . '.options') is-invalid @enderror"></textarea>
                            @error('questions.' . $i . '.options') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    @endif
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="required-{{ $i }}"
                            wire:model="questions.{{ $i }}.required">
                        <label class="form-check-label" for="required-{{ $i }}">پاسخ اجباری</label>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer text-end">
            <button type="button" class="btn btn-success" wire:click="save" wire:loading.attr="disabled">ذخیره فرم نظرسنجی</button>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <style>
        #feedbackDoctorSelect + .select2-container { width: 100% !important; }
    </style>
    <script>
        $(document).ready(function() {
            $('#feedbackDoctorSelect').select2({
                    dir: 'rtl',
                    width: '100%',
                    // inside the card, so it opens under the field instead of behind the fixed header
                    dropdownParent: $('#feedbackDoctorSelectWrap'),
                })
                .on('change', function() {
                    @this.set('form.doctor_id', $(this).val());
                });
        });
    </script>
@endpush
