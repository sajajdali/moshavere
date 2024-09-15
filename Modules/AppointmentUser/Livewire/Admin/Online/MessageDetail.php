<?php

namespace Modules\AppointmentUser\Livewire\Admin\Online;

use App\Enum\RouteEnum;
use Livewire\Component;
use App\Models\ShortLink;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\User\app\Notifications\UserSmsNotification;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessage;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessageFile;
use Modules\AppointmentUser\app\Notifications\AppointmentUserFeedbackSmsnotification;

class MessageDetail extends Component
{
    use WithFileUploads;
    #[Url]
    public $search;
    public array $form = [];
    public array $fetchData = [];
    public function runSearch()
    {
        $this->getMessages();
    }
    #[On('fileHasUpload')]
    public function storeRecordedVoice()
    {
        if (isset($this->form['voice'])) {
            $fileUrl = Storage::url($this->form['voice']);
            $extension = pathinfo($fileUrl, PATHINFO_EXTENSION);
            $model = [
                'appointment_online_id' =>  $this->fetchData['appOnline']->id,
                'user_id'               =>  $this->fetchData['user']->id,
                'answer_by'             =>  auth()->user()->id,
                'type'                  =>  AppointmentOnlineMessageTypeEnum::ANSWER,
                'seen'                  =>  AppointmentOnlineMessageSeenEnum::UNSEEN,
                'body'                  =>  isset($this->form['typedMessage']) ? $this->form['typedMessage'] : '',
            ];
            $AOM =  AppointmentOnlineMessage::create($model);
            $fileModel = [
                'user_id' => $this->fetchData['user']->id,
                'answer_by' => auth()->user()->id,
                'fk_id' => $AOM->id,
                'original_name' => $this->form['voice'],
                'server_name' => $this->form['voice'],
                'disk' => 'public',
                'path' => $this->form['voice'],
                'extension' => $extension,
                'mime' => 'mp3',
                'size' => 10,
            ];
            AppointmentOnlineMessageFile::create($fileModel);
            $this->addError('success', 'ویس با موفقیت ارسال شد');
            $this->fetchData['messages'] = $this->fetchData['appOnline']->messages;
            $this->dispatch('sendMessage', true);
        }
    }

    public function ignoreSearch()
    {
        unset($this->search);
        $this->fetchData['messages'] = $this->fetchData['appOnline']->messages;
        $this->dispatch('ignoreSearch', true);
    }

