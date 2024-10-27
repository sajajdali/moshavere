<div>
    <div class="page-header d-flex">
        <div >
            <h1 class="page-title">چت آنلاین</h1>
        </div>
        <a href="{{route('front.user.profile')}}" class="btn btn-primary" class="mb-3">
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
            بازگشت
        </a>
    </div>
    @isset($msg)
        <div class="col-md-12 alert alert-success fade show" role="alert">
            <i class="fa fa-check-circle-o me-2" aria-hidden="true"></i>
            {{ $msg }}
        </div>
    @endisset
    <div wire:loading>
        <div class="loading-overlay d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" role="status">
            </div>
        </div>
    </div>
    <div id="loading-spinner" class="d-none">
        <div class="loading-overlay d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" role="status">
            </div>
        </div>
    </div>
    <!-- PAGE-HEADER END -->
    @include('admin::layouts.components.alert')
    @error('success')
        <div class="col-md-12 alert alert-success fade show" role="alert">
            <i class="fa fa-check-circle-o me-2" aria-hidden="true"></i>
            {{ $message }}
        </div>
    @enderror
    @push('styles')
        <style>
            .overflow-scroll {
                overflow-y: auto;
                scrollbar-width: thin;
                /* For Firefox */
            }

            .overflow-scroll::-webkit-scrollbar {
                width: 8px;
            }

            .overflow-scroll::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.5);
                /* Adjust color */
                border-radius: 10px;
            }

            .overflow-scroll::-webkit-scrollbar-track {
                background-color: #f1f1f1;
                /* Track color */
            }
        </style>
    @endpush
    <div class="row row-deck">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body profile-details-main pb-0 overflow-scroll">
                    <div class="main-content-app pt-5">
                        <div>
                            <h6 class="mb-3">اطلاعات:</h6>
                            <div class="d-flex mb-2 mt-2">
                                <div>
                                    <a class="nav-link border rounded-pill chat-profile me-2" href="javascript:void(0)">
                                        <i class="fe fe-phone"></i>
                                    </a>
                                </div>
                                <div class="ms-2">
                                    <p class="fs-13 fw-semibold mb-0">وضعیت نوبت</p>
                                    <p class="fs-12 text-muted">{{ $fetchData['appOnline']->status->getName() }}</p>
                                </div>
                            </div>
                            <div class="d-flex mb-2 mt-2">
                                <div>
                                    <a class="nav-link border rounded-pill chat-profile me-2" href="javascript:void(0)">
                                        <i class="fa fa-envira" aria-hidden="true"></i>
                                    </a>
                                </div>
                                <div class="ms-2">
                                    <p class="fs-13 fw-semibold mb-0">تاریخ نوبت</p>
                                    <p class="fs-12 text-muted">{{ verta($fetchData['appOnline']->appointmentUser->visited_at)->format('Y/m/d') }}</p>
                                </div>
                            </div>
                            {{-- <div class="d-flex mb-2">
                                <div>
                                    <a class="nav-link border rounded-pill chat-profile me-2" href="javascript:void(0)">
                                        <i class="fa fa-medkit" aria-hidden="true"></i>
                                    </a>
                                </div>
                                <div class="ms-2">
                                    <p class="fs-13 fw-semibold mb-0">وضعیت نوبت</p>
                                    <p class="fs-12 text-muted">{{ $this->fetchData['appOnline']->status->getName() }}
                                    </p>
                                </div>
                            </div> --}}
                            <div class="d-flex mb-2 mt-2">
                                <div>
                                    <a class="nav-link border rounded-pill chat-profile me-2" href="javascript:void(0)">
                                        <i class="fa fa-user-md" aria-hidden="true"></i>
                                    </a>
                                </div>
                                <div class="ms-2">
                                    <p class="fs-13 fw-semibold mb-0">پزشک</p>
                                    <p class="fs-12 text-muted">{{ $fetchData['appOnline']->doctor->fullName }}</p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Closing for main-content-app -->
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="main-content-app pt-0">
                    <div class="main-content-body main-content-body-chat h-100">
                        <div class="main-chat-header pt-3 d-block d-sm-flex">
                            <div class="main-img-user online">
                            </div>
                            <nav class="nav">
                                <div class="d-flex">
                                    @if (!empty($search))
                                        <button class="btn btn-sm btn-secondary d-flex justify-content-center me-2"
                                            wire:click='ignoreSearch'>
                                            <i class="fa fa-times-circle fa-2x" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                    <div class="input-group">
                                        <input wire:model='search' type="text" class="form-control"
                                            placeholder="جست و جو">
                                        <button wire:click='runSearch' wire:target='runSearch'
                                            wire:loading.class='btn-loading btn-gray'
                                            class="btn ripple btn-primary input-group-text text-white border-0"
                                            type="button">
                                            <i class="fe fe-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </nav>
                        </div>
                        <!-- main-chat-header -->
                        <div class="main-chat-body flex-2" id="ChatBody" style="overflow: scroll !important">
                            <div class="content-inner">
                                @if (!empty($this->getMessagesBodys()) && $this->getMessagesBodys()->isNotEmpty())
                                    @foreach ($this->getMessagesBodys() as $date => $messages)
                                        <label class="main-chat-time"><span>{{ $date }}</span></label>
                                        @foreach ($messages as $message)
                                            @if ($message->messageFile->isNotEmpty())
                                                <div
                                                    class="@if ($message->type == Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum::ANSWER)  media flex-row-reverse chat-right
                                                    @else media chat-left @endif">
                                                    <div class="main-img-user online">
                                                        <img alt="avatar" src="{{ $message->user->avatar }}">
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="main-msg-wrapper">
                                                            @if ($message->messageFile()->count())
                                                                @foreach ($message->messageFile as $file)
                                                                    @if (in_array($file->mime, [
                                                                            'jpg',
                                                                            'image/png',
                                                                            'jpeg',
                                                                            'image/jpeg',
                                                                            'png',
                                                                            'image/png',
                                                                            'gif',
                                                                            'image/gif',
                                                                            'webp',
                                                                            'image/webp',
                                                                            'bmp',
                                                                            'image/bmp',
                                                                            'svg',
                                                                            'image/svg',
                                                                            'tiff',
                                                                            'image/tiff',
                                                                            'heic',
                                                                            'image/heic',
                                                                            'heif',
                                                                            'image/heif',
                                                                        ]))
                                                                        <a href="{{ Storage::url($file->disk . '/' . $file->path) }}"
                                                                            data-fancybox="gallery"
                                                                            data-caption="{{ $file->original_name }}">
                                                                            <img src="{{ Storage::url($file->disk . '/' . $file->path) }}"
                                                                                alt="image"
                                                                                style="width: 100px; height: auto;" />
                                                                        </a>
                                                                    @elseif($file->mime == 'mp3')
                                                                        <audio
                                                                            src="{{ Storage::url($file->disk . '/' . $file->path) }}"
                                                                            controls preload="auto"></audio>
                                                                    @endif
                                                                    <div class="main-msg-wrapper"
                                                                        data-id={{ $file->id }}>
                                                                        <a class="text-dark"
                                                                            href="{{ Storage::url($file->disk . '/' . $file->path) }}">
                                                                            <span class="fs-13 mt-1"> دانلود
                                                                                فایل</span>
                                                                            <i
                                                                                class="fe fe-download mt-3 ms-4 text-muted pe-2"></i>
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <span>{{ $message->created_at->format('H:i') }}</span>
                                                            <a href="javascript:void(0)"><i
                                                                    class="icon ion-android-more-vertical"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($message->body != null)
                                                <div
                                                    class="@if ($message->type == Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum::ANSWER)  media flex-row-reverse chat-right
                                                    @else media chat-left @endif">
                                                    <div class="main-img-user online">
                                                        <img alt="avatar" src="{{ $message->user->avatar }}">
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="main-msg-wrapper">
                                                            {{ $message->body }}
                                                        </div>
                                                        <div>
                                                            <span>{{ $message->created_at->format('H:i') }}</span>
                                                            <a href="javascript:void(0)"><i
                                                                    class="icon ion-android-more-vertical"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endforeach
                                @else
                                    <div class="col-md-12 alert alert-primary fade show" role="alert">
                                        لطفا پیام خود را بنویسید تا در اولین فرصت به آن پاسخ داده شود.
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="main-chat-footer d-flex justify-content-center pt-5 mb-5"
                            style="padding-top :40px !important;">
                            @if ($this->fetchData['appOnline']->status == Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::PENDING)
                                <div class="col-md-12 alert alert-info fade show mt-4 ms-3" role="alert">
                                    نوبت فعال نشده است!
                                </div>
                            @elseif(
                                $this->fetchData['appOnline']->status == Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::REJECT ||
                                    $this->fetchData['appOnline']->status == Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::CANCEL)
                                <div class="col-md-12 alert alert-danger fade show mt-4 ms-3" role="alert">
                                    نوبت رد شده است!
                                </div>
                            @elseif(
                                $this->fetchData['appOnline']->status ==
                                    Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::COMPLETED_BY_DOCTOR)
                                <div class="col-md-12 alert alert-primary fade show mt-4 ms-3" role="alert">
                                    <span>
                                        نوبت توسط پزشک پاسخ داده شده است و بسته شده!!
                                    </span>
                                </div>
                            @elseif ($this->fetchData['appOnline']->status != Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::REJECT)
                                {{-- TODO::VOICE js class activate --}}
                                <div class="d-flex flex-column align-items-center my-5">
                                    <button type="button" class="btn btn-secondary " data-bs-toggle="modal"
                                        id="openSoundRecoreddrmodal" data-bs-target="#soundRecorderModal">
                                        <i class="fa fa-microphone fa-xl" aria-hidden="true"></i>
                                    </button>
                                    <!-- Camera Button -->
                                    <button
                                        class="btn @if (isset($form['capturedPic'])) btn-success @else  btn-light @endif mx-3 d-flex justify-content-center p-1 py-2 mt-2"
                                        id="cameraButton" @if (isset($form['capturedPic'])) disabled @endif>
                                        @if (isset($form['capturedPic']))
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                        @else
                                            <i class="fa fa-camera" aria-hidden="true"></i>
                                        @endif
                                    </button>
                                    <input type="file" accept="image/*" capture="environment" id="cameraInput"
                                        wire:model='form.capturedPic' style="display:none;" />
                                </div>
                                <textarea  rows="3" class="form-control ms-1 mt-2 @error('form.typedMessage') is-invalid @enderror"
                                    wire:model='form.typedMessage'
                                    placeholder="@error('form.typedMessage') {{ $message }} @else متن خود را یادداشت کنید @enderror"></textarea>
                                <div class="d-flex flex-column align-items-center mt-5 ms-1 ms-sm-3">
                                    <button wire:click='sendMessage' wire:target='sendMessage'
                                        wire:loading.class='btn-loading' wire:loading.attr='disabeld' type="button"
                                        class="btn btn-icon btn-primary brround mb-2"><i
                                            class="fa fa-paper-plane-o"></i></button>
                                </div>
                            @endif
                            <nav class="nav">
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('appointmentuser::components.appointmentlist.disapprovemodal')
    <livewire:appointmentuser::admin.online.sound-recorder />

