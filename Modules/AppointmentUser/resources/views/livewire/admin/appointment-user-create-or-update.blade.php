<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">افزودن نوبت </h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <!-- ROW-2 OPEN -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">انتخاب پزشک یا بخش</h3>
                </div>
                <div class="card-body p-6">
                    <div class="panel panel-primary">
                        <div>
                            <div class="tabs-menu4 border-bottomo-sm">
                                <!-- Tabs -->
                                <nav class="nav d-sm-flex d-block">
                                    <a class="nav-link border border-bottom-0 br-sm-5 me-2 active" data-bs-toggle="tab"
                                        href="#doctors" wire:ignore.self>
                                        پزشک ها
                                    </a>
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasRole('منشی'))
                                        <a class="nav-link border border-bottom-0 br-sm-5 me-2  @if (isset($this->search['searchService'])) active @endif"
                                            data-bs-toggle="tab" href="#sections" wire:ignore.self>
                                            بخش ها
                                        </a>
                                    @endif
                                </nav>
                            </div>
                        </div>
                        <div class="tab-content">
                            {{-- doctor panel --}}
                            <div class="tab-pane active" id="doctors" wire:ignore.self>
                                <div class="row">
                                    <div class="col-md-12 col-xl-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-xl col-lg-6 col-md-12">
                                                        جست و جو در پزشکان
                                                    </div>
                                                    <div class="col-xl-4 col-lg-6 col-md-12 mt-3 mt-lg-0">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"
                                                                wire:model='search.doctors'
                                                                placeholder="نام خانوادگی پزشک">
                                                            @if (isset($this->search['doctors']))
                                                                <button
                                                                    class="btn ripple btn-secondary text-fixed-white input-group-text border-0"
                                                                    wire:click='ignoreSearch'
                                                                    wire:loading.class='btn-loading btn-gray'>نمایش
                                                                    همه</button>
                                                            @else
                                                                <button wire:click='searchDoctors'
                                                                    class="btn ripple btn-info text-fixed-white input-group-text border-0"
                                                                    wire:loading.class='btn-loading btn-gray'
                                                                    wire:target='searchDoctors' type="button">جست و جو
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if (isset($doctors))
                                        @foreach ($doctors as $key => $doctor)
                                            <div class="col-md-4 col-sm-12">
                                                <div class="card custom-card client-card border">
                                                    <div class="card-body">
                                                        <div class="client-card-top">
                                                            <div class="d-flex">
                                                                <div class="rounded-circle align-self-start mb-0">
                                                                </div>
                                                                <div class="flex-fill my-1"> <a
                                                                        href="javascript:void(0);">{{ $doctor?->fullName ?? '--' }}</a>
                                                                    <p>
                                                                        @if ($doctor?->specialities->isEmpty())
                                                                            <span class="badge bg-danger rounded-pill">
                                                                                تخصص ثبت نشده
                                                                            </span>
                                                                        @endif
                                                                        {{ $doctor->DocSpecialities() }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <button class="btn btn-warning w-100" wire:target='docSelected({{ $doctor->id }})' wire:loading.class='btn-loading bg-gray'
                                                                wire:click='docSelected({{ $doctor->id }})'>
                                                                    <i class="fa fa-check" aria-hidden="true"></i>
                                                                    <span>افزودن نوبت</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="alert alert-primary alert-dismissible fade show" role="alert">
                                            <span class="alert-inner--text">پزشکی یافت نشد،لطفا ابتدا پزشکان را به سیستم
                                                اضافه کنید!</span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-center">
                                    {{ $doctors->links() }}
                                </div>
                            </div>
                            {{-- section panel  --}}
                            <div class="tab-pane @if (isset($this->search['searchService'])) active @endif " id="sections"
                                wire:ignore.self>
                                <div class="row">
                                    <div class="col-md-12 col-xl-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-xl col-lg-6 col-md-12">
                                                        جست و جو در بخش ها
                                                    </div>
                                                    <div class="col-xl-4 col-lg-6 col-md-12 mt-3 mt-lg-0">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"
                                                                wire:model='search.searchService' placeholder="نام بخش">
                                                            @if (isset($this->search['searchService']))
                                                                <button
                                                                    class="btn ripple btn-secondary text-fixed-white input-group-text border-0"
                                                                    wire:click='ignoreSearch'
                                                                    wire:loading.class='btn-loading btn-gray'>نمایش
                                                                    همه</button>
                                                            @else
                                                                <button wire:click='searchService'
                                                                    wire:target='searchService'
                                                                    wire:loading.class='btn-loading btn-gray'
                                                                    class="btn ripple btn-info text-fixed-white input-group-text border-0"
                                                                    type="button"> جست و جو</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach ($Services as $key => $service)
                                        <div class="col-md-4 col-sm-12">
                                            <div class="card custom-card client-card border">
                                                <div class="card-body">
                                                    <div class="client-card-top">
                                                        <div class="d-flex">
                                                            <div class="rounded-circle align-self-start mb-0">
                                                            </div>
                                                            <div class="flex-fill my-1"> <a
                                                                    href="{{ route('admin.service.list') }}">{{ $service->title }}</a>
                                                                <p class="mt-2 ms-1">{{ $service->user->count() }} پزشک
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <button class="btn btn-warning w-100"
                                                            wire:loading.class='btn-loading bg-gray' wire:target='serviceSelectedFromServiceSection({{ $service->id }})'
                                                            wire:click='serviceSelectedFromServiceSection({{ $service->id }})'>
                                                            <i class="fa fa-check" aria-hidden="true"></i>
                                                            <span>افزودن نوبت</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-center">
                                    {{ $Services->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    {{-- <div class="modal fade" id="setDocOrsectionMOdal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        {{ $form['modalTitle'] }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        @foreach ($form['modalStatus'] as $wichPartSelected => $status)
                            @if ($status)
                                @if ($wichPartSelected == 'selectDoctor')
                                    @if (isset($form['doctorServices']) && count($form['doctorServices']))
                                        <div class="d-flex flex-column g-3">
                                            @foreach ($form['doctorServices'] as $service)
                                                <a wire:click='addAppointment({{ $service->id }})'
                                                    class="badge bg-primary-gradient my-1 p-5 text-white"
                                                    style="font-size: medium !important ; cursor: pointer;">{{ $service->title }}</a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning" role="alert">
                                            <span class="alert-inner--icon me-2"><i class="fe fe-info"></i></span>
                                            <span class="alert-inner--text"><strong>هیچ بخشی برای این پزشک تعریف نشده
                                                    است</strong>
                                                <br>
                                                لطفا ابتدا برای این پزشک بخش بندی و تنظیمات را انجام دهید تا بتوانید
                                                اقدام
                                                به ثبت
                                                نوبت
                                                نمایید</span>
                                        </div>
                                    @endif
                                @elseif($wichPartSelected == 'selectService')
                                    @if (isset($fetchData['ServiceList']) && $fetchData['ServiceList']->isNotEmpty())
                                        <div class="d-flex flex-column g-3">
                                            @foreach ($fetchData['ServiceList'] as $doc)
                                                <a wire:click='addAppointment({{ $doc->id }})'
                                                    class="badge bg-primary-gradient my-1 p-5 text-white"
                                                    style="font-size: medium !important ; cursor: pointer;">{{ $doc->fullName }}</a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning" role="alert">
                                            <span class="alert-inner--icon me-2"><i class="fe fe-info"></i></span>
                                            <span class="alert-inner--text"><strong>هیچ پزشکی برای این بخش تعریف نشده
                                                    است</strong>
                                                <br>
                                                لطفا ابتدا برای این بخش ، پزشک انتخاب کنید و تنظیمات را انجام دهید تا
                                                بتوانید اقدام
                                                به ثبت
                                                نوبت
                                                نمایید</span>
                                        </div>
                                    @endif
                                @elseif($wichPartSelected == 'selectPlace')
                                    @if (isset($fetchData['placeList']) && $fetchData['placeList']->isNotEmpty())
                                        <div class="d-flex flex-column g-3">
                                            @foreach ($fetchData['placeList'] as $place)
                                                <a wire:click='placeSelected({{ $place->id }})'
                                                    class="badge bg-primary-gradient my-1 p-5 text-white"
                                                    style="font-size: medium !important ; cursor: pointer;">{{ $place->title }}</a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning" role="alert">
                                            <span class="alert-inner--icon me-2"><i class="fe fe-info"></i></span>
                                            <span class="alert-inner--text"><strong>هیچ مطبی برای تعریف نشده
                                                    است</strong>
                                                <br>
                                                لطفا ابتدا یک مطب به سیستتم اضافه کرده و تنظیمات زمان های حضور را از
                                                قسمت
                                                تنظیمات نوبت دهی انجام دهید
                                            </span>
                                        </div>
                                    @endif
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بیخیال</button>
                </div>
            </div>
        </div>
    </div> --}}
    @include('appointmentuser::components.addappointment.modal.doclistmodal')
    @include('appointmentuser::components.addappointment.modal.placelistmodal')
    @include('appointmentuser::components.addappointment.modal.servicelistmodal')
    @include('appointmentuser::components.addappointment.modal.segmentListModal')
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            Livewire.on('lunchModal', function(name) {
                // Close all currently open modals
                $('.modal.show').each(function() {
                    let modalInstance = bootstrap.Modal.getInstance(this);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });
                var myModal = new bootstrap.Modal(document.getElementById(name.name), {
                    keyboard: false
                });
                myModal.show();
            });
        });
    </script>
@endpush
