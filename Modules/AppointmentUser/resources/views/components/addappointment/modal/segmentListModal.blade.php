<div>
    <div class="modal effect-flip-horizontal fade" id="segmentModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" wire:key='{{ time() }}'>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        انتخاب بخش بندی
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-y: scroll !important; max-height: 400px;">
                    <div>
                        @if (isset($fetchData['segments']['items']) && $fetchData['segments']['items']->isNotEmpty())
                            @if ($fetchData['segments']['is_one_choice'])
                                <div class="d-flex flex-column g-3">
                                    @foreach ($fetchData['segments']['items'] as $segment)
                                        <a wire:click='segmentSelected({{ $segment->id }})'
                                            class="badge bg-info-gradient my-1 p-5 text-white"
                                            style="font-size: medium !important ; cursor: pointer;">{{ $segment->title }}</a>
                                    @endforeach
                                </div>
                            @else
                            <h5 class="mb-4">
                                لطفا بخش بندی های مورد نظر را انتخاب کنید:
                            </h5>
                                @foreach ($fetchData['segments']['items'] as $segment)
                                    <div class="d-flex flex-column g-3 my-2 @if($loop->even) bg-gray-100 @endif p-2 rounded-top rounded-bottom">
                                        <div class="col-lg-4">
                                            <label class="ckbox" for="{{ $loop->index }}-segment-items">
                                                <input type="checkbox" wire:model='form.segmentSelectedIem.{{ $segment->id }}' id="{{ $loop->index }}-segment-items"><span
                                                    class="fs-6">{{ $segment->title }}</span>
                                            </label>
                                        </div>
                                        {{-- <a wire:click='segmentSelected({{ $segment->id }})'
                                            class="badge bg-danger-gradient my-1 p-5 text-white"
                                            style="font-size: medium !important ; cursor: pointer;">{{ $segment->title }}</a> --}}
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div class="alert alert-warning" role="alert">
                                <span class="alert-inner--icon me-2"><i class="fe fe-info"></i></span>
                                <span class="alert-inner--text"><strong>زیر بخش ها تعریف نشده اند</strong>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" wire:click='segmentSelected'>ثبت نوبت</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بیخیال</button>
                </div>
            </div>
        </div>
    </div>
</div>
