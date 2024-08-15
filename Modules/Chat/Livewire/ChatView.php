<?php

namespace Modules\Chat\Livewire;

use App\Events\PusherBroadcast;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Modules\Api\app\Resources\Api\Chat\ChatDetailResource;
use Modules\Chat\app\Models\Chat;
use Illuminate\Support\Collection;
use Modules\Chat\Enum\ChatStatusEnum;
use Illuminate\Support\Facades\Storage;
use Modules\Chat\Enum\ChatDetailTypeEnum;
use Modules\Chat\app\Models\ChatDetailsFile;
use Modules\Chat\app\Events\AdminAnswerChatEvent;

class ChatView extends Component
{
    #[Url]
    public int $chatId = 0;

    public $chatMessage = '';
    public $ImgMessg;
    public array $form ;

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
        if (empty($this->chatMessage) && ! isset($this->form['file'])) {
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
                'original_name' =>$fileName,
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
        }else{
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

        //clear input
        $this->chatMessage = '';

        // send pusher event
        $message = ChatDetailResource::make($chatDetail);

        event(new PusherBroadcast($message , $this->chat->id));

        $this->chat->user->notify(new \Modules\User\Notifications\UserMessageNotification(
            title: "پیام جدید!",
            excerpt: 'یک پیام جدید دارید',
            message: '',
            link: \App\Enum\RouteEnum::ONLINE_MESSAGE->getLink($this->chat->id),
        ));
        $this->dispatch('messageHasBeenSend',true);
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

    public function render()
    {
        //get all chats order by type and latest message
        // $chats =   $chats = new Collection();

        $chats = Chat::orderBy('status', 'asc')
        ->orderBy('created_at')->get();
        return view('chat::livewire.chat-view', compact('chats'));
    }
}
