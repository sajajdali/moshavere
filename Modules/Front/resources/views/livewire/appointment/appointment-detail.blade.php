<div class="mt-5">
    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-5"> نوبت شما در حالت <span class="text-info">{{ $fetchData['stauts'] }}</span> میباشد </h4>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
        </div>
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
                                    <th><strong>{{ $fetchData['app']->user->fullName }}</strong></th>
                                </tr>
                                <tr>
                                    <th>نام بخش</th>
                                    <th><strong>{{ $fetchData['app']->service->title }}</strong></th>
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
                                    <th><strong>{{ verta($fetchData['app']->visited_date)->format('H:i') }}</strong>
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
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">آدرس</h3>
                </div>
                <div class="card-body">
                    @if (isset($fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION]))
                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <div id="testmap"></div>
                                <div class="h-500" id="mapdiv"></div>
                            </div>

                            <hr class="opacity-50">
                            <div class="col-12">
                                <h5>مسیر یابی</h5>
                            </div>
                            <div class="col-md-12 d-flex justify-content-around">
                                <a href="https://maps.google.com/maps?daddr={{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LAT] }},{{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LNG] }}&amp;ll="
                                    class="navigation-button google-map-button">مسیریابی با گوگل مپ</a>
                                <a href="https://www.waze.com/ul?ll={{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LAT] }},{{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LNG] }}&navigate=yes"
                                    class="navigation-button waze-button">مسیریابی با اپلیکیشن ویز</a>

                                <a href=" https://neshan.org/maps/routing/car/destination/{{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LAT] }},{{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION][Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION_LNG] }}" class="navigation-button neshan-button">مسیریابی با اپلیکیشن نشان</a>
                            </div>
                        </div>
                    @endif
                    @if (isset($fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]))
                        <h5 class="mt-4">آدرس نوشتاری:</h5>
                        <p class="ms-2 mt-1">
                            {{ $fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@push('styles')
    <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/mapp.min.css">
    <link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/fa/style.css">
@endpush
@push('scripts')
    <script type="text/javascript" src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.min.js"></script>
    <script src="{{ admin_asset('js/mapp.min.js') }}"></script>
    <script src="{{ admin_asset('js/mapp.env.js') }}"></script>
    <script>
        $(document).ready(function() {
            if ({{ isset($fetchData['place']->detail[Modules\Place\app\Models\Place::DETAIL_KEY_LOCATION]) }}) {
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
    </script>
@endpush
