<?php

namespace Modules\Chat\Livewire;

use App\Enum\RouteEnum;
use Livewire\Component;
use App\Models\ShortLink;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Events\PusherBroadcast;
use Livewire\Attributes\Computed;
use Modules\Chat\app\Models\Chat;
use Illuminate\Support\Facades\Log;
use Modules\User\Enum\UserMetaEnum;
use Modules\Chat\Enum\ChatStatusEnum;
use Illuminate\Support\Facades\Storage;
use Modules\Chat\app\Models\ChatDetail;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Chat\Enum\ChatDetailTypeEnum;
use Modules\Chat\app\Models\ChatDetailsFile;
use Modules\Chat\app\Models\MessageTemplate;
use Modules\Chat\app\Events\AdminAnswerChatEvent;
use Modules\User\app\Notifications\UserSmsNotification;
use Modules\Api\app\Resources\Api\Chat\ChatDetailResource;

class ChatView extends Component
{
    use WithPagination, WithFileUploads;

    #[Url]
    public int $chatId = 0;
public array $fetchData =[];
    public $chatMessage = '';
    public $searchTerm = ''; // Property to hold the search term

    public $ImgMessg;
    public array $form;
    public $perPage = 50;
    private $loadMode = 100;
    #[Url]
    public $filterStatus = null;
    protected $paginationTheme = 'bootstrap'; // Optional, if you're using Bootstrap for styling



