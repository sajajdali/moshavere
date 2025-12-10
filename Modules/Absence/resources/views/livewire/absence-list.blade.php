<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">لیست عدم حضور های ثبت شده</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            @can('create', Modules\Absence\app\Models\Absence::class)
            <a href="{{ route('admin.absence.create') }}" class="btn btn-info">افزودن</a>
            @endcan
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between border-bottom">
                    <h3 class="card-title">لیست حضور های ثبت شده</h3>
                    <div class="card-options">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                            جست و جوی پیشرفته
                        </button>
                        @if (isset($search['id']) || isset($search['doc_name']) || isset($search['service_name']) || isset($search['start_date']) || isset($search['end_date']))
                        <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                            wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                        </button>
                        @endif
                    </div>

                </div>
                <div class="card-body">
                    <div class="mb-5 collapse" id="advanceSearch" wire:ignore>
                        <form class="form-horizontal example" autocomplete="off">
                            <div class="row mb-4">
                                <label for="search-id" class="col-md-2 form-label">ایدی</label>
                                <div class="col-md-10">
                                    <input class="form-control" id="search-id" wire:model="search.id"
                                        placeholder="ایدی پزشک مورد نظر" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="search-doc_name" class="col-md-2 form-label">نام خانوادگی پزشک</label>
                                <div class="col-md-10">
                                    <input class="form-control" id="search-doc_name" wire:model="search.doc_name"
                                        placeholder="نام پزشک" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="search-service_name" class="col-md-2 form-label">نام بخش</label>
                                <div class="col-md-10">
                                    <input class="form-control" id="search-service_name" wire:model="search.service_name"
                                        placeholder="نام خانوادگی پزشک" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="search-start_date" class="col-md-2 form-label datePicker">تاریخ شروع</label>
                                <div class="col-md-10">
                                    <input class="form-control datePicker" id="search-start_date" data-name='search.start_date' data-jdp
                                        wire:model="search.start_date" placeholder="تاریخ شروع عدم حضور" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="search-end_date" class="col-md-2 form-label datePicker">تاریخ پایان</label>
                                <div class="col-md-10">
                                    <input class="form-control datePicker" id="search-end_date" wire:model="search.end_date" data-name='search.end_date' data-jdp
                                        placeholder="تاریخ پایان عدم حضور" type="text">
                                </div>
                            </div>
                            <button class="btn btn-primary" type="button" wire:click="startSearch"
                                wire:loading.class="bg-gray btn-loading disabled">جست و
                                جو
                            </button>
                        </form>
                    </div>
                    <div class="table-responsive mb-3">
                        <table class="table text-nowrap text-md-nowrap table-bordered text-center"
                            wire:loading.class="op-0-3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">نام پزشک</th>
                                    <th scope="col">بخش ها</th>
                                    <th scope="col">تاریخ شروع</th>
                                    <th scope="col">تاریخ پایان</th>
                                    <th scope="col">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($absences->isNotEmpty())
                                    @foreach ($absences as $key => $absence)
                                    <tr class="text-center">
                                        <td>{{$absence->id}}</td>
                                        <td>{{$absence->user->full_name}}</td>
                                        <td>{!! $absence->checkForService() !!}</td>
                                        <td>{{{verta($absence->start_at)->format('Y/m/d')}}}</td>
                                        <td>{{{verta($absence->end_at)->format('Y/m/d')}}}</td>
                                        <td>
                                            <div class="btn-group mt-2 mb-2">
                                                @can('delete', $absence)
                                                <button type="button" class="btn btn-danger delete_confirm_alert"
                                                data-label="تنظیمات عدم حضور" data-id="{{ $absence->id }}">
                                                حذف
                                                 </button>
                                                 @else
                                                 <button type="button" class="btn btn-light" disabled>
                                                 حذف
                                                  </button>
                                            @endcan
                                            </div>

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
                        {{ $absences->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
<script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>

<script>
    var myCollapsible = document.getElementById('advanceSearch')
    myCollapsible.addEventListener('show.bs.collapse', function() {
        @this.set('searchPanel', 'show');
    });
    myCollapsible.addEventListener('hide.bs.collapse', function() {
        @this.set('searchPanel', '');
    })
    jalaliDatepicker.startWatch();
    $(document).on('input', '[data-jdp]', function() {
        let selectedDate = $(this).val();
        let seterValue = $(this).data('name');
        @this.set(seterValue, selectedDate);
    });
    Livewire.on('closeCollaps',function(){
        $('#advanceSearch').removeClass('show');
        $('#advanceSearch').addClass('hide');
    })
</script>
@endpush
