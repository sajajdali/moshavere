<div>
    <div wire:loading>
        <div class="loading-overlay d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">گفت و گو</h1>
        </div>
        <div class="card-options">
            @if ($filterStatus != null)
                <button class="btn btn-success me-2" type="button" wire:click="showFilteredChat(null)">
                    حذف فیلترها ->
                </button>
            @else
                <button class="btn btn-warning me-2" type="button" wire:click='showFilteredChat("closed")'>
                    نمایش چت های بسته شده
                </button>
                <button class="btn btn-info me-2" type="button" wire:click='showFilteredChat("userAwnswered")'>
                    چت های پاسخ کاربر
                </button>
            @endif
        </div>
    </div>
    <div id="loading-spinner" class="d-none">
        <div class="loading-overlay d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" role="status">
            </div>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    <div class="row row-deck">
        @include('chat::components.chatsidebar')
        @if ($chatId != 0)
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-8">
                <div class="card">
                    <div class="main-content-app pt-0">
                        <div class="main-content-body main-content-body-chat h-100">
                            <div class="main-chat-header pt-3 d-block d-sm-flex">
                                <div class="main-img-user online">
                                    <img alt="{{ $this->chat?->user?->full_name }}"
                                        src="{{ $this->chat?->user?->avatar }}">
                                </div>
                                <div class="main-chat-msg-name mt-2">
                                    <p class="mb-0">{{ $this->chat?->user?->full_name }}</p>
                                    <span class="dot-label bg-success"></span><small class="me-3">کاربر</small>
                                </div>
                                <div class="main-chat-msg-name text-muted mt-2 border-right">
                                    <p class="mb-0 "> کد ملی: {{ $this->chat?->user?->nationalCode }}</p>
                                    <p class="mb-0">شماره تماس: {{ $this->chat?->user?->mobile }} </p>
                                </div>
                                <nav class="nav">
                                    <div>
                                        <div class="input-group" wire:key='{{ time() }}'>
                                            @if (isset($this->chatList?->first()?->first()?->chat) &&
                                                    $this->chatList?->first()?->first()?->chat?->status != Modules\Chat\Enum\ChatStatusEnum::CLOSED)
                                                <button wire:confirm='از بستن چت مطمعن هستید؟'
                                                    wire:click='closeChat({{ $this->chatList?->first()?->first()?->chat->id }})'
                                                    class="btn btn-warning">بستن چت
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </nav>
                            </div>
                            <!-- main-chat-header -->
                            <div class="main-chat-body flex-2 overflow-scroll" id="ChatBody">
                                @if ($this->chatList?->isNotEmpty())
                                    <div class="content-inner" id="lightgallery" wire:key='{{ time() }}'>
                                        @foreach ($this->chatList as $date => $chatItems)
                                            <label class="main-chat-time"><span>پیام های
                                                    {{ verta(\Carbon\Carbon::parse($date)->toDatestring())->format('%d %b') }}</span></label>
                                            @foreach ($chatItems as $chatMessage)
                                                <div
                                                    class="media flex-row-reverse chat-right  @if ($chatMessage->type->is(\Modules\Chat\Enum\ChatDetailTypeEnum::MESSAGE)) @else chat-left @endif">
                                                    <div class="main-img-user online">
                                                        <img alt="avatar" src="{{ $chatMessage->user?->avatar }}">
                                                    </div>
                                                    @include('chat::components.mediabody')
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
                            <div class="main-chat-footer mb-5" wire:key='{{ time() . time() }}'
                                style="padding-top :60px !important;">
                                @include('chat::components.chatfooter')
                            </div>
                            <div class="row mt-5  pt-1 pt-sm-3">
                                <div class="col-12 mt-5">
                                    <span class="rounded-pill ms-1 mt-1 d-flex align-item-center mt-5">
                                        <div class="material-switch">
                                            <input wire:model='form.sendSms' id="sendSms" name="siwtch04"
                                                type="checkbox" />
                                            <label for="sendSms" class="label-info"></label>
                                        </div>
                                        <p class="card-sub-title">ارسال پیامک به کاربر</p>
                                    </span>
                                </div>
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
    <livewire:admin::file-manager-modal />
</div>
@push('scripts')
    <!--- TABS JS -->
    <script src="{{ admin_asset('js/pusher.js') }}"></script>
    <script src="{{ admin_asset('plugins/tabs/jquery.multipurpose_tabcontent.js') }}"></script>
    <script src="{{ admin_asset('plugins/tabs/tab-content.js') }}"></script>
    <script src="{{ admin_asset('js/chat.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
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
        $('body').on('change', '#searchInput', function() {
            @this.runSearch();
        });

        function js() {
            $('.select2-show-search').select2();
            $('body').on('change', '.select2-show-search', function() {
                var modelName = $(this).val();
                // $('#sendMessageBox').val(modelName);
                // @this.set('chatMessage', modelName);
                @this.templateMessageSelect(modelName);
            });
        }
        js();
        Livewire.on('loadJs', function() {
            setTimeout(() => {
                js();
            }, 500);
        });
    </script>
@endpush
