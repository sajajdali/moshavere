<div>
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">گفت و گو</h1>
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
    @if ($chats->isNotEmpty())
        <!-- Row -->
        <div class="row row-deck">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-4">
                <div class="card  overflow-scroll">
                    <div class="main-content-app pt-0">
                        <div class="main-content-left main-content-left-chat">
                            <!-- main-chat-header -->
                            <div class="tab-content main-chat-list flex-2">
                                <div class="tab-pane active" id="ChatList">
                                    <div class="main-chat-list tab-pane">
                                        @foreach ($chats as $chatItem)
                                            <a class="cursor-pointer media @if ($chatItem->id == $chatId) selected @else new @endif @if ($loop->first) border-top-0 @endif @if ($loop->last) border-bottom-0 @endif"
                                                wire:click="selectChatRoom({{ $chatItem->id }})">
                                                <div class="main-img-user online">
                                                    <img alt="{{ $chatItem->user?->full_name }}"
                                                        src="{{ $chatItem->user?->avatar }}">
                                                    @if ($chatItem->new_message_by_user > 0)
                                                        <span>{{ $chatItem->new_message_by_user }}</span>
                                                    @endif
                                                </div>
                                                <div class="media-body">
                                                    <div class="media-contact-name">
                                                        <span>{{ $chatItem->user?->full_name }}</span>
                                                        <span>{{ $chatItem->latest_message_ago }}</span>
                                                    </div>
                                                    <p> {{ $chatItem->latest_message_excerpt }} </p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                    <!-- main-chat-list -->
                                </div>
                            </div>
                            <!-- main-chat-list -->
                        </div>
                    </div>
                </div>
            </div>
            @if ($chatId != 0)
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-8">
                    <div class="card">
                        <div class="main-content-app pt-0">
                            <div class="main-content-body main-content-body-chat h-100">
                                <div class="main-chat-header pt-3 d-block d-sm-flex">
                                    <div class="main-img-user online"><img alt="{{ $this->chat?->user?->full_name }}"
                                            src="{{ $this->chat?->user?->avatar }}">
                                    </div>
                                    <div class="main-chat-msg-name mt-2">
                                        <p class="mb-0">{{ $this->chat?->user?->full_name }}</p>
                                        <span class="dot-label bg-success"></span><small class="me-3">کاربر</small>
                                    </div>
                                    <nav class="nav">
                                        <div>
                                            <div class="input-group"></div>
                                        </div>
                                    </nav>
                                </div>
                                <!-- main-chat-header -->
                                <div class="main-chat-body flex-2" id="ChatBody">
                                    @if ($this->chatList?->isNotEmpty())
                                        <div class="content-inner" id="lightgallery">
                                            @foreach ($this->chatList as $date => $chatItems)
                                                <label
                                                    class="main-chat-time"><span>{{ \Carbon\Carbon::parse($date)->diffForHumans() }}</span></label>
                                                @foreach ($chatItems as $chatMessage)
                                                        @if ($chatMessage->type->is(\Modules\Chat\Enum\ChatDetailTypeEnum::MESSAGE))
                                                        <div class="media flex-row-reverse chat-right">
                                                            @else
                                                        <div class="media chat-left">
                                                            @endif
                                                            <div class="main-img-user online">
                                                                <img alt="avatar"
                                                                    src="{{ $chatMessage->user?->avatar }}">
                                                            </div>
                                                            <div class="media-body">
                                                                @if ($chatMessage->files()->count())
                                                                    @foreach($chatMessage->files as $file)
                                                                        @if(in_array($file->mime,['jpg','image/png', 'jpeg','image/jpeg', 'png','image/png', 'gif','image/gif', 'webp','image/webp', 'bmp','image/bmp', 'svg','image/svg' ,'tiff','image/tiff', 'heic','image/heic', 'heif','image/heif']))
                                                                            <a href="{{  Storage::url($file->disk . '/' . $file->path) }}" data-fancybox="gallery" data-caption="{{ $file->original_name }}">
                                                                                <img src="{{  Storage::url($file->disk . '/' . $file->path) }}" alt="{{ $file->original_name }}" style="width: 100px; height: auto;"/>
                                                                            </a>
                                                                        @elseif($file->mime == 'mp3')
                                                                            <audio src="{{ Storage::url($file->disk . '/' . $file->path) }}" controls preload="auto"></audio>
                                                                        @endif
                                                                            <div class="main-msg-wrapper">
                                                                                <a class="text-dark"
                                                                                   href="{{ Storage::url($file->disk . '/' . $file->path) }}">
                                                                                    <span class="fs-13 mt-1"> دانلود فایل
                                                                                    </span> <i
                                                                                        class="fe fe-download mt-3 ms-4 text-muted pe-2"></i>
                                                                                </a>
                                                                            </div>
                                                                    @endforeach
                                                                @elseif($chatMessage->content)
                                                                    <div class="main-msg-wrapper">
                                                                        {{ $chatMessage->content }}
                                                                    </div>
                                                                @endif
                                                                <div>
                                                                    <span>{{ $chatMessage->created_at->format('H:i') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                @endforeach
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            هنوز پیامی ارسال نشده است.
                                        </div>
                                    @endif
                                </div>
                                <div class="main-chat-footer pt-5 pb-5">
                                    <input class="form-control" placeholder="متن پیام شما..." type="text"
                                        wire:model="chatMessage">
                                    {{-- send File modal --}}
                                    <button data-bs-target="#file-selector-modal" data-bs-toggle="modal" class="nav-link"
                                    href="javascript:void(0)">
                                    @if (isset($form['file']))
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                    @else
                                        <i class="fe fe-paperclip"></i>
                                    @endif
                                </button>
                                    <button type="button" wire:click="sendMessage"
                                        wire:loading.class="btn btn-light btn-loading"
                                        wire:loading.class.remove="btn-primary"
                                        class="btn btn-icon  btn-primary brround">
                                        <i class="fa fa-paper-plane-o"></i>
                                    </button>
                                    <nav class="nav">
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-8">
                    <div class="card">
                        <div class="alert alert-info">
                            برای مشاهده گفت و گو از لیست یکی از گفت و گو ها را انتخاب کنید.
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <!-- End Row -->
    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    هنوز چتی آغاز نشده است.
                </div>
            </div>
        </div>
    @endif
    <livewire:admin::file-manager-modal />
</div>
@push('scripts')
    <!--- TABS JS -->
    <script src="{{ admin_asset('js/pusher.js') }}"></script>
    <script src="{{ admin_asset('plugins/tabs/jquery.multipurpose_tabcontent.js') }}"></script>
    <script src="{{ admin_asset('plugins/tabs/tab-content.js') }}"></script>
    <script src="{{ admin_asset('js/chat.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>

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
                });
            </script>
            <script>
        Livewire.on('error', param => {
            swal({
                title: "خطا!",
                text: "" + param.message,
                type: "error",
                showCancelButton: true,
                allowOutsideClick: true,
                showConfirmButton: false,
                cancelButtonText: "متوجه شدم",
                closeOnConfirm: false
            });
        });
        Livewire.on('select_file', (param) => {
            @this.set('form.file', param.url);
            //close modal
            $('#file-selector-modal').modal('hide');
        });
        scroll();

        Livewire.on('messageHasBeenSend', function() {
            scroll();
            });
        function scroll() {
            setTimeout(() => {
                $('#ChatBody').scrollTop($('#ChatBody')[0].scrollHeight);
            }, 200);
        }
        Livewire.on('chatRoomSelected', function() {
            scroll();
        })
    </script>
@endpush
