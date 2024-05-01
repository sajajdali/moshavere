<div class="mt-5">
    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-5"> نوبت شما در حالت <span
                    class=" badge {{ $fetchData['stauts']['color'] }} rounded-pill">{{ $fetchData['stauts']['name'] }}</span>
                میباشد .</h4>
        </div>
    </div>
    <div class="row">
        @if ($fetchData['stauts']['payment'])
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <p class="text-bold font-xl">
                            برای فعال سازی نوبت ، مبلغ {{ number_format($fetchData['stauts']['price']) }} را پرداخت
                            نمایید :
                        </p>
                        <button class="btn btn-success px-3 py-2 font-xl">پرداخت و فعال سازی </button>
                    </div>
                </div>
            </div>
        @endif
        @if ($fetchData['cancel'])
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom d-flex justify-content-between">
                        <h3 class="card-title">مدیریت نوبت</h3>
                        <a class=" btn btn-danger text-bold confirm_swal_alert " data-label="نوبت"
                            data-description= "{{ setting(Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_CANCEL_DESCRIPTION) ?? 'از کنسل کردن نوبت مطمعن هستید؟ ' }}"
                            data-title="توجه!" data-confirmbtn="بله کنسل شود" data-action="cancelWithSms"
                            data-id="{{ $this->fetchData['app']->id }}" data-id="{{ $this->fetchData['app']->id }}"
                            href="">
                            <i class="fa fa-times" aria-hidden="true"></i>
                            کنسل
                            کردن
                        </a>
                    </div>
                </div>
            </div>
        @endif
        @if ($fetchData['description'])
            <div class="col-lg-12">
                <div class="card custom-card bg-warning-light">
                    <div class="card-body">
                        <div class="text-center">
                            <h3 class="card-title">توضیحات نوبت</h3>
                            <hr class="bg-warning opacity-25">
                        </div>
                        <p>
                            {!! nl2br($fetchData['description']) !!}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">جزئیات نوبت</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table border text-nowrap text-md-nowrap table-striped">
                            <tbody>
                                <tr>
                                    <th>نام و نام خانوادگی</th>
                                    <th class="border border-left">
                                        <strong>{{ $fetchData['app']->user->fullName }}</strong>
                                    </th>
                                </tr>
                                <tr>
                                    <th>نام بخش</th>
                                    <th><strong>{{ $fetchData['app']->service?->title ?? '---' }}</strong></th>
                                </tr>
                                <tr>
                                    <th>پزشک شما</th>
                                    <th><strong>{{ $fetchData['app']->doctor->fullName }}</strong></th>
                                </tr>
                                <tr>
                                    <th>تاریخ نوبت</th>
                                    <th><strong>{{ verta($fetchData['app']->visited_date)->format('d F') }}</strong>
                                    </th>
                                </tr>
                                <tr>
                                    <th>ساعت نوبت</th>
                                    <th>
                                        @if ($this->fetchData['stauts']['enum'] != Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL)
                                            <strong>{{ verta($fetchData['app']->visited_date)->format('H:i') }}</strong>
                                        @else
                                            <span class="badge bg-danger rounded-pill">کنسل شده</span>
                                        @endif
                                    </th>
                                </tr>
                                <tr>
                                    <th>شماره پیگیری نوبت</th>
                                    <th><strong>{{ $fetchData['app']->tracking_code }}</strong></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @if (isset($fetchData['place']))
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom d-flex justify-content-between">
                        <h3 class="card-title">آدرس</h3>
                        <div>
                            @if ($fetchData['socailmedia']['status'])
                                @if ($fetchData['socailmedia']['telegram'])
                                    <a href="{{ $fetchData['socailmedia']['telegram'] }}">
                                        <i class="fa fa-telegram fa-2x me-2 text-primary" aria-hidden="true"></i>
                                    </a>
                                @endif
                                @if ($fetchData['socailmedia']['whatsapp'])
                                    <a href="{{ $fetchData['socailmedia']['whatsapp'] }}">
                                        <i class="fa fa-whatsapp fa-2x me-2 text-success " aria-hidden="true"></i>

                                    </a>
                                @endif
                                @if ($fetchData['socailmedia']['instagram'])
                                    <a href="{{ $fetchData['socailmedia']['instagram'] }}">
                                        <i class="fa fa-instagram fa-2x  text-warning" aria-hidden="true"></i>
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if (isset($fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION]))
                            <div class="row mb-4">
                                <div class="col-lg-12">
                                    <div id="testmap"></div>
                                    <div class="h-500" id="mapdiv"></div>
                                </div>
                                <div class="col-12 mt-5">
                                    <h5>
                                        <i class="fa fa-location-arrow me-1" aria-hidden="true"></i>
                                        مسیر یابی
                                    </h5>
                                    <hr class="text-light opacity-50">
                                </div>
                                <div class="col-md-12 d-flex justify-content-around">
                                    <a href="https://maps.google.com/maps?daddr={{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LAT] }},{{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LNG] }}&amp;ll="
                                        class="navigation-button google-map-button">مسیریابی با گوگل مپ</a>
                                    <a href="https://www.waze.com/ul?ll={{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LAT] }},{{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LNG] }}&navigate=yes"
                                        class="navigation-button waze-button">مسیریابی با اپلیکیشن ویز</a>

                                    <a href=" https://neshan.org/maps/routing/car/destination/{{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LAT] }},{{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LNG] }}"
                                        class="navigation-button neshan-button">مسیریابی با اپلیکیشن نشان</a>
                                </div>
                        @endif
                        <div class="col-12">
                            @if (isset($fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]))
                                <h5 class="mt-4">
                                    <i class="fa fa-address-card-o me-1" aria-hidden="true"></i>
                                    آدرس نوشتاری:
                                </h5>
                                <hr class="text-light opacity-50">

                                <p class="ms-2 mt-1">
                                    {{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}
                                </p>
                            @endif
                        </div>
                        <div class="col-12">
                            @if (isset($fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]))
                                <h5 class="mt-4">
                                    <i class="fa fa-phone me-1" aria-hidden="true"></i>
                                    شماره تماس:
                                </h5>
                                <hr class="text-light opacity-50">

                                <p class="ms-2 mt-1">
                                    {{ implode(',', $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_NUMBERS]) }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
