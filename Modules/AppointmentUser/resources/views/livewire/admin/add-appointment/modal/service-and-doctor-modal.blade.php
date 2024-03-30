<div class="modal fade" id="changeDocmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    wire:ignore.self aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                @if (!empty($search['doctor']) || !empty($search['service']))
                    <button class="btn btn-secondary" wire:click='ignoreSearch'>نمایش همه</button>
                @else
                    <h5 class="modal-title" id="staticBackdropLabel">تغییر پزشک</h5>
                @endif
                <div class="modal-title">
                    <div class="input-group">
                        @if ($step == 1)
                            <input type="text" class="form-control" wire:model='search.doctor'
                                placeholder="نام پزشک">
                        @elseif($step == 2)
                            <input type="text" class="form-control" wire:model='search.service'
                                placeholder="نام بخش">
                        @endif
                        <button wire:click='searchDocAndSection'
                            class="btn ripple btn-info text-fixed-white input-group-text border-0" type="button">
                            <span wire:target='searchDocAndSection' wire:loading.class='disabeled btn-loading'>جست و
                                جو</span>
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
                        @foreach ($doctors as $doctor)
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <a href="#" wire:click="showRelatedSection({{ $doctor->id }})">
                                            <i class="fa fa-user-md fa-2x text-primary me-3" aria-hidden="true"></i>
                                            <span style="font-size: medium">{{ $doctor->full_name }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($step == 2)
                    <div class="row">
                        @if ($fetchData['service']->isNotEmpty())
                            @foreach ($fetchData['service'] as $key => $service)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- refresh data of the page with selection of the doctor with wire:click --}}
                                            <a href="#" wire:click='selectSection({{ $service->id }})'>
                                                <span style="font-size: medium">{{ $service->title }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-avatar alert-warning alert-dismissible">
                                برای این پزشک هیچ بخشی تعریف نشده است!!
                            </div>
                        @endif
                    </div>
                @elseif($step == 3)
                    <div class="row">
                        @if ($fetchData['places']->isNotEmpty())
                            @foreach ($fetchData['places'] as $key => $place)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- refresh data of the page with selection of the doctor with wire:click --}}
                                            <a href="#" wire:click='selectplace({{ $place->id }})'>
                                                <span style="font-size: medium">{{ $place->title }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-avatar alert-warning alert-dismissible">
                                برای این پزشک هیچ بخشی تعریف نشده است!!
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click='closeModal'
                    data-bs-dismiss="modal">بیخیال</button>
            </div>
        </div>
    </div>
</div>
