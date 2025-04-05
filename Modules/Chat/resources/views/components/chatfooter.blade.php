@if (isset($this->chatList?->first()?->first()?->chat) &&
        $this->chatList?->first()?->first()?->chat?->status == Modules\Chat\Enum\ChatStatusEnum::CLOSED)
    <div class="col-md-12 alert alert-primary fade show mt-4 ms-3" role="alert">
        چت بسته شده است!
    </div>
@else
    <div>
        <button
            class="btn @if (isset($form['capturedPic'])) btn-success @else  btn-light @endif mx-3 d-flex justify-content-center p-1 py-2 mt-2"
            id="cameraButton" @if (isset($form['capturedPic'])) disabled @endif>
            @if (isset($form['capturedPic']))
                <i class="fa fa-check" aria-hidden="true"></i>
            @else
                <i class="fa fa-camera" aria-hidden="true"></i>
            @endif
        </button>
        <input type="file" accept="image/*" capture="environment" id="cameraInput" wire:model='form.capturedPic'
            style="display:none;" />
    </div>
    <!-- Camera Button -->
    <div class="w-100 mt-5">
        @if (isset($fetchData['messageTemplate']))
            <div class="col-12 mt-5">
                <div class="form-group">
                    <select wire:igonre.self class="form-control select2-show-search form-select"
                        data-placeholder="متن های اماده...">
                        <option label="متن ثابت.."></option>
                        @foreach ($fetchData['messageTemplate'] as $msgTemp)
                            <option value="{{ $msgTemp->id }}">
                                {{ $msgTemp->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
        <textarea rows="3" class="form-control mt-5" placeholder="متن پیام شما..." wire:model="chatMessage"></textarea>
    </div>
    {{-- send File modal --}}
    <div class="mt-5 ms-5 ms-sm-0">
        <button type="button" wire:click="sendMessage" wire:loading.class="btn btn-light btn-loading"
            wire:loading.class.remove="btn-primary" class="btn btn-icon  btn-primary brround mb-2">
            <i class="fa fa-paper-plane-o"></i>
        </button>
        <button data-bs-target="#file-selector-modal" data-bs-toggle="modal"
            class="btn btn-light me-3 d-flex justify-content-center p-1 py-2" href="javascript:void(0)">
            @if (isset($form['file']))
                <i class="fa fa-check" aria-hidden="true"></i>
            @else
                <i class="fa fa-file" aria-hidden="true"></i>
            @endif
        </button>
    </div>
    <nav class="nav">
    </nav>
@endif
