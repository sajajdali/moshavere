<div>

    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">مدیریت پزشکان و بخش‌ها</h1>
        </div>
    </div>

    @include('admin::layouts.components.alert')

    <div class="card">
        <div class="card-header border-bottom d-block p-3">
            <ul class="nav nav-tabs doctor-service-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button"
                        class="nav-link {{ $activeTab === 'doctors' ? 'active' : '' }} {{ $hasDoctorsWithoutLicence ? 'incomplete-tab' : '' }}"
                        wire:click="setTab('doctors')">
                        <span class="tab-icon"><i class="fa fa-user-md"></i></span>
                        <span class="tab-label">
                            <span class="tab-title">پزشکان</span>
                            <span class="tab-description">اطلاعات و شماره نظام پزشکی</span>
                        </span>
                        @if ($hasDoctorsWithoutLicence)
                            <span class="tab-warning" title="حداقل یک پزشک شماره نظام پزشکی ندارد">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button"
                        class="nav-link {{ $activeTab === 'services' ? 'active' : '' }} {{ $hasServicesWithoutApiCode ? 'incomplete-tab' : '' }}"
                        wire:click="setTab('services')">
                        <span class="tab-icon"><i class="fa fa-th-large"></i></span>
                        <span class="tab-label">
                            <span class="tab-title">بخش‌ها</span>
                            <span class="tab-description">مدیریت کدهای API</span>
                        </span>
                        @if ($hasServicesWithoutApiCode)
                            <span class="tab-warning" title="حداقل یک بخش کد API ندارد">
                                <i class="fa fa-exclamation"></i>
                            </span>
                        @endif
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            @if ($activeTab === 'doctors')
                <div class="row g-3 mb-5 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label" for="doctor-first-name">نام</label>
                        <input id="doctor-first-name" type="text" class="form-control" placeholder="جست‌وجو با نام"
                            wire:model.live.debounce.400ms="search.first_name">
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label" for="doctor-last-name">نام خانوادگی</label>
                        <input id="doctor-last-name" type="text" class="form-control"
                            placeholder="جست‌وجو با نام خانوادگی" wire:model.live.debounce.400ms="search.last_name">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="doctor-licence-status">وضعیت شماره نظام پزشکی</label>
                        <select id="doctor-licence-status" class="form-select" wire:model.live="search.licence_status">
                            <option value="">همه پزشکان</option>
                            <option value="has">دارای شماره نظام پزشکی</option>
                            <option value="missing">بدون شماره نظام پزشکی</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-6">
                        <button type="button" class="btn btn-secondary w-100" wire:click="resetDoctorSearch"
                            title="پاک کردن جست‌وجو">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                </div>

                <div class="row">
                    @forelse ($doctors as $doctor)
                        @php($hasLicence = filled($doctor->dr_licence_number))
                        <div class="col-xl-4 col-lg-6 col-md-6" wire:key="doctor-card-{{ $doctor->id }}">
                            <div class="card h-100 mb-4 shadow-sm {{ $hasLicence ? 'border' : 'border border-danger' }}"
                                @if (!$hasLicence) style="border-width: 2px !important" @endif>
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ $doctor->getUserAvatar() }}" class="avatar avatar-lg brround me-3"
                                            alt="{{ $doctor->full_name }}">
                                        <div>
                                            <h4 class="mb-1">{{ $doctor->full_name ?: 'بدون نام' }}</h4>
                                            <span class="text-muted">شناسه: {{ $doctor->id }}</span>
                                        </div>
                                    </div>

                                    <div class="mb-4 flex-grow-1">
                                        <span class="fw-semibold">بخش‌ها:</span>
                                        @forelse ($doctor->services as $service)
                                            <span
                                                class="badge bg-primary-transparent text-primary me-1 mb-1">{{ $service->title }}</span>
                                        @empty
                                            <span class="text-muted">بخشی ثبت نشده است</span>
                                        @endforelse
                                    </div>

                                    <label class="form-label" for="licence-{{ $doctor->id }}">شماره نظام
                                        پزشکی</label>
                                    <div class="input-group">
                                        <input id="licence-{{ $doctor->id }}" type="text"
                                            class="form-control @error('licenceNumbers.' . $doctor->id) is-invalid @enderror"
                                            placeholder="شماره نظام پزشکی را وارد کنید"
                                            wire:model="licenceNumbers.{{ $doctor->id }}"
                                            @can('update', $doctor)
                                                wire:keydown.enter="saveLicence({{ $doctor->id }})"
                                            @else
                                                disabled title="شما اجازه ویرایش این پزشک را ندارید"
                                            @endcan>
                                        @can('update', $doctor)
                                            <button type="button" class="btn btn-primary"
                                                wire:click="saveLicence({{ $doctor->id }})" wire:loading.attr="disabled"
                                                wire:target="saveLicence({{ $doctor->id }})">
                                                ذخیره
                                            </button>
                                        @endcan
                                    </div>
                                    @error('licenceNumbers.' . $doctor->id)
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info mb-0">پزشکی با این مشخصات پیدا نشد.</div>
                        </div>
                    @endforelse
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $doctors->links() }}
                </div>
            @else
                <div class="row">
                    @forelse ($services as $service)
                        @php($hasApiCode = filled($service->api_code))
                        <div class="col-xl-3 col-lg-4 col-md-6" wire:key="service-card-{{ $service->id }}">
                            <div class="card shadow-sm mb-4 h-100 {{ $hasApiCode ? 'border' : 'border border-danger' }}"
                                @if (!$hasApiCode) style="border-width: 2px !important" @endif>
                                <div class="card-body d-flex flex-column">
                                    <h4 class="mb-2">{{ $service->title }}</h4>
                                    <span class="text-muted mb-4">{{ $service->user_count }} پزشک</span>

                                    <div class="mt-auto">
                                        <label class="form-label" for="api-code-{{ $service->id }}">کد سیستمی سلاک طب</label>
                                        <div class="input-group">
                                            <input id="api-code-{{ $service->id }}" type="text"
                                                class="form-control @error('serviceApiCodes.' . $service->id) is-invalid @enderror"
                                                placeholder="کد سیستمی سلاک طب"
                                                wire:model="serviceApiCodes.{{ $service->id }}"
                                                @can('update', $service)
                                                    wire:keydown.enter="saveServiceApiCode({{ $service->id }})"
                                                @else
                                                    disabled title="شما اجازه ویرایش این بخش را ندارید"
                                                @endcan>
                                            @can('update', $service)
                                                <button type="button" class="btn btn-primary"
                                                    wire:click="saveServiceApiCode({{ $service->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="saveServiceApiCode({{ $service->id }})">
                                                    ذخیره
                                                </button>
                                            @endcan
                                        </div>
                                        @error('serviceApiCodes.' . $service->id)
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info mb-0">بخشی در سیستم ثبت نشده است.</div>
                        </div>
                    @endforelse
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@push('styles')
    <style>
        .doctor-service-tabs {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem;
            width: min(100%, 720px);
            margin: 0 auto;
            padding: .5rem !important;
            background: #eef2f7;
            border: 0 !important;
            border-radius: 1rem;
        }

        .doctor-service-tabs .nav-item {
            width: 100%;
        }

        .doctor-service-tabs .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            width: 100%;
            min-height: 58px;
            padding: .65rem 1rem;
            color: #344054;
            background: transparent;
            border: 0 !important;
            border-radius: .75rem !important;
            text-align: right;
            transition: color .2s ease, background-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .doctor-service-tabs .nav-link:hover {
            color: var(--primary-bg-color);
            background: rgba(255, 255, 255, .7);
        }

        .doctor-service-tabs .nav-link.active {
            color: var(--primary-bg-color) !important;
            background: #fff !important;
            box-shadow: 0 5px 14px rgba(16, 24, 40, .1);
            transform: translateY(-1px);
        }

        .doctor-service-tabs .tab-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            color: #667085;
            background: #fff;
            border-radius: .65rem;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .08);
            font-size: 1.05rem;
        }

        .doctor-service-tabs .active .tab-icon {
            color: #fff;
            background: var(--primary-bg-color);
        }

        .doctor-service-tabs .tab-label {
            display: flex;
            flex-direction: column;
            line-height: 1.35;
        }

        .doctor-service-tabs .tab-title {
            font-size: 1rem;
            font-weight: 700;
        }

        .doctor-service-tabs .tab-description {
            color: #98a2b3;
            font-size: .72rem;
            font-weight: 400;
        }

        .doctor-service-tabs .tab-warning {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            margin-inline-start: auto;
            color: #fff;
            background: #dc3545;
            border-radius: 50%;
            font-size: .75rem;
        }

        .doctor-service-tabs .nav-link.incomplete-tab {
            color: #dc3545;
        }

        .doctor-service-tabs .nav-link.incomplete-tab.active {
            color: #dc3545 !important;
            background: #fff !important;
            box-shadow: 0 5px 14px rgba(220, 53, 69, .15);
        }

        .doctor-service-tabs .nav-link.incomplete-tab.active .tab-icon {
            color: #fff;
            background: #dc3545;
        }

        @media (max-width: 575.98px) {
            .doctor-service-tabs .tab-description {
                display: none;
            }

            .doctor-service-tabs .nav-link {
                gap: .4rem;
                padding-inline: .55rem;
            }

            .doctor-service-tabs .tab-warning {
                position: absolute;
                top: .35rem;
                left: .35rem;
                width: 18px;
                height: 18px;
                font-size: .65rem;
            }
        }
    </style>
@endpush
