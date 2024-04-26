<?php

namespace Modules\Api\Http\Controllers;

use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Api\app\Resources\Api\Chat\ChatDetailPaginateResource;
use Modules\Api\app\Resources\Api\Chat\ChatDetailResource;
use Modules\Api\app\Resources\Api\Chat\ChatPaginateResource;
use Modules\Api\app\Resources\Api\Chat\ChatResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Chat\app\Models\Chat;
use Modules\Chat\Enum\ChatDetailTypeEnum;
use Modules\Chat\Enum\ChatStatusEnum;
use Validator;
use Illuminate\Support\Facades\DB;


class ChatController extends Controller
{
    use ApiHandlerTrait;

    public function show(Chat $chat)
    {
        $user = auth()->user();

        if ($chat->user->id <> $user->id) {
            return $this->badRequest('شما درسترسی به این چت را ندارید');
        }
        $chatDetail = $chat->chatDetails()->orderByDesc('id')->paginate();
        if ($chatDetail->first()){
            $access = !($chatDetail->first()->chat->ban == true || $chatDetail->first()->chat->status == ChatStatusEnum::CLOSED);
        } else {
            $access = false;
        }
        $accessibility = [
            'can_send_message' =>  $access,
            'can_show_messages' => $access,
        ];
        $list = [
            'messages' => $chatDetail,
            'accessibility' => $accessibility
        ];
        return $this->ok( new ChatDetailPaginateResource($list));

    }

    public function sendMessage(Request $request)
    {
        $chat_id = null;
        $user = auth()->user();

        if ($request->has('chat_id')){
            $chat = Chat::find($request->input('chat_id'));
            if ($chat->user->id <> $user->id) {
                return $this->requestException([
                    'status' => false,
                    'message' => ' چت متعلق به این کاربر نیست'
                ]);
            }

        }
        $content = $request->input('content');
        DB::beginTransaction();
        try {

            if ($request->has('chat_id')) {
                $status = ChatStatusEnum::USER_SEND_QUESTION;
                $chat->update([
                    'status' => $status
                ]);
                $chat->increment('new_message_by_user');

            } else {
                $status = ChatStatusEnum::JUST_CREATED;
                $condition = [
                    'status' => $status,
                    'new_message_by_support' => 0,
                    'new_message_by_user' => 1,
                ];
                if ($request->has('question')){
                    $condition['detail'][Chat::DETAIL_QUESTION] = $request->input('question');
                }

                $chat = $user->chats()->create($condition);
            }

            $chatDetail = $chat->chatDetails()->create([
                'content' => $content,
                'user_id' => $user->id,
                'type' => ChatDetailTypeEnum::MESSAGE
            ]);


            if ($request->hasFile('files')) {
                $validator = Validator::make($request->all(), [
                    'files.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,pdf,mp3,wav,m4a,m4v,webm',
                ]);

                if ($validator->fails()) {
                    throw new \Exception('فرمت فایل ارسالی اشتباه است');
                }

                $files =  $request->file('files');
                $this->uploadFiles($user, $files, $chatDetail);
            }


            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->requestException([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return $this->ok([
            'status' => true,
            'message' => 'پیغام با موفقیت ارسال شد',
            'model' => ChatDetailResource::make($chat->chatDetails()->orderByDesc('id')->first())
        ]);
//        $chatDetail = $chat->chatDetails()->paginate();
//        return $this->ok( new ChatDetailPaginateResource($chatDetail));



    }

    private function uploadFiles($user , $files , $chatDetail)
    {
        foreach ($files as $file) {
            $orignName = $file->getClientOriginalName();
//            $extension = $file->getClientMimeType();
            $size = $file->getSize();

            $disk = 'chat/' . $chatDetail->id .'/' ;
            $name = $file->store($disk , 'public');

            $extension = pathinfo($name, PATHINFO_EXTENSION);

            $imageName = basename($name);
            $mime = strtok($extension, '/');
            if (strpos($orignName, "audio_123337") === 0) {
                $mime = 'mp3';
            }

            $chatDetail->files()->create([
                'user_id'   => $user->id,
                'original_name'   => $orignName,
                'server_name'   => $imageName,
                'disk'   => $disk,
                'path'   => $imageName,
                'extension'   => $extension,
                'mime'   => $mime,
                'size'   => $size,
            ]);
        }
    }

    public function index()
    {
        $user = auth()->user();
        $activeChats = $this->checkActiveChat();
        // When the user did not have an active chat
        if ($activeChats == null) {
            return $this->ok([
                'active_chat' => null,
                'questions' => $this->questionList(),
            ]);
        }
        // When the user did not have an active chat

        return $this->ok([
            'active_chat' => ChatResource::make($activeChats),
            'questions' => $this->questionList(),
        ]);

    }

    public function list()
    {
        $user = auth()->user();
        $chats = $user->chats()->orderByDesc('id')->paginate();
        return $this->ok( new ChatPaginateResource($chats));
    }

    private function checkActiveChat()
    {
        $user = auth()->user();
        return $user->chats()->where('status', '<>', ChatStatusEnum::CLOSED)->where('ban', false)->orderByDesc('id')->first();
    }

    private function questionList()
    {
        return chatQuestions();
    }
}
