<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">
                <span>تنظیمات زمان های حضور</span>
                <strong class="text-primary">{{ $doctor->fullName }}</strong>
            </h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom d-flex flex-column flex-sm-row justify-content-between">
            <h4>ویرایش تنظیمات حضور در <strong>تمام بخش ها</strong></h4>
            <button wire:click='editGeneralSetting' class="btn btn-info">
                <div class="d-flex">
                    <i wire:loading.remove wire:target='editGeneralSetting' class="fa fa-pencil-square-o fa-2x me-2"
                        aria-hidden="true"></i>
                    <span wire:loading.remove wire:target='editGeneralSetting'>ویرایش تنظیمات تمام بخش ها</span>
                </div>
                <span wire:loading wire:target='editGeneralSetting' class="spinner-border spinner-border-sm"
                    role="status" aria-hidden="true"></span>
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
            <button class="btn btn-info" type="button" data-bs-toggle="modal" data-bs-target="#changeDocmodal">
                <i class="fa fa-plus fa-xl" aria-hidden="true"></i>
                افزودن بخش با تنظیمات اختصاصی</button>
        </div>
        {{-- TODO::customize special time --}}
        <div class="card-body">
            {{-- section --}}
            <div class="row">
                <p class="text-muted ">در این قسمت بخش هایی که دارای ساعت و روز اختصاصی میباشد نمایشد داده میشود.</p>
                <div aria-multiselectable="true" class="accordion" id="special_accordion" role="tablist">
                    <div class="card mb-0 border-0">
                        <div class="card-header border-bottom-0" id="headingOne" role="tab">
                            <a aria-controls="collapsetree" aria-expanded="true" data-bs-toggle="collapse"
                                href="#collapsetree"
                                class="accor-basic d-flex flex-column flex-sm-row justify-content-between">
                                <span> ویزیت</span>
                                <div>
                                    <button class="btn btn-info my-2 my-sm-0">
                                        <div class="d-flex">
                                            <i class="fa fa-cogs fa-lg me-2 mt-1" aria-hidden="true"></i>
                                            <span>ویرایش تنظیمات این بخش</span>
                                        </div>
                                    </button>
                                    <button class="btn btn-danger">
                                        <div class="d-flex">
                                            <i class="fa fa-trash-o fa-lg me-2 mt-1" aria-hidden="true"></i>
                                            <span>حذف تنظیم اختصاصی</span>
                                        </div>
                                    </button>
                                </div>
                            </a>
                        </div>
                        <div aria-labelledby="headingOne" class="collapse" data-bs-parent="#special_accordion"
                            id="collapsetree" role="tabpanel">
                            <div class="card-body br-bottom-radius-5">
                                <div class="table-responsive ">
                                    <table class="table table-striped  border text-nowrap text-md-nowrap text-center">
                                        <thead>
                                            <tr>
                                                <th>روز</th>
                                                <th>نام بخش</th>
                                                <th>ساعت حضور</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>شنبه</td>
                                                <td>ویزیت</td>
                                                <td>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">10:30
                                                        تا
                                                        12:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">11:30
                                                        تا
                                                        13:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">11:30
                                                        تا
                                                        13:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">15:30
                                                        تا
                                                        16:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">20:30
                                                        تا
                                                        21:30</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>یکشنبه</td>
                                                <td>مشاوره</td>
                                                <td>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">10:30
                                                        تا
                                                        12:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">11:30
                                                        تا
                                                        13:30</span>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-0 mt-2 border-0">
                        <div class="card-header border-bottom-0" id="collapsefourheadingTwo" role="tab">
                            <a aria-controls="collapsefour" aria-expanded="true" data-bs-toggle="collapse"
                                href="#collapsefour"
                                class="accor-basic d-flex flex-column flex-sm-row justify-content-between">
                                <span> جراحی</span>
                                <div>
                                    <button class="btn btn-info my-2 my-sm-0">
                                        <div class="d-flex">
                                            <i class="fa fa-cogs fa-lg me-2 mt-1" aria-hidden="true"></i>
                                            <span>ویرایش تنظیمات این بخش</span>
                                        </div>
                                    </button>
                                    <button class="btn btn-danger">
                                        <div class="d-flex">
                                            <i class="fa fa-trash-o fa-lg me-2 mt-1" aria-hidden="true"></i>
                                            <span>حذف تنظیم اختصاصی</span>
                                        </div>
                                    </button>
                                </div>
                            </a>
                        </div>
                        <div aria-labelledby="collapsefourheadingTwo" class="collapse" data-bs-parent="#accordion"
                            id="collapsefour" role="tabpanel">
                            <div class="card-body br-bottom-radius-5">
                                <div class="table-responsive ">
                                    <table class="table table-striped  border text-nowrap text-md-nowrap text-center">
                                        <thead>
                                            <tr>
                                                <th>روز</th>
                                                <th>نام بخش</th>
                                                <th>ساعت حضور</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>شنبه</td>
                                                <td>ویزیت</td>
                                                <td>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">10:30
                                                        تا
                                                        12:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">11:30
                                                        تا
                                                        13:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">11:30
                                                        تا
                                                        13:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">15:30
                                                        تا
                                                        16:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">20:30
                                                        تا
                                                        21:30</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>یکشنبه</td>
                                                <td>مشاوره</td>
                                                <td>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">10:30
                                                        تا
                                                        12:30</span>
                                                    <span class="bg-secondary text-white rounded-pill py-1 px-2">11:30
                                                        تا
                                                        13:30</span>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- accordion -->
            </div>
        </div>
    </div>
    <livewire:appointmentsetting::modal.service-and-doctor-modal />
</div>

@push('styles')
    <style>
        p {
            font-size: medium;
        }
    </style>
@endpush

@push('scripts')
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
