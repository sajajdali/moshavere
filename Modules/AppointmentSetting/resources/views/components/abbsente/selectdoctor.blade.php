<div>
    <div class="card">
        <div class="card-header border-bottom">
            <h3 class="card-title"> انتخاب پزشک</h3>
            <div class="card-options">
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#advanceSearch"
                    aria-expanded="false" aria-controls="advanceSearch">
                    جست و جوی پیشرفته
                </button>
                @if (isset($search['id']) || isset($search['mobile']) || isset($search['first_name']) || isset($search['last_name']))
                    <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                        wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="mb-5 collapse {{ $searchPanel }}" id="advanceSearch" wire:ignore>
                <form class="form-horizontal example" autocomplete="off">
                    <div class="row mb-4">
                        <label for="search-id" class="col-md-2 form-label">ایدی</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-id" wire:model="search.id" absenteeholder="ایدی پزشک"
                                type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">نام پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.first_name"
                                absenteeholder="نام " type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">نام خانوادگی پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.last_name"
                                absenteeholder="نام خانوادگی" type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-name" class="col-md-2 form-label">موبایل پزشک</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-name" wire:model="search.mobile"
                                absenteeholder="شماره تماس" type="text">
                        </div>
                    </div>
                    <button class="btn btn-primary" type="button" wire:click="searchDoctor"
                        wire:loading.class="bg-gray btn-loading disabled">جست و
                        جو
                    </button>
                </form>
            </div>
            <div class="d-flex justify-content-center">
                <div class="row">
                    <div class="d-flex mt-1 mb-3 align-items-center">
                        <p style="font-size: medium" class="text-muted">لطفا پزشک و یا پزشکانی که مایل به
                            ویرایش
                            تاریخ نوبت دهی آنها هستید (غیر
                            فعال سازی یک تاریخ خاص) را انتخاب بکنید.<button id="checkAllButton"
                                class="text-primary text-reset">انتخاب همه</button>
                            <button class="text-danger text-reset d-none" id="uncheckAllButton">لغو انتخاب</button>
                        </p>
                    </div>
                    @foreach ($doctors as $key => $doctorList)
                        <div class="col-md-4">
                            <div class="form-group mt-2">
                                <div class="checkbox">
                                    <div class="custom-checkbox custom-control">
                                        <input type="checkbox" wire:model='doctor.{{ $doctorList->id }}'
                                            @if (array_key_exists($doctorList->id, $doctor) && $doctor[$doctorList->id] == 'true') checked @endif data-checkboxes="mygroup"
                                            class="custom-control-input" id="checkbox-{{ $key }}">
                                        <label for="checkbox-{{ $key }}"
                                            class="custom-control-label">{{ $doctorList->full_name }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- TODO::alert Message --}}
            {{-- <div class="alert alert-danger" role="alert">
                <p class="text-danger"><strong class="me-2">خطا!!</strong>لطفا یک پزشک را انتخاب کنید</p>
            </div> --}}
            <div class="row">
                <div class="col-9"></div>
                <div class="col-3 text-end">
                    <button class="btn btn-success" type="button" wire:click='AddStep'>
                        <span wire:loading.remove wire:target='AddStep'>مرحله بعد</span>
                        <span wire:loading wire:target='AddStep' class="spinner-border spinner-border-sm" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
