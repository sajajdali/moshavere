<div class="modal fade" id="ServiceAndPlaceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    wire:ignore.self aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                @if (isset($search))
                    <button class="btn btn-secondary" wire:click='ignoreSearch'>نمایش همه</button>
                @else
                    <h5 class="modal-title" id="staticBackdropLabel">
                        @if ($step == 1)
                            <span>انتخاب مطب</span>
                        @elseif($step == 2)
                            <span>انتخاب بخش</span>
                        @endif
                    </h5>
                @endif
                <div class="modal-title">
                    <div class="input-group">
                        <input type="text" class="form-control"
                            @if ($step == 1) placeholder="عنوان مطب"   wire:model='search'
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
                @if ($step == 1)
                    <div class="row d-flex justify-content-center align-items-center">
                        <div class="spinner-border text-primary  my-5 " role="status" wire:loading>
                        </div>
                    </div>
                    <div class="row" wire:loading.remove>
                        @foreach ($ServiceOrPlace as $place)
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <a href="#" wire:click="showRelatedSection({{ $place->id }})">
                                            <i class="fa fa-hospital-o fa-2x text-primary me-3" aria-hidden="true"></i>
                                            <span style="font-size: medium">{{ $place->title }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($step == 2)
                    <div class="row">
                        @foreach ($ServiceOrPlace as $key => $service)
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <a href="#" wire:click='selectSection({{ $service->id }})'>
                                            <span style="font-size: medium">{{ $service->title }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click='dismisMOdal'
                    data-bs-dismiss="modal">بیخیال</button>
            </div>
        </div>
    </div>
</div>
