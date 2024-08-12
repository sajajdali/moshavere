<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">پشتیبانی</h1>
        </div>

    </div>
    <div wire:loading>
        <div class="loading-overlay d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
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
    <div class="row row-deck">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body profile-details-main pb-0">
                    <div class="main-content-app">
                        <div class="text-center chat-image p-4 pb-0 mb-4 br-5">
                            <div class="rounded-circle chat-profile">
                                <a class="rounded-circle" href="{{ route('admin.user.index') }}">
                                    <img alt="profile-avatar" src="{{ $fetchData['user']->avatar }}"
                                        class="avatar avatar-xl rounded-circle">
                                </a>
                            </div>
                            <div class="main-chat-msg-name">
                                <a href="{{ route('admin.user.index') }}">
                                    <h5 class="mb-1 text-dark fw-semibold mb-1">{{ $fetchData['user']->fullname }}</h5>
                                </a>
                                <small class="me-3">تاریخ نوبت</small>
                                <p class="text-muted mt-0 mb-1 pt-0 fs-13">
                                    {{ verta($fetchData['appOnline']->date_visit)->format('Y-m-d') }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-3">اطلاعات:</h6>
                            <div class="d-flex mb-2 mt-2">
                                <div>
                                    <a class="nav-link border rounded-pill chat-profile me-2" href="javascript:void(0)">
                                        <i class="fe fe-phone"></i>
                                    </a>
                                </div>
                                <div class="ms-2">
                                    <p class="fs-13 fw-semibold mb-0">شماره</p>
                                    <p class="fs-12 text-muted">{{ $fetchData['user']->mobile }}</p>
                                </div>
                            </div>
                            <div class="d-flex mb-2">
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
                            </div>
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
                            <div class="border-top mt-3">
                                @if (
                                    $this->fetchData['appOnline']->status == Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::REJECT ||
                                        $this->fetchData['appOnline']->status == Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::CANCEL)
                                @else
                                    <div class="mt-3 d-flex flex-column justify-content-start">
                                        <button class="btn btn-danger rounded-full " style="background-color: #d77377 !important"
                                            wire:click='cancelAppointment()' wire:confirm='میخواهید این نوبت را کنسل کنید؟' type="button"> <i
                                                class="fa fa-times font-bold" aria-hidden="true"></i> کنسل کردن نوبت
                                        </button>
                                        <button class="btn btn-danger mt-3 my-2 rounded-full "
                                            wire:click='cancelAppointment()' wire:confirm='میخواهید این نوبت را کنسل کنید؟' type="button"> <i
                                                class="fa fa-times font-bold" aria-hidden="true"></i> کنسل کردن به همراه بازگشت وجه
                                        </button>
                                    </div>
                                @endif
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
                                <img alt="avatar" src="{{ $fetchData['user']->avatar }}">
                            </div>
                            <div class="main-chat-msg-name mt-2">
                                <p class="mb-0">{{ $fetchData['user']->fullname }}</p>
                                <span class="dot-label bg-success"></span>
                                <small class="me-3">
                                    {{ isset($this->fetchData['messages']) && !empty($this->fetchData['messages']) && $this->fetchData['messages']->isNotEmpty() ? $this->fetchData['messages']->last()->seen->getName() : 'بدون پیام' }}
                                </small>
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
                                                    class="@if ($message->type == Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum::ANSWER) media chat-left
                                                    @else  media flex-row-reverse chat-right @endif">
                                                    <div class="main-img-user online">
                                                        <img alt="avatar" src="{{ $message->user->avatar }}">
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="main-msg-wrapper">
                                                            @if ($message->messageFile()->count())
                                                                @foreach ($message->messageFile as $file)
                                                                    @if (in_array($file->mime, ['jpg','image/png', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'heic', 'heif']))
                                                                        <a href="{{ Storage::url($file->disk . $file->server_name) }}"
                                                                            data-fancybox="gallery"
                                                                            data-caption="{{ $file->original_name }}">
                                                                            <img src="{{ Storage::url($file->disk . $file->server_name) }}"
                                                                                alt="{{ $file->original_name }}"
                                                                                style="width: 100px; height: auto;" />
                                                                        </a>
                                                                    @elseif($file->mime == 'mp3')
                                                                        <audio
                                                                            src="{{ Storage::url($file->disk . $file->server_name) }}"
                                                                            controls preload="auto"></audio>
                                                                    @endif
                                                                    <div class="main-msg-wrapper">
                                                                        <a class="text-dark"
                                                                            href="{{ Storage::url($file->disk . $file->server_name) }}">
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
                                                <div class="@if ($message->type == Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum::ANSWER) media chat-left
                                                    @else  media flex-row-reverse chat-right @endif">
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
                                    <div class="col-md-12 alert alert-warning fade show" role="alert">
                                        پیامی یافت نشد
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="main-chat-footer d-flex justify-content-center pt-5">
                            @if ($this->fetchData['appOnline']->status == Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::PENDING)
                                <button type="button" class="btn btn-success ms-2" wire:click='approvedAppointment'
                                    wire:loading.class='btn-loading btn-gray' wire:target='approvedAppointment'> تایید
                                    این نوبت
                                </button>
                                <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal"
                                    data-bs-target="#resoanForDisapproveModal"> عدم تایید این نوبت
                                </button>
                            @elseif(
                                $this->fetchData['appOnline']->status == Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::REJECT ||
                                    $this->fetchData['appOnline']->status == Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::CANCEL)
                                <div class="col-md-12 alert alert-danger fade show mt-4 ms-3" role="alert">
                                    نوبت رد شده است!
                                </div>
                            @elseif ($this->fetchData['appOnline']->status != Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::REJECT)
                            {{-- TODO::VOICE js class activate --}}
                                {{-- <button type="button" class="btn btn-secondary ms-2" data-bs-toggle="modal"
                                    data-bs-target="#soundRecorderModal">
                                    <i class="fa fa-microphone fa-xl" aria-hidden="true"></i>
                                </button> --}}
                                <input class="form-control ms-2 @error('form.typedMessage') is-invalid @enderror"
                                    wire:model='form.typedMessage'
                                    placeholder="@error('form.typedMessage') {{ $message }} @else متن خود را یادداشت کنید @enderror"
                                    type="text">
                                <button data-bs-target="#file-selector-modal" data-bs-toggle="modal" class="nav-link"
                                    href="javascript:void(0)">
                                    @if (isset($form['file']))
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                    @else
                                        <i class="fe fe-paperclip"></i>
                                    @endif
                                </button>
                                <button wire:click='sendMessage' wire:target='sendMessage'
                                    wire:loading.class='btn-loading' wire:loading.attr='disabeld' type="button"
                                    class="btn btn-icon btn-primary brround"><i
                                        class="fa fa-paper-plane-o"></i></button>
                            @endif
                            <nav class="nav"></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('appointmentuser::components.appointmentlist.disapprovemodal')
    <livewire:admin::file-manager-modal />
    <livewire:appointmentuser::admin.online.sound-recorder />
</div>
@push('scripts')
    <script src="{{ admin_asset('js/sound/recorder.js') }}"></script>
    <script src="{{ admin_asset('js/sound/Fr.voice.js') }}"></script>
    <script src="{{ admin_asset('js/sound/app.js') }}"></script>
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <!-- Include Fancybox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />

    <!-- Include jQuery (if not already included) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

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
            $('#ChatBody').scrollTop($('#ChatBody')[0].scrollHeight);

            Livewire.on('sendMessage', function() {
                $('#ChatBody').scrollTop($('#ChatBody')[0].scrollHeight);
            });
            Livewire.on('ignoreSearch', function() {
                setTimeout(() => {
                    $('#ChatBody').scrollTop($('#ChatBody')[0].scrollHeight);
                }, 500);
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
                restore();
            });
        });
    </script>
@endpush
