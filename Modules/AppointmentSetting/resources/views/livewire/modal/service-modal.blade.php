<div>
    <div class="modal fade" id="setDocOrsectionMOdal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    @if (isset($search))
                        <button class="btn btn-secondary" wire:click='ignoreSearch'>نمایش همه</button>
                    @else
                        <h5 class="modal-title" id="staticBackdropLabel">تغییر پزشک</h5>
                    @endif
                    <div class="modal-title">
                        <div class="input-group">
                            <input type="text" class="form-control"
                                @if ($step == 1) placeholder="نام خانوادگی پزشک"   wire:model='search'
                               @elseif($step == 2)
                               placeholder="نام بخش" @endif>
                            <button wire:click='searchDocAndSection'
                                class="btn ripple btn-info text-fixed-white input-group-text border-0" type="button">
                                <span wire:target='searchDocAndSection' wire:loading.class='disabeled'>جست و جو</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @if (isset($fetchData['sections']))
                            @foreach ($fetchData['sections'] as $key => $section)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- refresh data of the page with selection of the doctor with wire:click --}}
                                            <a href="#" wire:click='selectSection({{ $section->id }})'>
                                                <span style="font-size: medium">{{ $section->title }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click='dismisMOdal'
                        data-bs-dismiss="modal">بیخیال</button>
                </div>
            </div>
        </div>
    </div>
</div>
