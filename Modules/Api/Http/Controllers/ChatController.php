<?php

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Api\app\Resources\Api\Chat\ChatDetailPaginateResource;
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

        $chatDetail = $chat->chatDetails()->paginate();
        return $this->ok([
            'data' => new ChatDetailPaginateResource($chatDetail)
        ]);

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

        } else {
//            $status = ChatStatusEnum::JUST_CREATED;

            // insert chat
//            $chat = $user->chats()->create([
//                'status' => $status,
//                'new_message_by_support' => 0,
//                'new_message_by_user' => 1
//            ]);
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

        return $this->created([
            'active_chat' => ChatResource::make($chat)
        ]);

        /*
        $chatDetail = $chat->chatDetails()->create([
           'content' => $content,
            'user_id' => $user->id,
            'type' => ChatDetailTypeEnum::MESSAGE
        ]);

        if($request->hasFile('files')) {
            $validator = Validator::make($request->all(), [
                'files.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,pdf,mp3,wav,m4a,m4v,webm',
            ]);

            if ($validator->fails()) {
                return $this->requestException([
                    'status' => false,
                    'message' => 'فرمت فایل های ارسالی اشتباه است',
                    'errors' => $validator->errors()->all()
                ]);
            }

            $files =  $request->file('files');
            $this->uploadFiles($user, $files, $chatDetail);
        }
        */

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
        return $this->ok([
            'data' => new ChatPaginateResource($chats)
        ]);
    }

    private function checkActiveChat()
    {
        $user = auth()->user();
        return $user->chats()->where('status', '<>', ChatStatusEnum::CLOSED)->where('ban', false)->orderByDesc('id')->first();
    }

    private function questionList()
    {
        return [
            [
                'id' => 1,
                'title' => 'مشکل در نوبتدهی حضوری ',
                'answer' => null,
                'answer_detail' => [],
                'children' => [
                    [
                        'id' => 11,
                        'title' => 'نوبت ها پر است ',
                        'children' => [
                            [
                                'id' => 12,
                                'title' => null,
                                'children' => [],
                                'answer' => 'مراجعه کننده گرامی به علت محدودیت در نوبت ها لطفا در روز دیگری تلاش به دریافت نوبت کنید و یا برای تسریع در روند شروع درمان ، نوبت ویزیت آنلاین دریافت کنید',
                                'answer_detail' => [
                                    'title_link' => 'دریافت نوبت آنلاین',
                                    'link' => 'on;ine_appointment',
                                    'image' => null,
                                    'video' => null
                                ]
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                    [
                        'id' => 13,
                        'title' => 'برای روز دیگری نوبت می خواهم ',
                        'children' => [
                            [
                                'id' => 14,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی نوبت به شما نشان داده شده اولین نوبت خالی است ، برای روزهای دیگر گزینه ( نوبت های دیگر ) را بزنید و اگر روز مدنظر در جدول نیست در روزهای آینده تلاش کنید',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => url('storage/help/chat/help.png'),
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => [],
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                    [
                        'id' => 15,
                        'title' => 'بیشتر از یک نوبت می خواهم ',
                        'children' => [
                            [
                                'id' => 16,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی هر شماره همراه / کدملی مجاز به دریافت یک نوبت می باشد. برای دریافت نوبت به نام شخص دیگر لطفا اطلاعات آن شخص را وارد کنید. ',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => url('storage/help/chat/help.png'),
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => [],
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                    [
                        'id' => 17,
                        'title' => 'ثبت نشدن نوبت دریافت شده  ',
                        'children' => [
                            [
                                'id' => 18,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی اگر پس از ثبت دریافت نوبت به شما کد رهگیری اختصاص داده نشد ، لطفا مجدد تلاش کنید و اگر پیغام پر شدن نوبت ها نمایش داده شد در روزهای آینده اقدام به دریافت نوبت کنید',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => null,
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => [],
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                ],
            ],
            [
                'id' => 2,
                'title' => 'مشکل در نوبتدهی ویزیت آنلاین ( غیر حضوری )  ',
                'children' => [
                    [
                        'id' => 19,
                        'title' => 'پس از درخواست چه زمانی نوبت ثبت می شود؟   ',
                        'children' => [
                            [
                                'id' => 20,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی از زمانی که هزینه ویزیت آنلاین را پرداخت می کنید ، صفحه چت برای شما باز می شود . پیام ها و مدارک خود را بارگذاری کنید و منتظر پاسخ پزشک باشید ',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => null,
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => []
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                    [
                        'id' => 21,
                        'title' => 'برای روز دیگری نوبت آنلاین می خواهم',
                        'children' => [
                            [
                                'id' => 22,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی درخواست ویزیت  آنلاین شما برای همان روز می باشد ، اگر قصد ویزیت در روز دیگری را دارید در همان روز اقدام به دریافت ویزیت آنلاین بفرمایید  ',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => null,
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => []
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],

                    [
                        'id' => 23,
                        'title' => 'ویزیت آنلاین به چه صورت انجام می شود؟',
                        'children' => [
                            [
                                'id' => 24,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی ویزیت آنلاین به صورت متنی می باشد ، پس از ثبت درخواست و پرداخت هزینه صفحه چت برای شما باز می شود ، پیام ها و مدارک خود را بارگذاری کرده و منتظر پاسخ پزشک طی 24 ساعت آینده باشید.',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => null,
                                    'video' => url('storage/help/chat/sample.mp4'),
                                    'show_chat_button' => true,
                                ],
                                'children' => []
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                    [
                        'id' => 25,
                        'title' => 'پیغام پر شدن نوبت یا غیر فعال بودن نوبت ویزیت آنلاین نمایش داده می شود.',
                        'children' => [
                            [
                                'id' => 26,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی اگر هر کدام از پیغام های فوق را مشاهده کردید ، در روز دیگری اقدام به دریافت ویزیت آنلاین بفرمایید.',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => null,
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => []
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                    [
                        'id' => 27,
                        'title' => 'آیا در ویزیت آنلاین می توانم عکس مدارک بفرستم و نسخه الکترونیکی دریافت کنم؟',
                        'children' => [
                            [
                                'id' => 28,
                                'title' => null,
                                'answer' => 'بله – از گزینه بغل صفحه (طبق عکس زیر) عکس مدارک را بارگذاری نمایید ، در صورت نیاز به دارو و یا آزمایش پزشک ، هم به صورت عکس نسخه کاغذی  و هم ثبت الکترونیک برای شما نسخه صادر می کند.',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => url('storage/help/chat/help.png'),
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => []
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                    [
                        'id' => 29,
                        'title' => 'مشکل در پرداخت دارم',
                        'children' => [
                            [
                                'id' => 30,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی پرداخت هزینه ویزیت آنلاین فقط و فقط از دزگاه بانکی انجام می شود و نیازمند رمز پویا می باشد',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => null,
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => []
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                    [
                        'id' => 31,
                        'title' => 'در حین ویزیت صفحه چت بسته شده ',
                        'children' => [
                            [
                                'id' => 31,
                                'title' => null,
                                'answer' => 'مراجعه کننده گرامی اگر ویزیت شما به اتمام رسیده و پزشک صفحه چت را بسته لطفا برای ارسال مدارک
 بعدی مجددا اقدام به دریافت نوبت آنلاین بفرمایید.
اگر حین صحبت ، چت بسته شده است و هنوز جواب کامل دریافت نکردید از طریق کادر زیر درخواست خود
را ثبت نمایید تا همکاران ما در اسرع وقت پاسخ شما را بدهند .',
                                'answer_detail' => [
                                    'title_link' => null,
                                    'link' => null,
                                    'image' => null,
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => []
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],

                ],
                'answer' => null,
                'answer_detail' => []
            ],
            [
                'id' => 3,
                'title' => 'سایر مشکلات ',
                'children' => [
                    [
                        'id' => 33,
                        'title' => 'چگونه وضعیت نوبت را پیگیری کنم؟',
                        'children' => [
                            [
                                'id' => 34,
                                'title' => null,
                                'answer' => 'برای پیگیری وضعیت نوبت به بخش نوبت های من مراجعه کنید',
                                'answer_detail' => [
                                    'title_link' => 'ورود به نوبت های من',
                                    'link' => 'appointment_lists',
                                    'image' => url('storage/help/chat/help.png'),
                                    'video' => null,
                                    'show_chat_button' => true,
                                ],
                                'children' => []
                            ]
                        ],
                        'answer' => null,
                        'answer_detail' => []
                    ],
                ],
                'answer' => null,
                'answer_detail' => []
            ]

        ];
    }
}
