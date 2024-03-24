<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">
                <span>تنظیمات زمان های حضور</span>
                <strong class="text-primary">{{ $doctor->fullName }}</strong>
            </h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="card">
        <div class="card-header border-bottom d-flex flex-column flex-sm-row justify-content-between">
            <h4>ویرایش تنظیمات حضور در <strong>تمام بخش ها</strong></h4>
            <button wire:click='editGeneralSetting' class="btn btn-info" wire:loading.class='disable btn-loading bg-gray'>
                <div class="d-flex">
                    <i class="fa fa-pencil-square-o fa-2x me-2" aria-hidden="true"></i>
                    <span>ویرایش تنظیمات تمام بخش ها</span>
                </div>
            </button>
        </div>
        <div class="card-body">
            {{-- section --}}
            <div class="row">
                <p class="text-muted ">تنظیمات انجام شده برای بخش این پزشک به شرح زیر میباشد.</p>
                <div class="row row-sm">
                    <div class="col-lg-12">
                        <div class="table-responsive ">
                            <table class="table table-striped  border text-nowrap text-md-nowrap text-center">
                                <thead>
                                    <tr>
                                        <th>روز</th>
                                        <th>ساعت حضور</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->GeneralTimes() as $appsetting)
                                        @if (count($appsetting) > 1)
                                            <tr>
                                                <td>{{ $appsetting->first()->day_number->getName() }}</td>
                                                <td>
                                                    @foreach ($appsetting as $key => $eachDayTime)
                                                        <span class="bg-secondary text-white rounded-pill p-2">از
                                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $eachDayTime->start_at)->format('H:i') }}
                                                            &nbsp;
                                                            تا
                                                            &nbsp;
                                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $eachDayTime->end_at)->format('H:i') }}</span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td>{{ $appsetting->first()->day_number->getName() }}</td>
                                                <td>
                                                    <span class="bg-secondary text-white rounded-pill p-2">از
                                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $appsetting->first()->start_at)->format('H:i') }}
                                                        &nbsp;
                                                        تا
                                                        &nbsp;
                                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $appsetting->first()->end_at)->format('H:i') }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between">
            <h4>بخش ها با تنظیمات <strong>اختصاصی</strong></h4>
            <button class="btn btn-info" type="button" data-bs-toggle="modal" data-bs-target="#ServiceAndPlaceModal">
                <i class="fa fa-plus fa-xl" aria-hidden="true"></i>
                افزودن بخش با تنظیمات اختصاصی</button>
        </div>
        {{-- TODO::customize special time --}}
        <div class="card-body">
            {{-- section --}}
            <div class="row">
                <p class="text-muted ">در این قسمت بخش هایی که دارای ساعت و روز اختصاصی میباشد نمایش داده می شود.</p>
                @if ($fetchData['SpecialAppointmentSetting']->isNotEmpty())
                    @foreach ($fetchData['SpecialAppointmentSetting'] as $key => $specialAppSetting)
                        <div aria-multiselectable="true" class="accordion"
                            id="special_accordion-{{ $specialAppSetting->id }}" role="tablist">
                            <div class="card mb-0 border-0">
                                <div class="card-header border-bottom-0" id="headingOne" role="tab">
                                    <a aria-controls="collapsetree" aria-expanded="true" data-bs-toggle="collapse"
                                        href="#collapsetree"
                                        class="accor-basic d-flex flex-column flex-sm-row justify-content-between">
                                        <span> {{ $specialAppSetting->service->title }}</span>
                                        <div>
                                            <button type="button" class="btn btn-info my-2 my-sm-0"
                                                wire:click='editSpecialSection({{ $specialAppSetting->id }})'>
                                                <div class="d-flex">
                                                    <i class="fa fa-cogs fa-lg me-2 mt-1" aria-hidden="true"></i>
                                                    <span>ویرایش تنظیمات این بخش</span>
                                                </div>
                                            </button>
                                            <button class="btn btn-danger delete_confirm_alert"
                                                data-label="تنظیمات اختصاصی" data-id="{{ $specialAppSetting->id }}">
                                                <div class="d-flex">
                                                    <i class="fa fa-trash-o fa-lg me-2 mt-1 " aria-hidden="true"></i>
                                                    <span>حذف تنظیم اختصاصی</span>
                                                </div>
                                            </button>
                                        </div>
                                    </a>
                                </div>
                                <div aria-labelledby="headingOne" class="collapse"
                                    data-bs-parent="#special_accordion-{{ $specialAppSetting->id }}" id="collapsetree"
                                    role="tabpanel">
                                    <div class="card-body br-bottom-radius-5">
                                        <div class="table-responsive ">
                                            <table
                                                class="table table-striped  border text-nowrap text-md-nowrap text-center">
                                                <thead>
                                                    <tr>
                                                        <th>روز</th>
                                                        <th>ساعت حضور</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($this->SpecialTimes($specialAppSetting->id) as $spAppsetting)
                                                        @if (count($spAppsetting) > 1)
                                                            <tr>
                                                                <td>{{ $spAppsetting->first()->day_number->getName() }}
                                                                </td>
                                                                <td>
                                                                    @foreach ($spAppsetting as $key => $eachDayTime)
                                                                        <span
                                                                            class="bg-secondary text-white rounded-pill p-2">از
                                                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $eachDayTime->start_at)->format('H:i') }}
                                                                            &nbsp;
                                                                            تا
                                                                            &nbsp;
                                                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $eachDayTime->end_at)->format('H:i') }}</span>
                                                                    @endforeach
                                                                </td>
                                                            </tr>
                                                        @else
                                                            <tr>
                                                                <td>{{ $spAppsetting->first()->day_number->getName() }}
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="bg-secondary text-white rounded-pill p-2">از
                                                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $spAppsetting->first()->start_at)->format('H:i') }}
                                                                        &nbsp;
                                                                        تا
                                                                        &nbsp;
                                                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $spAppsetting->first()->end_at)->format('H:i') }}</span>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info" role="alert"> ساعت اختصاصی برای هیچ بخشی تعریف نشده است! </div>
                @endif
            </div>
        </div>
    </div>
    <livewire:appointmentsetting::general-setting.modal.service-and-place-modal :doctor="$doctor" />
</div>

@push('styles')
    <style>
        p {
            font-size: medium;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script>
        $(document).ready(function() {
            Livewire.on('closeModal', function() {
                var myModalEl = document.querySelector('#changeDocmodal')
                var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)
                modal.hide();
            });
        });
    </script>
@endpush
