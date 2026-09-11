@php
    $appointments = $this->handleSearch();
    $hasFilters = collect($search)->contains(fn($value) => filled($value));
    $selectedCount = collect(data_get($form, 'checkbox', []))->filter()->count();
    $totalStats = $appointments->total();
@endphp

<div class="appointment-list-page" dir="rtl">
    <style>
        .side-app{background:#f4f6f8}
        .appointment-list-page{min-height:100vh;background:#f4f6f8;padding:8px 0 32px;color:#101828;font-family:"yekanbakh-reg",Tahoma,sans-serif}
        .appointment-shell{max-width:1560px;margin:0 auto;display:flex;flex-direction:column;gap:18px}
        .appointment-topbar{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;flex-wrap:wrap}
        .appointment-breadcrumb{display:flex;align-items:center;gap:10px;font-size:12.5px;color:#98a2b3;margin-bottom:6px}
        .appointment-title{margin:0;font-size:26px;font-weight:700;letter-spacing:0}
        .appointment-subtitle{font-size:13.5px;color:#667085;margin-top:6px}
        .appointment-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        .om-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:42px;padding:0 16px;border-radius:10px;border:1px solid #d5dae1;background:#fff;color:#344054;font-size:13.5px;font-weight:500;cursor:pointer;line-height:1.2}
        .om-btn:hover{background:#f9fafb;border-color:#b9c2cc;color:#344054;text-decoration:none}
        .om-btn-primary{border-color:#0f766e;background:#0f766e;color:#fff;font-weight:600;box-shadow:0 1px 2px rgba(16,24,40,.08)}
        .om-btn-primary:hover{background:#0b5c56;border-color:#0b5c56;color:#fff}
        .om-btn-danger{border-color:#f0b2ab;color:#b42318}
        .om-btn-danger:hover{background:#fef3f2;color:#b42318}
        .appointment-card{background:#fff;border:1px solid #e6e8ec;border-radius:16px;box-shadow:0 1px 2px rgba(16,24,40,.04);overflow:hidden}
        .appointment-toolbar{padding:16px 18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;border-bottom:1px solid #eef0f3}
        .appointment-search{position:relative;flex:1;min-width:280px}
        .appointment-search i{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#98a2b3;font-size:15px}
        .appointment-search input,.appointment-filter-grid input,.appointment-filter-grid select{width:100%;height:40px;border:1px solid #d5dae1;border-radius:9px;background:#fff;padding:0 12px;font-size:13px;color:#101828;outline:none}
        .appointment-search input{height:44px;border-radius:11px;background:#fbfcfd;padding-right:40px;font-size:13.5px}
        .appointment-search input:focus,.appointment-filter-grid input:focus,.appointment-filter-grid select:focus{border-color:#0f766e;box-shadow:0 0 0 3px rgba(15,118,110,.10)}
        .appointment-tabs{display:flex;align-items:center;gap:6px;padding:4px;background:#f2f4f7;border-radius:11px;flex-wrap:wrap}
        .appointment-tab{height:34px;padding:0 14px;border-radius:8px;border:none;background:transparent;color:#667085;font-size:12.5px;font-weight:500}
        .appointment-tab.active{background:#fff;color:#0f766e;font-weight:600;box-shadow:0 1px 2px rgba(16,24,40,.08)}
        .appointment-advanced{padding:22px 18px;background:#fbfcfd;border-bottom:1px solid #eef0f3}
        .appointment-filter-section{margin-bottom:22px}
        .appointment-filter-heading{display:flex;align-items:center;gap:10px;margin-bottom:12px}
        .appointment-filter-heading span{font-size:13px;font-weight:600;color:#0f766e}
        .appointment-filter-heading:after{content:"";flex:1;height:1px;background:#e6e8ec}
        .appointment-filter-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}
        .appointment-filter-grid label{display:flex;flex-direction:column;gap:6px;margin:0}
        .appointment-filter-grid label span{font-size:12.5px;color:#475467;font-weight:500}
        .appointment-selection{display:flex;align-items:center;gap:14px;padding:12px 18px;background:#eefaf8;border-bottom:1px solid #cdeae5}
        .appointment-selection strong{font-size:13.5px;color:#0f5f59}
        .appointment-table-wrap{overflow-x:auto}
        .appointment-card.has-open-operation,.appointment-table-wrap.has-open-operation{overflow:visible}
        .appointment-row.operation-is-open{position:relative;z-index:20}
        .appointment-row .dropdown-menu{z-index:1090;min-width:230px;border:1px solid #e4e7ec;border-radius:11px;padding:6px;box-shadow:0 14px 32px rgba(16,24,40,.16)}
        .appointment-row .dropdown-menu li a{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:7px;color:#344054;font-size:12.5px;white-space:nowrap}
        .appointment-row .dropdown-menu li a:hover{background:#f2f4f7;text-decoration:none}
        .appointment-row .dropdown-menu li a i{width:16px;text-align:center}
        .appointment-table{min-width:1480px}
        .appointment-grid{display:grid;grid-template-columns:42px 58px minmax(170px,.9fr) 1.15fr 128px 1fr 1fr 118px 168px 120px 126px;align-items:center}
        .appointment-head{padding:0 8px;background:#f9fafb;border-bottom:1px solid #e6e8ec;position:sticky;top:0;z-index:5}
        .appointment-head>div{padding:10px 5px;font-size:12px;font-weight:600;color:#667085}
        .th-sortable{cursor:pointer;user-select:none;display:flex;align-items:center;gap:4px}
        .th-sortable:hover{color:#0f766e}
        .th-sort-arrow{font-size:10px;color:#c8cdd4;transition:color .15s}
        .th-sortable.active .th-sort-arrow{color:#0f766e}
        .appointment-row{padding:10px 8px;border-bottom:1px solid #eef0f3;background:#fff}
        .appointment-row>div{padding:0 5px;min-width:0}
        .om-avatar{width:36px;height:36px;flex:none;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:#fff;background:#0f766e}
        .om-pill{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:500;padding:3px 9px;border-radius:20px;border:1px solid #d5dae1;background:#f9fafb;color:#475467;white-space:nowrap}
        .om-status{display:inline-flex;align-items:center;justify-content:center;width:100%;font-size:12px;font-weight:600;padding:6px 10px;border-radius:8px}
        .payment-expiry{display:flex;align-items:center;justify-content:center;gap:5px;width:100%;margin-top:5px;padding:5px 7px;border-radius:7px;background:#fff7e6;border:1px solid #fedf89;color:#93370d;font-size:10.5px;font-weight:600;line-height:1.45;text-align:center}
        .payment-expiry i{font-size:11px;flex:none}
        .payment-expiry.is-expired{background:#fef3f2;border-color:#fecdca;color:#b42318}
        .om-muted{font-size:11.5px;color:#667085}
        .om-ellipsis{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .appointment-detail{padding:16px 20px 18px;background:#fbfcfd;border-bottom:1px solid #eef0f3;display:grid;grid-template-columns:2fr 1fr 1fr;gap:22px}
        .appointment-footer{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:14px;padding:14px 18px;border-top:1px solid #eef0f3}
        .quick-time-modal .modal-content{border:0;border-radius:20px;overflow:hidden;box-shadow:0 24px 60px rgba(16,24,40,.22)}
        .quick-time-modal .modal-header{padding:20px 22px;border-bottom:1px solid #eef0f3;background:linear-gradient(135deg,#f0fdfa 0%,#fff 72%)}
        .quick-time-modal .modal-title-wrap{display:flex;align-items:center;gap:12px}
        .quick-time-modal .modal-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#0f766e;color:#fff;font-size:18px;box-shadow:0 8px 18px rgba(15,118,110,.2)}
        .quick-time-modal .modal-title{font-size:17px;font-weight:700;color:#101828;margin:0}
        .quick-time-modal .modal-subtitle{font-size:12.5px;color:#667085;margin-top:3px}
        .quick-time-modal .modal-body{padding:22px}
        .quick-time-summary{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:12px 14px;margin-bottom:18px;border-radius:12px;background:#f8fafc;border:1px solid #eaecf0}
        .quick-time-summary span{display:block;font-size:11.5px;color:#98a2b3;margin-bottom:3px}
        .quick-time-summary strong{font-size:13px;color:#344054;font-weight:600}
        .quick-time-field{display:flex;flex-direction:column;gap:7px}
        .quick-time-field label{font-size:12.5px;font-weight:600;color:#344054;margin:0}
        .quick-time-field input{width:100%;height:46px;border:1px solid #d0d5dd;border-radius:10px;padding:0 13px;background:#fff;color:#101828;font-size:14px;outline:none}
        .quick-time-field input:focus{border-color:#0f766e;box-shadow:0 0 0 3px rgba(15,118,110,.1)}
        .quick-time-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px}
        .quick-time-warning{display:flex;gap:10px;align-items:flex-start;margin-top:16px;padding:12px 14px;border:1px solid #fedf89;border-radius:11px;background:#fffaeb;color:#93370d;font-size:12.5px;line-height:1.8}
        .quick-time-sms{display:flex;align-items:center;gap:12px;margin-top:14px;padding:13px 14px;border:1px solid #d1e9ff;border-radius:11px;background:#f5fbff;cursor:pointer}
        .quick-time-sms input{width:18px;height:18px;accent-color:#0f766e;flex:none}
        .quick-time-sms strong{display:block;color:#1849a9;font-size:13px}
        .quick-time-sms small{display:block;color:#475467;font-size:11.5px;margin-top:2px}
        .quick-time-modal .modal-footer{padding:16px 22px;border-top:1px solid #eef0f3;background:#fbfcfd;gap:8px}
        .quick-time-error{font-size:11.5px;color:#d92d20;margin-top:1px}
        .pagination{margin:0}
        @media (max-width: 1200px){.appointment-filter-grid{grid-template-columns:repeat(2,1fr)}}
        @media (max-width: 768px){.appointment-list-page{padding:0 0 28px}.appointment-filter-grid{grid-template-columns:1fr}.appointment-actions{width:100%}.om-btn{flex:1}.appointment-title{font-size:22px}}
    </style>

    <div wire:loading>
        <div class="loading-overlay d-flex align-items-center justify-content-center">
            <div class="dimmer active"><div class="spinner2"><div class="cube1" style="width:20px;height:20px;"></div><div class="cube2" style="width:20px;height:20px;"></div></div></div>
        </div>
    </div>

    <div class="appointment-shell" wire:loading.class="op-0-3">
        <div class="appointment-topbar">
            <div>
                <div class="appointment-breadcrumb"><span>پیشخوان</span><span>/</span><span>نوبت‌ها</span></div>
                <h1 class="appointment-title">لیست نوبت‌های ثبت شده</h1>
                <div class="appointment-subtitle">مدیریت و پیگیری نوبت‌های بیماران - نمایش {{ $appointments->firstItem() ?? 0 }} تا {{ $appointments->lastItem() ?? 0 }} از {{ $appointments->total() }} نوبت</div>
            </div>
            <div class="appointment-actions">
                <button wire:loading.class="btn-loading bg-gray" wire:target="ExportData" wire:click="ExportData" class="om-btn" type="button">
                    <i class="fa fa-download"></i><span>خروجی اکسل</span>
                </button>
                <button wire:loading.class="btn-loading bg-gray" wire:target="showTodayAppointments" wire:click="showTodayAppointments" class="om-btn" type="button">
                    <i class="fa fa-calendar-day-o"></i><span>نوبت‌های امروز</span>
                </button>
                @can('appointment_user.addApp')
                    <a href="{{ route('admin.appointment_user.addApp') }}" class="om-btn om-btn-primary"><i class="fa fa-plus"></i><span>افزودن نوبت</span></a>
                @endcan
            </div>
        </div>

        @include('admin::layouts.components.alert')
        @if (isset($msg) && !empty($msg))
            <div class="alert alert-success fade show" role="alert"><i class="fa fa-check-circle-o me-2"></i>{{ $msg }}</div>
        @endif
        @error('exelError')
            <div class="alert alert-danger fade show" role="alert"><i class="fa fa-remove me-2"></i>{{ $message }}</div>
        @enderror

        <div class="appointment-card">
            <div class="appointment-toolbar">
                <div class="appointment-search">
                    <i class="fa fa-search"></i>
                    <input wire:model.live.debounce.600ms="search.user_mobile" placeholder="جست و جو بر اساس شماره موبایل..." type="text">
                </div>
                <div class="appointment-tabs">
                    <button class="appointment-tab {{ blank($search['AppointmentStatus']) ? 'active' : '' }}" wire:click="$set('search.AppointmentStatus', null)" type="button">همه <span>{{ $totalStats }}</span></button>
                    <button class="appointment-tab {{ (string) $search['AppointmentStatus'] === (string) \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value ? 'active' : '' }}" wire:click="$set('search.AppointmentStatus', {{ \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value }})" type="button">تایید شده</button>
                    <button class="appointment-tab {{ (string) $search['AppointmentStatus'] === (string) \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_MONITORING->value ? 'active' : '' }}" wire:click="$set('search.AppointmentStatus', {{ \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_MONITORING->value }})" type="button">در انتظار تایید</button>
                    <button class="appointment-tab {{ (string) $search['AppointmentStatus'] === (string) \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL->value ? 'active' : '' }}" wire:click="$set('search.AppointmentStatus', {{ \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL->value }})" type="button">کنسل شده</button>
                </div>
                <button class="om-btn {{ $hasFilters ? 'om-btn-primary' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#advanceSearch">
                    <i class="fa fa-sliders"></i><span>جست و جوی پیشرفته</span>
                </button>
                @if ($hasFilters)
                    <button class="om-btn" wire:click="resetProperties" type="button" wire:loading.class="bg-gray btn-loading disabled">نمایش همه نوبت‌ها</button>
                @endif
            </div>

            <div class="collapse {{ $hasFilters ? 'show' : '' }}" id="advanceSearch" wire:ignore.self>
                <div class="appointment-advanced">
                    <form autocomplete="off">
                        <div class="appointment-filter-section">
                            <div class="appointment-filter-heading"><span>مشخصات کاربر</span></div>
                            <div class="appointment-filter-grid">
                                <label><span>آیدی کاربر</span><input wire:model="search.user_id" placeholder="آیدی کاربر" type="text"></label>
                                <label><span>نام</span><input wire:model="search.user_first_name" placeholder="نام کاربر" type="text"></label>
                                <label><span>نام خانوادگی</span><input wire:model="search.user_last_name" placeholder="نام خانوادگی" type="text"></label>
                                <label><span>شماره موبایل</span><input wire:model="search.user_mobile" placeholder="شماره تماس" type="text"></label>
                                <label><span>کد ملی</span><input wire:model="search.national_code" placeholder="کد ملی کاربر" type="text"></label>
                            </div>
                        </div>

                        <div class="appointment-filter-section">
                            <div class="appointment-filter-heading"><span>فیلتر نوبت</span></div>
                            <div class="appointment-filter-grid">
                                <label><span>آیدی نوبت</span><input wire:model="search.appointment_id" placeholder="آیدی نوبت" type="text"></label>
                                <label><span>نوع نوبت</span><select wire:model="search.kind"><option value="">همه انواع</option>@foreach (\Modules\AppointmentUser\Enum\AppointmentUserKindEnum::cases() as $kindCase)<option value="{{ $kindCase->value }}">{{ $kindCase->getName() }}</option>@endforeach</select></label>
                                <label><span>وضعیت نوبت</span><select wire:model="search.AppointmentStatus"><option value="">همه وضعیت‌ها</option>@foreach (\Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::cases() as $enumCase)<option value="{{ $enumCase->value }}">{{ $enumCase->getName() }}</option>@endforeach</select></label>
                                <label><span>زمان نوبت</span><input data-jdp data-name="search.appointment_date" wire:model="search.appointment_date" placeholder="زمان نوبت" type="text"></label>
                                <label><span>زمان ثبت نوبت</span><input data-jdp data-name="search.appointment_set_date" wire:model="search.appointment_set_date" placeholder="زمان ثبت" type="text"></label>
                                <label><span>تاریخ شروع</span><input data-jdp data-name="search.appointment_star_date" wire:model="search.appointment_star_date" placeholder="از تاریخ" type="text"></label>
                                <label><span>تاریخ پایان</span><input data-jdp data-name="search.appointment_end_date" wire:model="search.appointment_end_date" placeholder="تا تاریخ" type="text"></label>
                                <label><span>شماره پرونده</span><input wire:model="search.docNumber" placeholder="شماره پرونده" type="text"></label>
                                @if (!empty(\Modules\User\Entities\User::operators()))
                                    <label><span>اپراتور نوبت</span><select wire:model="search.appointment_operatorId"><option value="">همه اپراتورها</option><option value="0">بدون اپراتور</option>@foreach (\Modules\User\Entities\User::operators() as $operators)<option value="{{ $operators->id }}">{{ $operators->fullName }}</option>@endforeach</select></label>
                                @endif
                                <label><span>ثبت کننده نوبت</span><select wire:model="search.setterAppointment"><option value="">انتخاب کنید...</option>@foreach (($fetchData['appointmentSetter'] ?? []) as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></label>
                            </div>
                        </div>

                        <div class="appointment-filter-section">
                            <div class="appointment-filter-heading"><span>بخش و پزشک</span></div>
                            <div class="appointment-filter-grid">
                                <label><span>بخش / سرویس</span><select wire:model="search.section_status"><option value="">همه بخش‌ها</option>@foreach (($fetchData['Services'] ?? []) as $service)<option value="{{ $service->id }}">{{ $service->title }}</option>@endforeach</select></label>
                                <label style="grid-column:span 2"><span>پزشک</span><select wire:model="search.Doc_id"><option value="">انتخاب کنید...</option>@foreach (($fetchData['doctors'] ?? []) as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->fullName }}</option>@endforeach</select></label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button class="om-btn om-btn-primary" type="button" wire:click="startSearch" wire:loading.class="bg-gray btn-loading disabled">اعمال فیلتر</button>
                            <button class="om-btn" type="button" wire:click="resetProperties" wire:loading.class="bg-gray btn-loading disabled">نمایش همه نوبت‌ها</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($selectedCount > 0)
                <div class="appointment-selection">
                    <strong>{{ $selectedCount }} نوبت انتخاب شده است</strong>
                    <div class="flex-grow-1"></div>
                    @canany(['update', 'delete'], $appointments->first())
                        <button class="om-btn om-btn-danger confirm_swal_alert" data-description="از کنسل کردن نوبت های انتخابی مطمعن هستید؟" data-title="کنسل کردن" data-confirmbtn="بله کنسل شوند" data-action="GroupCancel" type="button">کنسل کردن گروهی</button>
                    @endcanany
                </div>
            @endif

            <div class="appointment-table-wrap">
                <div class="appointment-table">
                    <div class="appointment-grid appointment-head">
                        <div><input type="checkbox" class="checkbox select-all-visible" style="width:16px;height:16px;accent-color:#0f766e"></div>
                        <div class="th-sortable {{ $sortField === 'id' ? 'active' : '' }}" wire:click="sortBy('id')">شناسه <i class="fa {{ $sortField === 'id' ? ($sortDirection === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-v' }} th-sort-arrow"></i></div>
                        <div>کاربر</div>
                        <div>پزشک / اپراتور</div>
                        <div class="th-sortable {{ $sortField === 'date_visit' ? 'active' : '' }}" wire:click="sortBy('date_visit')">زمان نوبت <i class="fa {{ $sortField === 'date_visit' ? ($sortDirection === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-v' }} th-sort-arrow"></i></div>
                        <div class="th-sortable {{ $sortField === 'service_id' ? 'active' : '' }}" wire:click="sortBy('service_id')">بخش <i class="fa {{ $sortField === 'service_id' ? ($sortDirection === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-v' }} th-sort-arrow"></i></div>
                        <div>ثبت شده توسط</div>
                        <div class="th-sortable {{ $sortField === 'created_at' ? 'active' : '' }}" wire:click="sortBy('created_at')">تاریخ ثبت <i class="fa {{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-v' }} th-sort-arrow"></i></div>
                        <div class="th-sortable {{ $sortField === 'status' ? 'active' : '' }}" wire:click="sortBy('status')">وضعیت و عملیات <i class="fa {{ $sortField === 'status' ? ($sortDirection === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-v' }} th-sort-arrow"></i></div>
                        <div>کد ملی</div>
                        <div class="th-sortable {{ $sortField === 'kind' ? 'active' : '' }}" wire:click="sortBy('kind')">نوع نوبت <i class="fa {{ $sortField === 'kind' ? ($sortDirection === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-arrows-v' }} th-sort-arrow"></i></div>
                    </div>

                    @forelse ($appointments as $key => $ap)
                        @php
                            $palette = ['#0f766e','#5925dc','#b54708','#175cd3','#c11574','#067647'];
                            $statusClass = match($ap->status) {
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_SUCCESSFUL => 'background:#ecfdf3;color:#067647;border:1px solid #abefc6',
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_PENDING,
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_MONITORING,
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT => 'background:#fffaeb;color:#b54708;border:1px solid #fedf89',
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL,
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_DISAPPROVED,
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED => 'background:#fef3f2;color:#b42318;border:1px solid #fecdca',
                                default => 'background:#eff8ff;color:#175cd3;border:1px solid #b2ddff',
                            };
                            $kindClass = match($ap->kind) {
                                \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::IN_PERSION => 'background:#f0f9ff;color:#026aa2;border-color:#b9e6fe',
                                \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::ONLINE => 'background:#f4f3ff;color:#5925dc;border-color:#e3e0ff',
                                default => 'background:#fdf2fa;color:#c11574;border-color:#fcceee',
                            };
                            $rowBackground = match($ap->status) {
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL => '#fff1f1',
                                \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT => '#e9f3ff',
                                default => $loop->odd ? '#f7fbff' : '#fff',
                            };
                            $userDocumentRoute = $ap->user
                                ? ($ap->kind === \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::VOIP
                                    ? route('admin.user.report', ['user' => $ap->user->id])
                                    : route('admin.user.document', ['user' => $ap->user->id]))
                                : '#';
                        @endphp
                        <div class="appointment-grid appointment-row" style="background:{{ $rowBackground }}" wire:key="appointment-row-{{ $ap->id }}">
                            <div>
                                <input wire:model="form.checkbox.{{ $ap->id }}" class="checkbox row-checkbox" id="checkbox-{{ $ap->id }}" type="checkbox" style="width:16px;height:16px;accent-color:#0f766e">
                            </div>
                            <div class="d-flex flex-column align-items-center gap-1">
                                <span style="font-size:13px;font-weight:600;color:#344054">{{ $ap->id }}</span>
                                @can('appointment_user.feedBack')
                                    @if ($ap->feedbacks->isNotEmpty() || $ap->surveyVoiceUrl())
                                        <a wire:click="lunchFeedBackModal({{ $ap->id }})" href="#"><span class="om-pill" style="background:#f4f3ff;color:#5925dc;border-color:#e3e0ff">نظرسنجی</span></a>
                                    @endif
                                @endcan
                            </div>
                            <div class="d-flex align-items-center" style="padding-right:2px;padding-left:2px">
                                <div class="d-flex flex-column min-w-0">
                                    <a class="om-ellipsis" href="{{ $userDocumentRoute }}" style="font-size:13.5px;font-weight:600;color:#101828">{{ $ap->user?->full_name ?? 'کاربر حذف شده' }}</a>
                                    <span class="om-muted" style="font-size:12.5px" dir="ltr">{{ $ap->user?->mobile ?? $ap->checkForRegisterForOthers() }}</span>
                                    @if (
                                        setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION) &&
                                            isset($ap->details[\Modules\AppointmentUser\app\Models\AppointmentUser::USRE_ATTENDED_STATUS])
                                    )
                                        <span>{!! $ap->attendedStatus() !!}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <span class="om-ellipsis" style="font-size:13px;font-weight:500;color:#344054">{{ $ap->doctor?->full_name ?? 'پزشک حذف شده' }}</span>
                                @if ($ap->operator)
                                    <span class="om-muted">اپراتور: {{ $ap->operator->full_name }}</span>
                                @endif
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <span style="font-size:14.5px;font-weight:700;color:#101828" dir="ltr">{{ verta($ap->date_visit)->format('Y/m/d') }}</span>
                                <span class="om-muted d-flex align-items-center justify-content-center gap-1 w-100" style="direction:ltr">
                                    <bdi dir="ltr">{{ filled($ap->start_time) ? substr($ap->start_time, 0, 5) : '--:--' }}</bdi>
                                    <span dir="rtl">تا</span>
                                    <bdi dir="ltr">{{ filled($ap->end_time) ? substr($ap->end_time, 0, 5) : '--:--' }}</bdi>
                                </span>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <span class="om-ellipsis" style="font-size:13px;color:#344054">{{ $ap->service?->title ?? 'سرویس حذف شده' }}</span>
                                @if ($ap->hasSegment())
                                    <span class="om-muted om-ellipsis">{{ collect($this->segmentData($ap->id))->pluck('title')->implode('، ') }}</span>
                                @endif
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <span class="om-ellipsis" style="font-size:13px;color:#344054">{{ filled($ap->agent?->fullName) ? $ap->agent->fullName : 'خود کاربر' }}</span>
                                @if ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::ONLINE && $ap->hasAgent())
                                    <span class="om-muted">{{ $ap->confirm_or_reject_by() }}</span>
                                @endif
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <span style="font-size:12.5px;color:#344054" dir="ltr">{{ verta($ap->created_at)->format('Y/m/d') }}</span>
                                <span class="om-muted" dir="ltr">{{ verta($ap->created_at)->format('H:i') }}</span>
                            </div>
                            <div class="d-flex flex-column align-items-center gap-1">
                                <div class="d-flex align-items-center gap-2 w-100">
                                    @if($ap->hasFinalizedPatientNoShow())<span class="om-pill" style="color:#b42318">عدم حضور بیمار · تسویه کامل</span>@endif
                                    @if($ap->hasCompletedPhoneConsultation())<span class="om-pill" style="background:#ecfdf3;color:#067647;border-color:#abefc6">مشاوره تمام شده</span>@endif
                                    @canany(['update', 'delete'], $ap)
                                        <div class="btn-group w-100">
                                            <button type="button" class="om-status dropdown-toggle" style="{{ $statusClass }}" data-bs-toggle="dropdown" data-bs-boundary="viewport">{{ $ap->status->getName() }}</button>
                                            <ul class="dropdown-menu dropdown-menu-end" role="menu">@include('appointmentuser::components.appointmentlist.operationbutton', ['quickTimeEditEnabled' => true])</ul>
                                        </div>
                                    @else
                                        <span class="om-status" style="{{ $statusClass }}">{{ $ap->status->getName() }}</span>
                                    @endcan
                                </div>
                                @if (
                                    $ap->status === \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT
                                    && data_get($ap->details, \Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT . '.status') === true
                                    && $ap->deadline_at
                                )
                                    <span class="payment-expiry" data-payment-deadline="{{ $ap->deadline_at->getTimestampMs() }}">
                                        <i class="fa fa-clock-o"></i>
                                        <span>در حال محاسبه زمان باقی‌مانده...</span>
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <span dir="ltr" style="font-size:12.5px;color:#344054">{{ $ap->user?->national_code ?? '---' }}</span>
                                @if ($ap->isAppForothers())
                                    <span class="om-muted text-primary">برای شخص دیگر</span>
                                @endif
                            </div>
                            <div class="d-flex flex-column align-items-start gap-1">
                                @if ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::IN_PERSION)
                                    {!! $ap->kind->getIcon() !!}
                                @else
                                    <a
                                        @if ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::VOIP)
                                            href="{{ route('admin.consultation.call-reports.appointment', $ap) }}"
                                        @elseif ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::ONLINE && $ap->online->isNotEmpty())
                                            href="{{ route('admin.appointment_user.message.detail', ['onlineAppId' => $ap->online->first()?->id]) }}"
                                        @else
                                            href="#"
                                        @endif
                                    >
                                        <span class="om-pill" style="{{ $kindClass }}">{!! $ap->kind->getIcon() !!} {{ $ap->kind->getName() }}</span>
                                    </a>
                                @endif
                                @if ($ap->kind == \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::ONLINE)
                                    <span class="om-pill" style="background:#fffaeb;color:#b54708;border-color:#fedf89">{{ $ap->online->first()?->messages?->first()?->unReadedMessageCount() ?? 0 }} پیام خوانده نشده</span>
                                @endif
                                @if ($ap->isStoredFromVoip())
                                    <span class="om-pill" style="background:#f0f9ff;color:#026aa2;border-color:#b9e6fe">ثبت از ویپ</span>
                                @endif
                            </div>
                        </div>
                        @if ($setting['show_description'])
                            <div class="appointment-detail">
                                <div><span class="om-muted d-block mb-1">توضیحات</span><span style="font-size:13px;color:#344054;line-height:1.9">{{ $ap->getAppDescription() ?: 'بدون توضیحات' }}</span></div>
                                <div><span class="om-muted d-block mb-1">شماره پرونده</span><span dir="ltr">{{ $ap->user?->document_number ?? '---' }}</span></div>
                                <div><span class="om-muted d-block mb-1">عملیات سریع</span><div class="d-flex gap-2 flex-wrap">@canany(['update', 'delete'], $ap)<button wire:click='editAppointment("{{ $ap->id }}")' class="om-btn" style="height:32px;padding:0 12px" type="button">ویرایش</button>@endcanany @if($ap->user)<a class="om-btn" style="height:32px;padding:0 12px" href="{{ $userDocumentRoute }}">{{ $ap->kind === \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::VOIP ? 'گزارش جامع کاربر' : 'پرونده کاربر' }}</a>@endif</div></div>
                            </div>
                        @endif
                    @empty
                        <div style="padding:70px 20px;display:flex;flex-direction:column;align-items:center;gap:10px">
                            <div style="width:52px;height:52px;border-radius:14px;background:#f2f4f7;display:flex;align-items:center;justify-content:center;font-size:22px;color:#98a2b3"><i class="fa fa-search"></i></div>
                            <div style="font-size:15px;font-weight:600">نوبتی یافت نشد</div>
                            <div style="font-size:13px;color:#667085">عبارت جست و جو یا فیلترها را تغییر دهید.</div>
                            @can('appointment_user.addApp')
                                <a class="om-btn om-btn-primary" href="{{ route('admin.appointment_user.addApp') }}">ثبت نوبت جدید</a>
                            @endcan
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="appointment-footer">
                <span style="font-size:13px;color:#667085">نمایش {{ $appointments->firstItem() ?? 0 }} تا {{ $appointments->lastItem() ?? 0 }} از {{ $appointments->total() }} نوبت</span>
                <div style="justify-self:center">{{ $appointments->links() }}</div>
                <div></div>
            </div>
        </div>

        <div>
            @include('appointmentuser::components.appointmentlist.disapprovemodal')
            @include('appointmentuser::components.appointmentlist.feedbackmodal')
            @include('appointmentuser::components.appointmentlist.quicktimeeditmodal')
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            function updatePaymentCountdowns() {
                const now = Date.now();

                document.querySelectorAll('[data-payment-deadline]').forEach(function(element) {
                    const deadline = Number(element.dataset.paymentDeadline);
                    const label = element.querySelector('span');
                    const remainingSeconds = Math.max(0, Math.ceil((deadline - now) / 1000));

                    if (remainingSeconds <= 0) {
                        element.classList.add('is-expired');
                        label.textContent = 'مهلت پرداخت تمام شده؛ در صف حذف است';
                        return;
                    }

                    const hours = Math.floor(remainingSeconds / 3600);
                    const minutes = Math.floor((remainingSeconds % 3600) / 60);
                    const seconds = remainingSeconds % 60;
                    const parts = [];
                    if (hours) parts.push(hours.toLocaleString('fa-IR') + ' ساعت');
                    if (minutes || hours) parts.push(minutes.toLocaleString('fa-IR') + ' دقیقه');
                    parts.push(seconds.toLocaleString('fa-IR') + ' ثانیه');
                    label.textContent = 'تا ' + parts.join(' و ') + ' دیگر حذف می‌شود';
                });
            }

            function js() {
                const iranianHolidays = @json(holidays_array());
                jalaliDatepicker.startWatch({
                    zIndex: 99999,
                    dayRendering: function(dayOptions) {
                        const formatted = `${dayOptions.year}/${String(dayOptions.month).padStart(2, '0')}/${String(dayOptions.day).padStart(2, '0')}`;
                        return { isHollyDay: iranianHolidays.includes(formatted) };
                    }
                });

                $('.select-all-visible').off('change').on('change', function() {
                    $('.row-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
                });
                $('.select2-show-search').select2();
                $('body').off('change.appointmentSelect2').on('change.appointmentSelect2', '.select2-show-search', function() {
                    @this.set('search.' + $(this).data('id'), $(this).val());
                });
                $(document).off('input.appointmentDate').on('input.appointmentDate', '[data-jdp]', function() {
                    @this.set($(this).data('name'), $(this).val());
                });
                updatePaymentCountdowns();
            }
            js();
            window.clearInterval(window.appointmentPaymentCountdownTimer);
            window.appointmentPaymentCountdownTimer = window.setInterval(updatePaymentCountdowns, 1000);
            $(document).off('show.bs.dropdown.appointmentOperations hidden.bs.dropdown.appointmentOperations')
                .on('show.bs.dropdown.appointmentOperations', '.appointment-row .btn-group', function() {
                    $(this).closest('.appointment-row').addClass('operation-is-open');
                    $(this).closest('.appointment-table-wrap').addClass('has-open-operation');
                    $(this).closest('.appointment-card').addClass('has-open-operation');
                })
                .on('hidden.bs.dropdown.appointmentOperations', '.appointment-row .btn-group', function() {
                    $(this).closest('.appointment-row').removeClass('operation-is-open');
                    $(this).closest('.appointment-table-wrap').removeClass('has-open-operation');
                    $(this).closest('.appointment-card').removeClass('has-open-operation');
                });
            Livewire.on('loadJs', function() { setTimeout(js, 500); });
            Livewire.on('openQuickTimeEditModal', function() {
                setTimeout(function() {
                    const modalElement = document.getElementById('quickTimeEditModal');
                    if (modalElement) {
                        bootstrap.Modal.getOrCreateInstance(modalElement, { keyboard: false }).show();
                        js();
                    }
                }, 100);
            });
            Livewire.on('closeQuickTimeEditModal', function() {
                const modalElement = document.getElementById('quickTimeEditModal');
                if (modalElement) {
                    bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                }
            });
            Livewire.on('lunchModal', function() {
                setTimeout(function() {
                    new bootstrap.Modal(document.getElementById('resoanForDisapproveModal'), { keyboard: false }).show();
                }, 1000);
            });
            Livewire.on('lunchFeedBackModal', function() {
                setTimeout(function() {
                    new bootstrap.Modal(document.getElementById('feedBackModal'), { keyboard: false }).show();
                }, 1000);
            });
            Livewire.on('dateFormatWrong', function() {
                setTimeout(function() { $('html, body').animate({ scrollTop: 0 }, 100); }, 50);
            });
            Livewire.on('exelError', function() {
                setTimeout(function() {
                    swal("توجه!", "تعداد داده ها زیاد است! لطفا با استفاده از جست و جو تعداد داده ها را محدود کنید", "warning");
                    $('html, body').animate({ scrollTop: 0 }, 50);
                }, 1000);
            });
        });
    </script>
@endpush