</div>
@push('styles')
    <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/mapp.min.css">
    <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/fa/style.css">
@endpush
@push('scripts')
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/admin.sweetalert.js') }}"></script>
    <script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.min.js"></script>
    <script src="{{ admin_asset('js/mapp.min.js') }}"></script>
    <script src="{{ admin_asset('js/mapp.env.js') }}"></script>
    <script>
        @if (isset($fetchData['place']))
            $(document).ready(function() {
                if (
                    {{ isset($fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION]) }}) {
                    var crosshairIcon = {
                        iconUrl: "{{ front_asset('image/marker-icon.png') }}",
                        iconSize: [25, 41], // size of the icon
                        iconAnchor: [12, 55], // point of the icon which will correspond to marker's location
                    };
                    var placeLat =
                        {{ (float) $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LAT] }};
                    var placeLng =
                        {{ (float) $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LNG] }};
                    var app = new Mapp({
                        element: '#mapdiv',
                        presets: {
                            latlng: {
                                lat: placeLat,
                                lng: placeLng,
                            },
                            zoom: 14
                        },
                        apiKey: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjAwYjU3ZjUzYjk4OThlOGZlYmZlMjJhODc3NjM3ZGJlYzE5OGZmYzAzMmQ1MDdmODcxY2M5ZThlODM4N2ZkNjRiNzM3MWVlOWFmYjk1MWJlIn0.eyJhdWQiOiIyNjA5NSIsImp0aSI6IjAwYjU3ZjUzYjk4OThlOGZlYmZlMjJhODc3NjM3ZGJlYzE5OGZmYzAzMmQ1MDdmODcxY2M5ZThlODM4N2ZkNjRiNzM3MWVlOWFmYjk1MWJlIiwiaWF0IjoxNzA3NTQ3MzUyLCJuYmYiOjE3MDc1NDczNTIsImV4cCI6MTcxMDA1Mjk1Miwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.G_8eZJV03f9krGyP_nvkNXn9nODDK8VAf-lI9ESuZBPobkrPCceG02Y-nzosNEilZzZSGqW2yBjZE6PMZVcf81T53bMAlo6DmPaDGoqjAO88ZrL1tvhQ7KPBDBSkA4oODvSVGtA071CWpvUd7xdzoy0h-mEGmIdkY3Cs3MkPbCltrYXaK1LuDSE-4fz2HHeyswUAc8IHkoxKcze-FACfT_uifSijX6rfYfG4k9uXTNap41rKvmqZ1c4DSXkkHTc_2Pit1WUAX-y-ALxKtt22h8GQPv4FV-Bd_PJHp9g6U93QmKaeJdC0PCcnVOJHhHfGtme7I0zYAfmtgqDC5j2CAw'
                    });
                    app.addVectorLayers();
                    app.addZoomControls();
                    app.addMarker({
                        latlng: {
                            lat: placeLat,
                            lng: placeLng,
                        },
                        icon: crosshairIcon,
                        popup: false,
                        pan: false,
                        draggable: true,
                        history: false
                    });
                }
            });
        @endif
    </script>
@endpush
