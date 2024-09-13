<?php

namespace Modules\Api\Http\Controllers\Appointment;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Api\app\Http\Requests\Api\Requests\Appointment\StoreAppointmentOnlineRequest;
use Modules\Api\app\Resources\Api\Appointment\online\AppointmentOnlineMessagesPaginateResource;
use Modules\Api\app\Resources\Api\Appointment\online\AppointmentOnlineMessagesResource;
use Modules\Api\app\Resources\Api\Appointment\online\AppointmentOnlinePaginateResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Validator;


class AppointmentApiOnlineController extends Controller
{
    use ApiHandlerTrait;

    private function uploadFiles($user , $files , $appointmentMessage)
    {
        foreach ($files as $file) {
            $orignName = $file->getClientOriginalName();
//            $extension = $file->getClientMimeType();
            $size = $file->getSize();

            $disk = 'appointment/online/' . $appointmentMessage->online->appointmentUser->id .'/' ;
            $name = $file->store($disk , 'public');

            $extension = pathinfo($name, PATHINFO_EXTENSION);

            $imageName = basename($name);
            $mime = strtok($extension, '/');
            $needConvert = false;
            if (strpos($orignName, "audio_123337") === 0) {
                $needConvert = true;
                $mime = 'mp3';
            }

            $file = $appointmentMessage->messageFile()->create([
                'user_id'   => $user->id,
                'original_name'   => $orignName,
                'server_name'   => $imageName,
                'disk'   => $disk,
                'path'   => $imageName,
                'extension'   => $extension,
                'mime'   => getValueAfterSlash($mime),
                'size'   => $size,
            ]);

            if ($needConvert) {
                convertVoiceFile('app/public/' .$file['disk'] . $file['path']  );
            }

        }
    }
    public function sendMessage(AppointmentOnline $appointmentOnline , Request $request)
    {
        $user = auth()->user();
        if ($user->id !=  $appointmentOnline->user->id){
            return $this->requestException([
                'status' => false,
                'message' => 'نوبت متعلق به این کاربر نیست'
            ]);
        }
//        if ($request->input('message') == null){
//            return $this->requestException([
//                'status' => false,
//                'message' => 'وارد کردن پیغام الزامی است'
//            ]);
//        }

        $type = AppointmentOnlineMessageTypeEnum::QUESTION;
        if ($request->has('type') && $request->get('type') == 2){
            $type = AppointmentOnlineMessageTypeEnum::ANSWER;
        }
        $message = $appointmentOnline->messages()->create([
            'user_id' => $user->id,
            'seen' => AppointmentOnlineMessageSeenEnum::UNSEEN,
            'type' => $type,
            'body' => $request->input('message')
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
            $this->uploadFiles($user, $files, $message);
        }
        $appointmentOnline->increment('new_messages');

        $status = AppointmentOnlineStatusEnum::REPLY_BY_USER;
        if ($request->has('type') && $request->has('type') == 2){
            $status = AppointmentOnlineStatusEnum::ANSWER_BY_DOCTOR;
        }

        $appointmentOnline->update([
            'status' => $status
        ]);

        return $this->ok([
            'status' => true,
            'message' => 'پیغام با موفقیت ارسال شد',
            'model' => AppointmentOnlineMessagesResource::make($message)
        ]);
    }

    public function messages(AppointmentOnline $appointmentOnline)
    {
        $user = auth()->user();
        if ($user->id !=  $appointmentOnline->user->id){
            return $this->requestException([
                'status' => false,
                'message' => 'نوبت متعلق به این کاربر نیست'
            ]);
        }
        $appointmentOnline->update(['new_messages' => 0]);
        $accessibility = [
            'can_send_message' => $appointmentOnline->status->canSendMessage(),
            'can_show_messages' => $appointmentOnline->status->canShowMessages(),
            'can_send_voice' => isset($appointmentOnline->setting->detail[AppointmentSetting::ONLINE_CAN_SEND_VOICE]) && $appointmentOnline->setting->detail[AppointmentSetting::ONLINE_CAN_SEND_VOICE]
        ];
        $messages = $appointmentOnline->messages()->orderByDesc('id')->paginate();
        $list = [
            'messages' => $messages,
            'accessibility' => $accessibility
        ];
        return $this->ok(new AppointmentOnlineMessagesPaginateResource($list));
    }
}
