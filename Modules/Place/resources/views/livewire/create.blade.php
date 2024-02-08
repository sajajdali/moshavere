<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">
                <span>اضافه کردن مطب</span>
            </h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="card">
        <div class="card-body">
            {{-- section --}}
            <hr style="opacity: 0.5">
            <div class="row mb-2">
                <div class="col-md-2 pt-2">
                    <label class="text-primary">نام مطب:</label>
                </div>
                <div class="col-md-10">
                    <div class="input-group mb-3">
                        <input type="text" wire:model='place.name' class="form-control" aria-describedby="basic-addon3">
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <label for="lastName" class=" text-primary">لوکشین مطب</label>
                <div class="col-lg-12">
                    <div class="card" id="map">
                        <div class="card-header border-bottom">
                            <div class="card-title">With Popup</div>
                        </div>
                        <div class="card-body">
                            <div class="h-500" id="leaflet2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 d-flex flex-column justify-content-start flex-sm-row justify-content-sm-between">
                    <h3>شماره مطب</h3>
                    <div>
                        <button type="button" wire:click="addCounter('number')"
                            class="btn btn-info rounded-pill text-center my-3 my-sm-0">
                            <span wire:loading.remove wire:target="addCounter('number')"> <span
                                    class="d-flex align-items-center"><i class="fa fa-plus fa-lg me-1"
                                        aria-hidden="true"></i>
                                    <span class="ms-1">اضافه کردن
                                        شماره</span></span></span>
                            <span wire:loading wire:target="addCounter('number')">
                                <div class="spinner-border spinner-border-sm" role="status">
                                </div>
                            </span>
                        </button>
                        @if ($counter['number'] > 1)
                            <button type="button" wire:click="removeCounter('number')"
                                class="btn btn-danger rounded-pill text-center">
                                <span wire:loading.remove wire:target="removeCounter('number')">
                                    <i class="fa fa-minus" aria-hidden="true"></i> <span>حذف شماره</span>
                                </span>
                                <span wire:loading wire:target="removeCounter('number')">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                    </div>
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <hr class="d-none d-sm-block my-2 px-5 text-center" style="opacity: 0.5;">
                </div>
                @for ($i = 0; $i < $counter['number']; $i++)
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group ">
                                <label for="exampleInputPassword2">
                                    @if ($i >= 1)
                                        شماره ی - {{ $i + 1 }}
                                    @endif
                                </label>
                                <input type="text" class="form-control" id="placenumber-{{$i}}" wire:model='place.number.{{$i}}'
                                    placeholder="شماره تماس">
                            </div>
                        </div>
                    </div>
                @endfor
                <div class="col-12">
                    @if (!empty($doctors))
                        <div class="row mt-5">
                            <h4>لیست پزشکان</h4>
                            <hr style="opacity: 0.9">
                            <div class="row">
                                <p class="text-muted my-1">لطفا پزشکان مرتبط با این مطب را انتخاب کنید</p>
                                @foreach ($doctors as $key => $doctorList)
                                    <div class="col-md-4">
                                        <div class="form-group mt-2">
                                            <div class="checkbox">
                                                <div class="custom-checkbox custom-control">
                                                    <input type="checkbox" wire:model='doctor.{{ $doctorList->id }}'
                                                        @if (array_key_exists($doctorList->id, $doctor) && $doctor[$doctorList->id] == 'true') checked @endif
                                                        data-checkboxes="mygroup" class="custom-control-input"
                                                        id="checkbox-{{ $key }}">
                                                    <label for="checkbox-{{ $key }}"
                                                        class="custom-control-label">{{ $doctorList->full_name }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                    @endif
                </div>
                <div class="col-12 mt-4">
                    <h4>ترتیب نمایش این مطب در لیست مطب ها</h4>
                    <hr style="opacity: 0.9">
                    <div class="form-group">
                        <label for="order">ترتیب نمایش :</label>
                        <input type="number" class="form-control" id="order" wire:model='order'
                            placeholder="اواویت نمایش مربوط به این مطب در صورتی که چند مطب داشته باشید را به عدد وارد کنید.">
                    </div>
                </div>
                <div class="col-12 mt-5">
                    <div class="checkbox">
                        <div class="custom-checkbox custom-control">
                            <input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" wire:model='status'
                                id="checkbox-1">
                            <label for="checkbox-1" class="custom-control-label ">فعال</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <button class="btn btn-success">ذخیره اطلاعات</button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
    <style>
        label {
            font-size: medium;
        }

        p {
            font-size: medium;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ admin_asset('js/leaf.js') }}"></script>
    <script>
        $(document).ready(function() {});
    </script>
@endpush
