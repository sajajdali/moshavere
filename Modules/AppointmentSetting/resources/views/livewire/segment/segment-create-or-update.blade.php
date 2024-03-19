<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">
                {{ $isEdited ? 'ویرایش ' : 'افزودن  جدید' }}
            </h1>
        </div>
    </div>

    @include('admin::layouts.components.alert')

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">
                        {{ $isEdited ? 'ویرایش ' : 'افزودن  جدید' }}
                    </h3>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent>
                        <div class="form-row">
                            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
                                <label for="title">نام گروه بندی (الزامی)</label>
                                <input type="text" class="form-control @error('form.title') is-invalid @enderror"
                                       id="name"
                                       wire:model="form.title" placeholder="نام">
                                @error('form.title')
                                <div id="title"
                                     class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label for="parent_id">نوع انتخاب</label>
                                <select wire:model="form.multiple_choice"
                                        class="form-control @error('form.food_unit_id') is-invalid @enderror"
                                        data-placeholder="انتخاب نشده" id="food_unit_id">
                                    <option value="1">تک انتخابی</option>
                                    <option value="0">انخاب چند بخش</option>

                                </select>
                                @error('form.food_unit_id')
                                <div id="validationuserName"
                                     class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-7">
                            <hr>
                        </div>
                        <div class="d-flex justify-content-end mb-5">
                            <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="اضافه کردن بخش بندی" wire:click="addItem()"
                                    class="btn btn-info rounded-pill text-center">
                                        <span wire:loading.remove wire:target="addItem()"> <span
                                                class="d-flex align-item-center"><i class="fa fa-2x fa-plus-circle"
                                                                                    aria-hidden="true"></i>
                                                <span class="ms-1">اضافه کردن
                                                    بخش بندی</span></span></span>
                                <span wire:loading wire:target="addItem()">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                        </div>
                        </button>
                        @for($i = 0 ; $i <= $form['count_items'] ; $i++)
                            <div class="card card-body position-relative">
                                <span class="badge rounded-pill bg-success position-absolute"
                                      style="top : -13px;right: -8px">{{$i + 1}}</span>
                                @if($i > 0)

                                    <div class="d-flex justify-content-end ">
                                        <button aria-label="button" type="button"
                                                wire:click="removeItem({{$i}})"
                                                class="btn btn-icon btn-danger rounded-pill btn-wave waves-effect waves-light">

                                            <span wire:loading.remove wire:target="removeItem({{$i}})"> <span
                                                    class="d-flex align-item-center"><i class="fa fa-2x fa-minus-circle"
                                                                                        aria-hidden="true"></i>
                                                <span class="ms-1"></span>حذف</span></span>
                                            <span wire:loading wire:target="removeItem({{$i}})">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                            </div>
                                        </span>
                                        </button>
                                    </div>
                                @endif

                                <div class="form-row">
                                    @if(isset($form['items'][$i]['id']))
                                        <input type="hidden" wire:model="form.items.{{$i}}.id">
                                    @endif
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <label>نام بخش یا ناحیه</label>
                                        <input type="text" class="form-control @error('form.items.'.$i.'.title') is-invalid @enderror"

                                               wire:model="form.items.{{$i}}.title"
                                               placeholder="نام بخش یا ناحیه">
                                        @error('form.items.'.$i.'.title')
                                        <div
                                            class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <label>مبلغ (ریال)</label>
                                        <input type="number"
                                               class="form-control @error('form.items.'.$i.'.price') is-invalid @enderror"

                                               wire:model="form.items.{{$i}}.price" dir="ltr" placeholder="مبلغ">
                                        @error('form.items.'.$i.'.price')
                                        <div
                                            class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <label for="priority">اولویت نمایش</label>
                                        <input type="number"
                                               class="form-control @error('form.items.'.$i.'.priority') is-invalid @enderror"

                                               wire:model="form.items.{{$i}}.priority" dir="ltr"
                                               placeholder="اولویت نمایش">
                                        @error('form.items.'.$i.'.priority')
                                        <div
                                            class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <label for="priority">زمان مورد نیاز</label>
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control @error('form.items.'.$i.'.time') is-invalid @enderror"
                                                   id="basic-url" dir="ltr" aria-describedby="basic-addon3" wire:model='form.items.{{$i}}.time'>
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon3">مدت زمان به دقیقه</span>
                                            </div>
                                        </div>
                                        @error('form.items.'.$i.'.time')
                                        <div
                                            class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <label>وضعیت نمایش در سایت</label>

                                        <select wire:model="form.items.{{$i}}.display_on_site"
                                                class="form-control @error('form.items.'.$i.'.display_on_site') is-invalid @enderror"
                                                data-placeholder="انتخاب نشده" id="food_unit_id">
                                            <option value="1">نمایش در سایت</option>
                                            <option value="0">عدم نمایش در سایت</option>

                                        </select>
                                        @error('form.items.'.$i.'.display_on_site')
                                        <div
                                            class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                </div>
                            </div>
                        @endfor
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 m-3 ms-0">
                            <div class="main-toggle-group d-sm-flex align-items-center ms-0">

                                <div
                                    class="toggle toggle-lg toggle-success my-1 @if(isset($form['active']) && $form['active']) on @else off @endif"
                                    id="status">
                                    <span></span>
                                </div>
                                <div class="ms-2">
                                    <p class="text-muted m-0">وضعیت بخش بندی فعال باشد؟</p>
                                </div>
                            </div>
                        </div>
                        @error('*')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <span class="alert-inner--text">لطفا ارور های موجود در فرم بالا را برطرف کنید</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        @enderror

                        <button type="submit" class="btn btn-primary" wire:loading.class="bg-gray btn-loading disabled"
                                wire:click="updateOrCreate">
                            @if($isEdited)
                                ویرایش گروه بندی
                            @else
                                ایجاد گروه بندی
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')

    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush

@push('scripts')
    <script>

        $(document).ready(function () {

            $('#status').on('click', function () {
            @this.set('form.active', $('#status').hasClass('on'))
                ;
            });



        });
    </script>
@endpush

