<div>
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog" wire:key='{{time()}}'>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                      انتخاب سرویس
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        @if (isset($fetchData['ServiceList']) && $fetchData['ServiceList']->isNotEmpty())
                        <div class="d-flex flex-column g-3">
                                @foreach ($fetchData['ServiceList'] as $service)
                                    <a wire:click='serviceSelected({{ $service->id }})'
                                        class="badge bg-primary-gradient my-1 p-5 text-white"
                                        style="font-size: medium !important ; cursor: pointer;">{{ $service->title }}</a>
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بیخیال</button>
                </div>
            </div>
        </div>
    </div>
</div>
