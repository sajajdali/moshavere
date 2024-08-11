<?php

namespace Modules\AppointmentUser\Livewire\Admin\Online;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Storage;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Traits\OprationButtonsTrait;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessage;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessageFile;

class MessageDetail extends Component
{
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
            $fileUrl = Storage::disk('public')->url($this->form['voice']);
        }
        $p = explode('/', $this->form['voice']);
        // $mimeType = Storage::mimeType($this->form['voice']);
        $size  =  ceil((Storage::size($this->form['voice'])) / 1024);
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
            'original_name' => $p[3],
            'server_name' => $p[3],
            'disk' => $p[0],
            'path' => $this->form['voice'],
            'extension' => $extension,
            'mime' => 'mp3',
            'size' => $size,
        ];
        AppointmentOnlineMessageFile::create($fileModel);
        $this->addError('success', 'ویس با موفقیت ارسال شد');
        $this->fetchData['messages'] = $this->fetchData['appOnline']->messages;
        $this->dispatch('sendMessage', true);
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
            'form.typedMessage' => 'required_without:form.file',
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

            // Get the file mime type
            $fileMime = Storage::mimeType('/public/' . $filePath);
            // Get the file size
            $fileSizebyte = Storage::size('/public/' . $filePath);
            $fileSize = $fileSizebyte / 1024;
            // Get the disk
            $fileDisk = 'public';
            $extension = pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);

            $fileModel = [
                'user_id' => $this->fetchData['user']->id,
                'answer_by' => auth()->user()->id,
                'fk_id' => $AOM->id,
                'original_name' => $fileName,
                'server_name' => $fileName,
                'disk' => $fileDisk,
                'path' => $filePath,
                'extension' => $extension,
                'mime' => $fileMime,
                'size' => $fileSize,
            ];
            AppointmentOnlineMessageFile::create($fileModel);
            unset($this->form['file']);
        }
        unset($this->form['typedMessage']);
        $this->addError('success', 'پیام با موفقیت ارسال شد');

        $this->fetchData['messages'] = $this->fetchData['appOnline']->messages;

        $this->fetchData['user']->notify(new \Modules\User\Notifications\UserMessageNotification(
            title: "پیام جدید!",
            excerpt: 'یک پیام جدید دارید',
            message: '',
            link: \App\Enum\RouteEnum::ONLINE_MESSAGE->getLink($this->fetchData['appOnline']->id),
        ));

        $this->dispatch('sendMessage', true);
    }
    public function messages()
    {
        return [
            'form.typedMessage.required_without' => 'لطفا پیام را وارد کنید',
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

    public function cancelAppointment(){
        $this->fetchData['appOnline']->update(['status' => AppointmentOnlineStatusEnum::CANCEL]);
        return redirect()->route('admin.appointment_user.message.detail',['onlineAppId'=>$this->fetchData['appOnline']->id])->with('success', 'نوبت با موفقیت کنسل شد');
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