    public function sendMessage()
    {
        $this->validate([
            'form.typedMessage' => 'required_without_all:form.file,form.capturedPic',
        ]);
        $model = [
            'appointment_online_id' =>  $this->fetchData['appOnline']->id,
            'user_id'               =>  $this->fetchData['user']->id,
            'answer_by'             =>  auth()->user()->id,
            'type'                  =>  AppointmentOnlineMessageTypeEnum::ANSWER,
            'seen'                  =>  AppointmentOnlineMessageSeenEnum::UNSEEN,
            'body'                  =>  isset($this->form['typedMessage']) ? $this->form['typedMessage'] : '',
        ];
        $AOM =  AppointmentOnlineMessage::create($model);
        if (isset($this->form['file'])) {
            $url = $this->form['file'];
            // Parse the URL
            $parsedUrl = parse_url($url);

            // Get the file path
            $filePath = str_replace('/storage/', '', $parsedUrl['path']);
            // Get the file name
            $fileName = basename($filePath);

            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            // Get the file mime type
            $fileMime = Storage::mimeType('/storage/' . $filePath);
            // Get the disk
            $fileDisk = 'public';

            $fileModel = [
                'user_id' => $this->fetchData['user']->id,
                'answer_by' => auth()->user()->id,
                'fk_id' => $AOM->id,
                'original_name' => $fileName,
                'server_name' => $fileName,
                'disk' => $fileDisk,
                'path' => $filePath,
                'extension' => $extension,
                'mime' => $extension,
                'size' => 1,
            ];
            AppointmentOnlineMessageFile::create(attributes: $fileModel);
            unset($this->form['file']);
        }
        if (isset($this->form['capturedPic'])) 
        {
            $filePath = $this->form['capturedPic']->store('public/uploads');
            $fileName = basename($filePath);
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $fileMime = Storage::mimeType($filePath);
            $fileDisk = 'public';
            $fileModel = [
                'user_id' => $this->fetchData['user']->id,       // Set the user ID
                'answer_by' => auth()->user()->id,              // Authenticated user (answerer)
                'fk_id' => $AOM->id,                            // Foreign key to appointment/message
                'original_name' => $fileName,                   // Original file name
                'server_name' => $fileName,                     // Name used in storage (same in this case)
                'disk' => $fileDisk,                            // Disk used for storage
                'path' => str_replace('public/', '', $filePath), // File path relative to storage
                'extension' => $extension,                      // File extension (jpg, png, etc.)
                'mime' => $fileMime,                            // MIME type (e.g., image/jpeg)
                'size' => $this->form['capturedPic']->getSize(), // File size (in bytes)
            ];
            AppointmentOnlineMessageFile::create($fileModel);
            unset($this->form['capturedPic']);
        }
        $notificationMessage =  isset($this->form['typedMessage']) ? substr($this->form['typedMessage'], 0, 50) : 'یک پیام جدید دارید';
        unset($this->form['typedMessage']);
        $this->addError('success', 'پیام با موفقیت ارسال شد');
        $this->fetchData['messages'] = $this->fetchData['appOnline']->messages;
        if (isset($this->form['sendSms']) && $this->form['sendSms'] == true) {
            $template = setting(SettingKeyEnum::SMS_FOR_SEND_MESSAGE_IN_CHATS);
            if (isset($template)) {
                $messageLink = 'https://webapp.mata-app.com' . (\App\Enum\RouteEnum::ONLINE_MESSAGE->getLink($this->fetchData['appOnline']->id));
                $shortLink =  $this->fetchData['appOnline']->shortLink()->create([
                    'link_code' => ShortLink::generateShortLinkCode(),
                    'link_url'  => $messageLink,
                ]);
                $this->fetchData['user']->notify(new UserSmsNotification($template, url('/s/' . $shortLink->link_code)));
            }
            unset($this->form['sendSms']);
        }
        try {
            $this->fetchData['user']->notify(new \Modules\User\Notifications\UserMessageNotification(
                title: "پیام جدید برای نوبت آنلاین!",
                excerpt: $notificationMessage,
                message: '',
                link: \App\Enum\RouteEnum::CHAT->getLink($this->fetchData['appOnline']->id),
            ));
        } catch (\Throwable $th) {
        }

        $this->dispatch('sendMessage', true);
    }
    public function messages()
    {
        return [
            'form.typedMessage.required_without_all' => 'لطفا پیام را وارد کنید',
        ];
    }
    #[Computed]
    public function getMessagesBodys()
    {
        if (isset($this->search)) {
            $this->fetchData['messages'] =  $this->fetchData['appOnline']->messages()->where('body', 'LIKE', "%{$this->search}%")->get();
        }
        if (isset($this->fetchData['messages']) && !empty($this->fetchData['messages'])) {
            $temp = $this->fetchData['messages']->groupBy(function ($messages) {
                return verta($messages->created_at)->format('%B %d، %Y');
            });
        }
        return $temp;
    }
    public function ignoreDisaproveModal()
    {
        if (isset($this->form['reason'])) {
            unset($this->form['reason']);
        }
    }
    public function approvedAppointment()
    {
        $this->fetchData['appOnline']->appointmentUser()->update(['status' => AppointmentOnlineStatusEnum::ACCEPTED]);
        $this->fetchData['appOnline']->update(['status' => AppointmentOnlineStatusEnum::ACCEPTED]);
        return redirect()->route('admin.appointment_user.message.detail', $this->fetchData['appOnline']->id);
    }
    public function disaprovedModal()
    {

        if (isset($this->form['reason'])) {
            $detail =  $this->fetchData['appOnline']->appointmentUser->details;
            if (isset($detail)) {
                $detail = array_merge($detail, [AppointmentUser::DISAPPROVED_DESCRIPTION => $this->form['reason']]);
            } else {
                $detail = [AppointmentUser::DISAPPROVED_DESCRIPTION => $this->form['reason']];
            }
        }
        $this->fetchData['appOnline']->appointmentUser()->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL]);
        $this->fetchData['appOnline']->update(['status' => AppointmentOnlineStatusEnum::REJECT]);

        return redirect()->route('admin.appointment_user.message.detail', $this->fetchData['appOnline']->id);
    }

    public function cancelAppointment()
    {
        $this->fetchData['appOnline']->update(['status' => AppointmentOnlineStatusEnum::CANCEL]);
        $this->fetchData['appOnline']->appointmentUser()->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL]);
        return redirect()->route('admin.appointment_user.message.detail', ['onlineAppId' => $this->fetchData['appOnline']->id])->with('success', 'نوبت با موفقیت کنسل شد');
    }
    public function closeApp()
    {

        $appointmentUser = $this->fetchData['appOnline']->appointmentUser;
        $link_code = ShortLink::generateShortLinkCode();
        $link_url = route('front.feedBack', ['appointmentUser_id' => $appointmentUser->id, 'user_id' => $appointmentUser->user->id]);
        ShortLink::create([
            'link_code' => $link_code,
            'link_url'  => $link_url,
            'shortlinkable_type'  => 'feedBack',
            'shortlinkable_id'  => $appointmentUser->id,
        ]);
        $smsTemplate = setting(SettingKeyEnum::SMS_FEEDBACK);
        if (isset($smsTemplate)) {
            $appointmentUser->notify(new AppointmentUserFeedbackSmsnotification($smsTemplate, $link_code));
        }
        Cache::forget('appointmentList.' . $appointmentUser->id);

        $this->fetchData['appOnline']->update(['status' => AppointmentOnlineStatusEnum::COMPLETED_BY_DOCTOR]);
        $this->fetchData['appOnline']->appointmentUser()->update(['status' => AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED]);
        return redirect()->route('admin.appointment_user.message.detail', ['onlineAppId' => $this->fetchData['appOnline']->id])->with('success', 'وضعیت نوبت به تمام شده ، تغییر پیدا کرد');
    }
    public function updateComponent() {
        $this->render();
    }
    public function mount()
    {
        $this->fetchData['appOnline'] = AppointmentOnline::find(request()->route('onlineAppId'));

        $this->fetchData['messages']  = $this->fetchData['appOnline']->messages;
        $this->fetchData['appOnline']->messages()
            ->where('type', AppointmentOnlineMessageTypeEnum::QUESTION)
            ->where('seen', AppointmentOnlineMessageSeenEnum::UNSEEN)
            ->update(['seen' => AppointmentOnlineMessageSeenEnum::SEEN]);
        $this->fetchData['user']      =  $this->fetchData['appOnline']->user;
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.online.message-detail');
    }
}
