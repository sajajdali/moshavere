<div>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">نظرسنجی های انجام شده</h1>
            </div>
        </div>
        @include('admin::layouts.components.alert')
        <div class="row row-sm"  wire:loading.class="op-0-3">
            <div class="col-lg-12">
                <div class="card custom-card" id="resultCard">
                    <div class="card-header d-flex justify-content-between border-bottom">
                        <h3 class="card-title">لیست همه نظر سنجی ها</h3>
                        <div class="card-options">
                            <div>
                                <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                                جست و جوی پیشرفته
                            </button>
                            @if (
                                !empty($search['id']) ||
                                    !empty($search['userName']) ||
                                    !empty($search['doctorName']) ||
                                    !empty($search['serviceName']))
                                <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                                    data-bs-toggle="collapse" data-bs-target="#advanceSearch" aria-expanded="false"
                                    aria-controls="advanceSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                                </button>
                            @endif
                            </div>

                        </div>

                    </div>
                    <div class="card-body">
                        <div class="mb-5 collapse
                        @if (
                            !empty($search['id']) ||
                                !empty($search['userName']) ||
                                !empty($search['doctorName']) ||
                                !empty($search['serviceName'])) show @endif"
                            id="advanceSearch" wire:ignore>
                            <form class="form-horizontal example" autocomplete="off">
                                <div class="row mb-4">
                                    <label for="search-id" class="col-md-2 form-label">ایدی</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-id" wire:model="search.id"
                                            feedBackholder="ایدی مطب مورد نظر" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-user_name" class="col-md-2 form-label">نام کاربر</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-user_name" wire:model="search.userName"
                                            feedBackholder="کد" type="text">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="search-name" class="col-md-2 form-label">نام پزشک</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-name" wire:model="search.doctorName"
                                            feedBackholder="کد" type="text">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="search-name" class="col-md-2 form-label">نام بخش</label>
                                    <div class="col-md-10">
                                        <input class="form-control" id="search-name" wire:model="search.serviceName"
                                            feedBackholder="کد" type="text">
                                    </div>
                                </div>

                                <button class="btn btn-primary" type="button" wire:click="startSearch"
                                    wire:loading.class="bg-gray btn-loading disabled">جست و
                                    جو
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table text-nowrap text-md-nowrap table-bordered text-center">
                                <thead>
                                    <tr class="table-primary">
                                        <th scope="col">#</th>
                                        <th scope="col">نام کاربر</th>
                                        <th scope="col">نام پزشک</th>
                                        <th scope="col">بخش</th>
                                        <th scope="col">نتیجه نظر سنجی</th>
                                        <th scope="col">زمان ویزیت</th>
                                        <th scope="col">زمان انجام نظر سنجی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($feedBacks->isNotEmpty())
                                    @foreach ($feedBacks as $key => $each_app_feedBack)
                                            <tr class="text-center @if ($each_app_feedBack->appointmentUser == null)  table-secondary    @endif">
                                                <td>{{$loop->index +1  }}</td>
                                                <td>{{ $each_app_feedBack->appointmentUser?->user?->fullname ?? 'نوبت یافت نشد' }}
                                                </td>
                                                <td>{{ $each_app_feedBack->appointmentUser?->doctor?->fullname ?? 'نوبت یافت نشد' }}
                                                </td>
                                                <td>{{ $each_app_feedBack->appointmentUser?->service?->title ?? 'نوبت یافت نشد' }}
                                                </td>
                                                <td>
                                                    <a href="#"
                                                        wire:click='showModal({{ $each_app_feedBack->appointment_user_id }})'>مشاهده</a>
                                                </td>
                                                <td>{{ verta($each_app_feedBack->appointmentUser?->date_visit)->format('Y/m/d ساعت H:i') }}
                                                </td>
                                                <td>{{ verta($each_app_feedBack->created_at)->format('Y/m/d ساعت H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="100%" class="text-center">
                                                <div class="alert alert-info">
                                                    هیچ موردی یافت نشد
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $feedBacks->links() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between border-bottom">
                        <h3 class="card-title">نوبت های آنلاین</h3>
                        <div class="card-options">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 shadow-sm">
                                <div class="card overflow-hidden">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h3 class="mb-2 fw-semibold">{{$fetchData['onlineApp']['like']}}</h3>
                                                <p class="text-muted fs-13 mb-0">راضی از نوبت دهی</p>
                                                <p class="text-muted mb-0 mt-2 fs-12">
                                                    <span class="icn-box text-success fw-semibold fs-13 me-1">

                                                </p>
                                            </div>
                                            <div wire:click='showSpecialFeedback("onlineLike")' style="cursor: pointer"  data-toggle="tooltip" data-placement="top" title="نمایش نظرات" class="col col-auto top-icn dash">
                                                <div class="counter-icon bg-success dash ms-auto box-shadow-success">
                                                    <i class="fa fa-heart text-white text-white" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 shadow-sm">
                                <div class="card overflow-hidden ">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h3 class="mb-2 fw-semibold">{{$fetchData['onlineDoc']['like']}}</h3>
                                                <p class="text-muted fs-13 mb-0">راضی از کادر درمان</p>
                                                <p class="text-muted mb-0 mt-2 fs-12">
                                                    <span class="icn-box text-success fw-semibold fs-13 me-1">

                                                </p>
                                            </div>
                                            <div wire:click='showSpecialFeedback("onlineDocLike")' style="cursor: pointer"  data-toggle="tooltip" data-placement="top" title="نمایش نظرات"  class="col col-auto top-icn dash">
                                                <div class="counter-icon bg-success dash ms-auto box-shadow-success">
                                                    <i class="fa fa-heart text-white" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 shadow-sm">
                                <div class="card overflow-hidden">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h3 class="mb-2 fw-semibold">{{$fetchData['onlineApp']['dislike'] }}</h3>
                                                <p class="text-muted fs-13 mb-0">ناراضی از نوبت دهی</p>
                                                <p class="text-muted mb-0 mt-2 fs-12">
                                                    <span class="icn-box text-danger fw-semibold fs-13 me-1">

                                                </p>
                                            </div>
                                            <div class="col col-auto top-icn dash">
                                                <div wire:click='showSpecialFeedback("onlineAppdislike")' style="cursor: pointer"  data-toggle="tooltip" data-placement="top" title="نمایش نظرات"  class="counter-icon bg-secondary dash ms-auto box-shadow-secondary">
                                                    <i class="fa fa-thumbs-down text-white" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 shadow-sm">
                                <div class="card overflow-hidden ">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h3 class="mb-2 fw-semibold">{{$fetchData['onlineDoc']['dislike']}}</h3>
                                                <p class="text-muted fs-13 mb-0">ناراضی از کادر درمان</p>
                                                <p class="text-muted mb-0 mt-2 fs-12">
                                                    <span class="icn-box text-danger fw-semibold fs-13 me-1">

                                                </p>
                                            </div>
                                            <div wire:click='showSpecialFeedback("onlineDocdislike")' style="cursor: pointer"  data-toggle="tooltip" data-placement="top" title="نمایش نظرات"  class="col col-auto top-icn dash">
                                                <div class="counter-icon bg-secondary dash ms-auto box-shadow-secondary">
                                                    <i class="fa fa-thumbs-down text-white" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between border-bottom">
                        <h3 class="card-title">نوبت های حضوری</h3>
                        <div class="card-options">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 shadow-sm">
                                <div class="card overflow-hidden">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h3 class="mb-2 fw-semibold">{{$fetchData['inPerson_app']['like']}}</h3>
                                                <p class="text-muted fs-13 mb-0">راضی از نوبت دهی</p>
                                                <p class="text-muted mb-0 mt-2 fs-12">
                                                    <span class="icn-box text-success fw-semibold fs-13 me-1">

                                                </p>
                                            </div>
                                            <div class="col col-auto top-icn dash">
                                                <div wire:click='showSpecialFeedback("inPerson_applike")' style="cursor: pointer"  data-toggle="tooltip" data-placement="top" title="نمایش نظرات" class="counter-icon bg-success dash ms-auto box-shadow-success">
                                                    <i class="fa fa-heart text-white text-white" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 shadow-sm">
                                <div class="card overflow-hidden ">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h3 class="mb-2 fw-semibold">{{$fetchData['inPerson_doc']['like']}}</h3>
                                                <p class="text-muted fs-13 mb-0">راضی از کادر درمان</p>
                                                <p class="text-muted mb-0 mt-2 fs-12">
                                                    <span class="icn-box text-success fw-semibold fs-13 me-1">

                                                </p>
                                            </div>
                                            <div class="col col-auto top-icn dash">
                                                <div wire:click='showSpecialFeedback("inPerson_doclike")' style="cursor: pointer"  data-toggle="tooltip" data-placement="top" title="نمایش نظرات" class="counter-icon bg-success dash ms-auto box-shadow-success">
                                                    <i class="fa fa-heart text-white" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 shadow-sm">
                                <div class="card overflow-hidden">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h3 class="mb-2 fw-semibold">{{$fetchData['inPerson_app']['dislike'] }}</h3>
                                                <p class="text-muted fs-13 mb-0">ناراضی از نوبت دهی</p>
                                                <p class="text-muted mb-0 mt-2 fs-12">
                                                    <span class="icn-box text-danger fw-semibold fs-13 me-1">

                                                </p>
                                            </div>
                                            <div class="col col-auto top-icn dash">
                                                <div wire:click='showSpecialFeedback("inPerson_appdislike")' style="cursor: pointer"  data-toggle="tooltip" data-placement="top" title="نمایش نظرات" class="counter-icon bg-secondary dash ms-auto box-shadow-secondary">
                                                    <i class="fa fa-thumbs-down text-white" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 shadow-sm">
                                <div class="card overflow-hidden ">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h3 class="mb-2 fw-semibold">{{$fetchData['inPerson_doc']['dislike']}}</h3>
                                                <p class="text-muted fs-13 mb-0">ناراضی از کادر درمان</p>
                                                <p class="text-muted mb-0 mt-2 fs-12">
                                                    <span class="icn-box text-danger fw-semibold fs-13 me-1">

                                                </p>
                                            </div>
                                            <div class="col col-auto top-icn dash">
                                                <div wire:click='showSpecialFeedback("inPerson_docdislike")' style="cursor: pointer"  data-toggle="tooltip" data-placement="top" title="نمایش نظرات" class="counter-icon bg-secondary dash ms-auto box-shadow-secondary">
                                                    <i class="fa fa-thumbs-down text-white" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @include('appointmentuser::components.appointmentlist.feedbackmodal')
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script>
        $(document).ready(function() {
            Livewire.on('lunchFeedBackModal', function() {
                setTimeout(() => {
                    var feedBackModal = new bootstrap.Modal(document.getElementById(
                        'feedBackModal'), {
                        keyboard: false
                    });
                    feedBackModal.show();
                }, 1000);
            });
            Livewire.on('appointmentNotFound', function() {
                swal({
                    title: "نکته!",
                    text: "نوبت حذف شده است!",
                    confirmButtonText: 'حله'
                });
            });
            Livewire.on('scrollToTop', function() {
                setTimeout(() => {
                    $('html, body').animate({ scrollTop: 0 }, '120');
                }, 50);
            });
        });
    </script>
@endpush