</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ admin_asset('js/sound/recorder.js') }}"></script>
    <script src="{{ admin_asset('js/sound/Fr.voice.js') }}"></script>
    <script src="{{ admin_asset('js/sound/app.js') }}"></script>
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script> --}}
    <!-- Include Fancybox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
    <!-- Include Fancybox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

    <script>
        $(document).ready(function() {

            $('[data-fancybox="gallery"]').fancybox({
                buttons: [
                    "zoom",
                    "slideShow",
                    "thumbs",
                    "close"
                ],
                loop: true,
                protect: true,
                // Add more options as needed
            });
            Livewire.on('select_file', (param) => {
                @this.set('form.file', param.url);
                $('#file-selector-modal').modal('hide');
            });

            function scrollToEndOfchat() {
                setTimeout(() => {
                    $('#ChatBody').animate({
                        scrollTop: $('#ChatBody')[0].scrollHeight
                    }, 600);
                }, 100);
            }
            scrollToEndOfchat();
            Livewire.on('sendMessage', function() {
                scrollToEndOfchat();
            });
            Livewire.on('ignoreSearch', function() {
                scrollToEndOfchat();
            });
            Livewire.on('fileHasUpload', function() {
                var myModalEl = document.getElementById('soundRecorderModal');
                var modalsound = bootstrap.Modal.getInstance(myModalEl);

                modalsound.hide();
            });
            $(document).on("click", "#save:not(.disabled)", function() {
                function upload(blob) {
                    var formData = new FormData();
                    formData.append('file', blob);
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        }
                    });
                    $.ajax({
                        url: "/admin/appointment_user/storevoice",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(url) {
                            @this.set('form.voice', url)
                            @this.dispatch('fileHasUpload');
                            $("#audio").attr("src", url);
                            $("#secound_loading").removeClass('d-block').addClass('d-none');
                        }
                    });
                }
                if ($(this).parent().data("type") === "mp3") {
                    Fr.voice.exportMP3(upload, "blob");
                } else {
                    $("#secound_loading").removeClass('d-none').addClass('d-block');
                    Fr.voice.export(upload, "blob");
                }
            });
            $('body').on('click', '#cameraButton', function() {
                $('#cameraInput').click();
            });
            $('body').on('change', '#cameraInput', function(event) {
                const file = event.target.files[0];
                if (file) {
                    $('#loading-spinner').removeClass('d-none');
                    $('#loading-spinner').fadeIn();
                }
            });
            Livewire.on('picUploade', function() {
                $('#loading-spinner').fadeOut();
                $('#loading-spinner').addClass('d-none');
            });
        });
    </script>
@endpush
