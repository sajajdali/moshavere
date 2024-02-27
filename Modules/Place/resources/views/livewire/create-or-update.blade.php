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
                        <input type="text" wire:model='form.name'
                            class="form-control @error('form.name') is-invalid @enderror"
                            aria-describedby="basic-addon3">
                        @error('form.name')
                            <div id="validationName" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div id="testmap"></div>
                    <div class="card">
                        <div class="card-header">
                            محل مطب بر روی نقشه
                        </div>
                        <div class="card-body" wire:ignore>
                            <div class="h-500" id="mapdiv"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 d-flex flex-column justify-content-start flex-sm-row justify-content-sm-between">
                    <h3>شماره مطب</h3>
                    <div>
                        <button type="button" wire:click="addCounter"
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
                        @if ($counter > 1)
                            <button type="button" wire:click="removeCounter"
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
                @for ($i = 0; $i < $counter; $i++)
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group ">
                                <label for="exampleInputPassword2">شماره تماس :</label>
                                <input type="text" class="form-control" id="placenumber-{{ $i }}"
                                    wire:model='form.numbers.{{ $i }}' placeholder="شماره تماس">
                            </div>
                        </div>
                    </div>
                @endfor
                <div class="col-12">
                    @if (!empty($fetchData['doctors']))
                        <div class="row mt-5">
                            <h4>لیست پزشکان</h4>
                            <hr style="opacity: 0.9">
                            <div class="row">
                                <p class="text-muted my-1">لطفا پزشکان مرتبط با این مطب را انتخاب کنید</p>
                                @foreach ($fetchData['doctors'] as $key => $doctorList)
                                    @if ($doctorList->id == 2)
                                        @continue
                                    @endif
                                    <div class="col-md-4">
                                        <div class="form-group mt-2">
                                            <div class="checkbox">
                                                <div class="custom-checkbox custom-control">
                                                    <input type="checkbox"
                                                        wire:model='form.doctors.{{ $doctorList->id }}'
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
                    @endif
                </div>
                <div class="col-12 mt-4">
                    <h4>ترتیب نمایش این مطب در لیست مطب ها</h4>
                    <hr style="opacity: 0.9">
                    <div class="form-group">
                        <label for="order">ترتیب نمایش :</label>
                        <input wire:key="prioruty" type="text" class="form-control" id="placeorder"
                            wire:model='form.priority'
                            placeholder="اواویت نمایش مربوط به این مطب در صورتی که چند مطب داشته باشید را به عدد وارد کنید.">
                    </div>
                </div>
                <div class="col-12 mt-5">
                    <div class="checkbox">
                        <div class="custom-checkbox custom-control">
                            <input type="checkbox" data-checkboxes="mygroup" class="custom-control-input"
                                wire:model='form.active' id="activeCheckbox">
                            <label for="activeCheckbox" class="custom-control-label ">فعال</label>
                        </div>
                    </div>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger mt-4" role="alert">
                        <p class="text-danger"><strong>خطا!!</strong>لطفا اخطارهای بوجود امده را در قسمت بالا برطرف کنید
                        </p>
                    </div>
                @endif
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary" wire:loading.class="bg-gray btn-loading disabled"
                        wire:click="updateOrCreate">
                        @if ($isEdited)
                            ویرایش اطلاعات
                        @else
                            ‌ذخیره اطلاعات
                        @endif
                    </button>
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
    <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/mapp.min.css">
    <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/fa/style.css">
@endpush
@push('scripts')
    <script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.min.js"></script>
    <script src="{{ admin_asset('js/mapp.min.js') }}"></script>
    <script src="{{ admin_asset('js/mapp.env.js') }}"></script>
    <script>
        $(document).ready(function() {
            var crosshairIcon = {
                iconUrl: 'https://nobat.selakteb.com/images/marker-icon.png',
                iconSize: [25, 41], // size of the icon
                iconAnchor: [12, 55], // point of the icon which will correspond to marker's location
            };
            var app = new Mapp({
                element: '#mapdiv',
                presets: {
                    latlng: {
                        lat: 35.786442157435,
                        lng: 51.498822688591,
                    },
                    zoom: 12
                },
                apiKey: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjAwYjU3ZjUzYjk4OThlOGZlYmZlMjJhODc3NjM3ZGJlYzE5OGZmYzAzMmQ1MDdmODcxY2M5ZThlODM4N2ZkNjRiNzM3MWVlOWFmYjk1MWJlIn0.eyJhdWQiOiIyNjA5NSIsImp0aSI6IjAwYjU3ZjUzYjk4OThlOGZlYmZlMjJhODc3NjM3ZGJlYzE5OGZmYzAzMmQ1MDdmODcxY2M5ZThlODM4N2ZkNjRiNzM3MWVlOWFmYjk1MWJlIiwiaWF0IjoxNzA3NTQ3MzUyLCJuYmYiOjE3MDc1NDczNTIsImV4cCI6MTcxMDA1Mjk1Miwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.G_8eZJV03f9krGyP_nvkNXn9nODDK8VAf-lI9ESuZBPobkrPCceG02Y-nzosNEilZzZSGqW2yBjZE6PMZVcf81T53bMAlo6DmPaDGoqjAO88ZrL1tvhQ7KPBDBSkA4oODvSVGtA071CWpvUd7xdzoy0h-mEGmIdkY3Cs3MkPbCltrYXaK1LuDSE-4fz2HHeyswUAc8IHkoxKcze-FACfT_uifSijX6rfYfG4k9uXTNap41rKvmqZ1c4DSXkkHTc_2Pit1WUAX-y-ALxKtt22h8GQPv4FV-Bd_PJHp9g6U93QmKaeJdC0PCcnVOJHhHfGtme7I0zYAfmtgqDC5j2CAw'
            });
            if ({{ array_key_exists('loc', $form) }}) {
                app.addMarker({
                    latlng: {
                        lat: {{ (float) $form['loc']['lat'] }},
                        lng: {{ (float) $form['loc']['lng'] }},
                    },
                    icon: crosshairIcon,
                    popup: false,
                    pan: false,
                    draggable: true,
                    history: false
                });
            }
            app.addVectorLayers();
            app.addZoomControls();
            app.map.on('click', function(e) {
                var marker = app.addMarker({
                    latlng: {
                        lat: e.latlng.lat,
                        lng: e.latlng.lng,
                    },
                    icon: crosshairIcon,
                    popup: false,
                    pan: false,
                    draggable: true,
                    history: false
                });
                var lat = e.latlng.lat;
                var lon = e.latlng.lng;
                @this.set('form.place.loc.lat', e.latlng.lat);
                @this.set('form.place.loc.lng', e.latlng.lng);
            });
        });
    </script>
@endpush
