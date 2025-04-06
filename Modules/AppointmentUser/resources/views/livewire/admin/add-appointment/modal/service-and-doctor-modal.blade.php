<div class="modal fade" id="changeDocmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    wire:ignore.self aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                @if (!empty($search['doctor']) || !empty($search['service']))
                    <button class="btn btn-secondary" wire:click='ignoreSearch'>نمایش همه</button>
                @else
                    @if ($step == 1)
                        <h5 class="modal-title" id="staticBackdropLabel">انتخاب پزشک</h5>
                    @elseif($step == 2)
                        <h5 class="modal-title" id="staticBackdropLabel">انتخاب بخش</h5>
                    @elseif($step == 3)
                        <h5 class="modal-title" id="staticBackdropLabel">انتخاب مطب</h5>
                    @endif
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
                                        <a href="#" wire:click="doctorSelected({{ $doctor->id }})">
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
                        @error('selectService')
                            <div class="col-md-12 alert alert-danger fade show" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                        @if ($fetchData['service']->isNotEmpty())
                            @if ($fetchData['service']->first()->isParentCategoryExists($fetchData['service']))
                                @foreach ($fetchData['service'] as $key => $service)
                                    @if (!isset($service->parent_id))
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <span href="#"
                                                            @if ($service->hasChild()) type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseExample-{{ $service->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="collapseExample-{{ $service->id }}" @endif>
                                                            <span style="font-size: medium">
                                                                @if ($service->hasChild())
                                                                    <i class="fa fa-plus me-1 text-primary"
                                                                        aria-hidden="true"></i>
                                                                @endif
                                                                {{ $service->title }}
                                                            </span>
                                                        </span>
                                                        <label class="rdiobox" for="rdio-{{ $service->id }}">
                                                            <input name="rdio" type="radio"
                                                                value="{{ $service->id }}" wire:model='form.service'
                                                                id="rdio-{{ $service->id }}">
                                                            <span>انتخاب</span>
                                                        </label>
                                                    </div>
                                                    <div class="collapse" id="collapseExample-{{ $service->id }}">
                                                        <div class="card mt-3">
                                                            @foreach ($fetchData['service'] as $index => $subService)
                                                                @if (isset($subService->parent_id) && $subService->parent_id == $service->id)
                                                                    <div class="card-body">
                                                                        <div class="d-flex justify-content-between">
                                                                            <span>
                                                                                <i class="fa fa-minus me-1 text-primary"
                                                                                    aria-hidden="true"></i>
                                                                                <span
                                                                                    style="font-size: medium">{{ $subService->title }}</span>
                                                                            </span>
                                                                            <label class="rdiobox"
                                                                                for="rdio-{{ $subService->id }}">
                                                                                <input name="rdio" type="radio"
                                                                                    value="{{ $subService->id }}"
                                                                                    wire:model='form.service'
                                                                                    id="rdio-{{ $subService->id }}">
                                                                                <span>انتخاب</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                {{-- no parent service  --}}
                                @foreach ($fetchData['service'] as $key => $service)
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <span>
                                                        <span style="font-size: medium">
                                                            {{ $service->title }}
                                                        </span>
                                                    </span>
                                                    <label class="rdiobox" for="rdio-{{ $service->id }}">
                                                        <input name="rdio" type="radio"
                                                            value="{{ $service->id }}" wire:model='form.service'
                                                            id="rdio-{{ $service->id }}">
                                                        <span>انتخاب</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div class="alert alert-avatar alert-warning alert-dismissible">
                                نتیجه ای یافت نشد!!!
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
                @if ($step == 2)
                    <button type="button" class="btn btn-secondary" wire:click='selectSection'
                        wire:target='selectSection' wire:loading.class='btn-loading bg-gray'>ادامه</button>
                @endif
                <button type="button" class="btn btn-secondary" wire:click='closeModal'
                    data-bs-dismiss="modal">بیخیال</button>
            </div>
        </div>
    </div>
</div>
