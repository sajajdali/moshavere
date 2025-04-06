<div class="col-sm-12 col-md-12 col-lg-12 col-xl-4">
    <div class="card  overflow-scroll">
        <div class="main-content-app pt-0">
            <div class="main-content-left main-content-left-chat">
                <!-- main-chat-header -->
                <div class="tab-content main-chat-list flex-2">
                    <div class="tab-pane active" id="ChatList">
                        <div class="main-chat-list tab-pane">
                            <div>
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="text" id="searchInput" class="form-control"
                                            placeholder="جست و جوی پیشرفته ...." wire:model="searchTerm"
                                            wire:keydown.enter='runSearch'>
                                        <button class="btn btn-outline-secondary" type="button">
                                            جستجو
                                        </button>
                                    </div>
                                </div>
                                <!-- Chat list -->
                                @if ($chats->count())
                                    @foreach ($chats as $chatItem)
                                        <a style="background-color:
                                        @if ($chatItem->status == \Modules\Chat\Enum\ChatStatusEnum::CLOSED) #f4c3c3 @elseif($chatItem->status == \Modules\Chat\Enum\ChatStatusEnum::ANSWERED) #e5e7ff @endif"
                                            class="cursor-pointer media @if ($chatItem->id == $chatId) selected @else new @endif @if ($loop->first) border-top-0 @endif @if ($loop->last) border-bottom-0 @endif"
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
                                                <p>{{ $chatItem->latest_message_excerpt }}</p>
                                            </div>
                                        </a>
                                    @endforeach

                                    <!-- Show "Continue view" button if more pages are available -->
                                    @if ($chats->hasMorePages())
                                        <div class="text-center mt-3">
                                            <button wire:click="loadMore" class="btn btn-primary">مشاهده بیشتر
                                                ...
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-center mt-3">
                                            <p>دیتای بیشتری یافت نشد ...</p>
                                        </div>
                                    @endif
                                @else
                                    <x-alert type="warning" message="هیچ اطلاعاتی یافت نشد" />
                                @endif
                            </div>

                        </div>
                        <!-- main-chat-list -->
                    </div>
                </div>
                <!-- main-chat-list -->
            </div>
        </div>
    </div>
</div>