    #[Computed]
    public function chatList()
    {
        Chat::firstWhere('id', $this->chatId)?->update([
            'new_message_by_user' => 0,
        ]);
        return Chat::firstWhere('id', $this->chatId)->chatDetails()->orderBy('id', 'asc')->get()->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });
    }

    #[Computed]
    public function chat()
    {
        return Chat::firstWhere('id', $this->chatId);
    }
    // public function updatedImgMessg()
    // {

    //     if (empty($this->ImgMessg)) {
    //         $this->dispatch('error', message: 'لطفا فایل را دوباره انتخاب کنید.');
    //         return;
    //     }
    //    $chatDetailId =  $this->chat?->chatDetails()->create([
    //         'content' => $this->ImgMessg,
    //         'type' => ChatDetailTypeEnum::ADMIN_MESSAGE,
    //         'user_id' => auth()->id(),
    //     ]);
    //         $this->InsertFileUpload($chatDetailId);
    //     $this->chat?->update([
    //         'status' => ChatStatusEnum::ANSWERED,
    //         'new_message_by_user' => 0,
    //     ]);
    //     //clear input
    //     $this->chatMessage = '';
    //     AdminAnswerChatEvent::dispatch($this->chat);
    // }
    // private function insertFileUpload( $chatDetailId){
    // if (isset($this->ImgMessg)) {
    //     $fileUrl = Storage::disk('public')->url($this->ImgMessg);
    // }
    // $p = explode('/', $this->ImgMessg);
    // // $mimeType = Storage::mimeType($this->ImgMessg);
    // $size  =  ceil((Storage::size('public/'. end($p))) / 1024);
    // $mime  =  Storage::mimeType('public/'. end($p));
    // $extension = pathinfo($fileUrl, PATHINFO_EXTENSION);
    // $url = $this->ImgMessg;
    // // Parse the URL
    // $parsedUrl = parse_url($url);
    // // Get the file path
    // $filePath = str_replace('/storage/', '', $parsedUrl['path']);

    // $fileModel = [
    //     'user_id' => $this->chat?->user->id,
    //     'answer_by' => auth()->user()->id,
    //     'chat_detail_id' =>  $chatDetailId->id,
    //     'original_name' => end($p),
    //     'server_name' => end($p),
    //     'disk' => 'public',
    //     'path' => $filePath,
    //     'extension' => $extension,
    //     'mime' => $mime,
    //     'size' => $size,
    // ];
    // ChatDetailsFile::create($fileModel);
    // }

    public function sendMessage()
    {
        if (empty($this->chatMessage) && ! isset($this->form['file']) && ! isset($this->form['capturedPic'])) {
            $this->dispatch('error', message: 'متن پیام خود را وارد کنید.');
            return;
        }
        if ($this->chat == null) {
            $this->dispatch('error', message: 'لطفا گفت و گوی مد نظر خود را انتخاب کنید');
            return;
        }
        if (isset($this->form['file'])) {
            $url = $this->form['file'];
            // Parse the URL
            $parsedUrl = parse_url($url);

            // Get the file path
            $filePath = str_replace('/storage/', '', $parsedUrl['path']);
            // Get the file name
            $fileName = basename($filePath);

            // Get the dsk
            $fileDisk = 'public';
            $extension = pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);

            $chatDetail =  $this->chat?->chatDetails()->create([
                'content' => $this->ImgMessg,
                'type' => ChatDetailTypeEnum::ADMIN_MESSAGE,
                'user_id' => auth()->id(),
            ]);
            $fileModel = [
                'user_id' => $this->chat?->user->id,
                'answer_by' => auth()->user()->id,
                'chat_detail_id' =>  $chatDetail->id,
                'original_name' => $fileName,
                'server_name' => $fileName,
                'disk' => $fileDisk,
                'path' => $filePath,
                'extension' => $extension,
                'mime' => $extension,
                'size' => 1,
            ];
            ChatDetailsFile::create($fileModel);
            unset($this->form['file']);
            $this->addError('success', 'پیام با موفقیت ارسال شد');
        } elseif (isset($this->form['capturedPic'])) {
            $chatDetail = $this->chat?->chatDetails()->create([
                'content' => $this->ImgMessg,
                'type' => ChatDetailTypeEnum::ADMIN_MESSAGE,
                'user_id' => auth()->id(),
            ]);

            // Store the captured image and get the full path
            $filePath = $this->form['capturedPic']->store('public/uploads');

            // Strip the 'public/' part to store a relative path
            $filePath = str_replace('public/', '', $filePath);

            // Get the file name
            $fileName = basename($filePath);

            // Get the file extension
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            // Define the disk being used (assumed 'public')
            $fileDisk = 'public';

            // Prepare the file model for saving in the database
            $fileModel = [
                'user_id' => $this->chat?->user->id,
                'answer_by' => auth()->user()->id,
                'chat_detail_id' => $chatDetail->id,
                'original_name' => $fileName,
                'server_name' => $fileName,
                'disk' => $fileDisk,
                'path' => $filePath, // Now it's relative, like in the first method
                'extension' => $extension,
                'mime' => $extension,
                'size' => $this->form['capturedPic']->getSize(),
            ];

            // Store the file record in the database
            ChatDetailsFile::create($fileModel);

            // Clear the form after submission
            unset($this->form['capturedPic']);
        } else {
            unset($this->form['typedMessage']);
            $this->addError('success', 'پیام با موفقیت ارسال شد');
            $chatDetail = $this->chat?->chatDetails()->create([
                'content' => $this->chatMessage,
                'type' => ChatDetailTypeEnum::ADMIN_MESSAGE,
                'user_id' => auth()->id(),
            ]);
        }
        $this->chat?->update([
            'status' => ChatStatusEnum::ANSWERED,
            'new_message_by_user' => 0,
        ]);
        $this->chat?->increment('new_message_by_support');

        $notificationMessage =  isset($this->chatMessage) ? substr($this->chatMessage, 0, 50) : 'یک پیام جدید دارید';
        //clear input
        $this->chatMessage = '';

        // send pusher event
        try {
            $message = ChatDetailResource::make($chatDetail);
            event(new PusherBroadcast($message, $this->chat->id));
        } catch (\Throwable $th) {
            Log::error('An error occurred during chat processing: ' . $th->getMessage(), [
                'exception' => $th,
            ]);
        }
        // send sms
        if (isset($this->form['sendSms']) && $this->form['sendSms'] == true) {
            $template = setting(SettingKeyEnum::SMS_FOR_SEND_MESSAGE_IN_CHATS);
            if (isset($template)) {
                $messageLink = 'https://webapp.mata-app.com' . (\App\Enum\RouteEnum::CHAT->getLink(replacement: $this->chat->id));
                $shortLink =  $this->chat->shortLink()->create([
                    'link_code' => ShortLink::generateShortLinkCode(),
                    'link_url'  => $messageLink,
                ]);
                $this->chat->user->notify(new UserSmsNotification($template, url('/s/' . $shortLink->link_code)));
            }
            unset($this->form['sendSms']);
        }
        //send notification
        try {
            // $this->chat->user->notify(new \Modules\User\Notifications\UserMessageNotification(
            //     title: "پیام جدید برای پشتیبانی !",
            //     excerpt: $notificationMessage,
            //     message: '',
            //     link: \App\Enum\RouteEnum::ONLINE_MESSAGE->getLink($this->chat->id),
            // ));
        } catch (\Throwable $th) {
        }

        $this->dispatch('messageHasBeenSend', true);
        AdminAnswerChatEvent::dispatch($this->chat);
    }

    #[On('echo:chat.{chatId},.UserAnswer')]
    public function newMessage(): void
    {
        unset($this->chatList);
    }

    public function selectChatRoom(int $chatId): void
    {
        $this->chatId = $chatId;
        $this->dispatch('chatRoomSelected');
    }
    public function closeChat($id)
    {
        $closeChat =  Chat::find($id);
        $closeChat->update([
            'status' => ChatStatusEnum::CLOSED,
        ]);
        return redirect()->route('admin.chat', ['chatId' => $closeChat->id])->with('success', 'وضعیت گفت و گو به بسته شده تغییر کرد.');
    }

    public function loadMore()
    {
        // Increase the number of items to display
        $this->perPage += $this->loadMode;
    }

    public function showFilteredChat($value)
    {
        $this->filterStatus = $value;
    }
    public function updated($properyty)
    {
        if ($properyty == 'form.capturedPic') {
            $this->dispatch('picUploade', true);
        }
    }
    public function runSearch()
    {
        $this->render();
    }
    public function booted()
    {
        $this->dispatch('loadJs', true);
    }
    public function mount() {
        $this->fetchData['messageTemplate']      =  MessageTemplate::all();
    }
    public function render()
    {
        // Start the chat query
        $chats = Chat::query();

        $chats->when(isset($this->searchTerm), function ($q) {
            $q->where(function ($qq) {
                $qq->whereHas('chatDetails', function ($qqq) {
                    $qqq->where('content', 'like',  "%{$this->searchTerm}%");
                })->orWhereHas('user', function ($qqq) {
                    $qqq->whereHas('metas', function ($qqqq) {
                        $qqqq->where([
                            ['meta_key', UserMetaEnum::FIRST_NAME],
                            ['meta_value', 'LIKE', "%{$this->searchTerm}%"],
                        ])->orWhere([
                            ['meta_key', UserMetaEnum::LAST_NAME],
                            ['meta_value', 'LIKE', "%{$this->searchTerm}%"],
                        ]);
                    })->orWhere('mobile', $this->searchTerm);
                });
            });
        });

        // Apply status filter if set
        if ($this->filterStatus) {
            if($this->filterStatus == 'closed') {
                $chats->where('status', chatStatusEnum::CLOSED);
            }elseif($this->filterStatus == 'userAwnswered') {
                $chats->whereIn('status',[ chatStatusEnum::JUST_CREATED,chatStatusEnum::USER_SEND_QUESTION]);
            }
        } else {
            $chats->where('status', '!=', ChatStatusEnum::CLOSED);
        }

        // Order chats based on the last message's created_at from chatDetails
        $chats = $chats->orderByDesc(
            ChatDetail::select('created_at')
                ->whereColumn('chats.id', 'chat_details.chat_id')
                ->latest()
                ->limit(1)
        )
            ->paginate($this->perPage);

        return view('chat::livewire.chat-view', compact('chats'));
    }
}
