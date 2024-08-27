<?php

namespace Modules\Front\Livewire\FeedBack;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Front\app\Models\FeedBack;
use Modules\AppointmentUser\app\Models\AppointmentUser;

#[Layout('front::layouts.app')]
class Questions extends Component
{
    #[Locked]
    public array $fetchData = [
        'formAlreadyCopelete' => false
    ];
    public array $form = [];

    #[Locked]
    public bool $feedBackCompelete = false;

    public $appintment_user_id;

    public function messages() {
        return [
            'form.required' => 'لطفا یک گزینه را انتخاب کنید',
        ];
    }
    public function feedBackAnswered()
    {
        $this->resetErrorBag();
        $this->validate(['form' => 'required']);
        if(count($this->form) < count($this->fetchData['questions'] )) {
           return $this->addError('form' , 'به تمامی پرسش ها پاسخ داده نشده است');
        }
        foreach ($this->form as $question => $answer) {
            FeedBack::create([
                'appointment_user_id' => $this->appintment_user_id,
                'question'            => $question,
                'answer'              => $answer,
            ]);
        }
        $this->fetchData['formAlreadyCopelete'] = true ;
    }
    public function mount()
    {
        $user = User::find(request()->route('user_id'));
        $app_id = request()->route('appointmentUser_id');
        $appId =  AppointmentUser::find($app_id);
        if ($user->id != $appId->user_id ) {
            abort(403, 'Unauthorized action.');
        }

        if (!isset($app_id) ||  empty($appId)) {
            return abort('404');
        }
        if ($appId->feedbacks()->exists() ){
            $this->fetchData['formAlreadyCopelete'] = true ;
        }
        $this->appintment_user_id = $app_id;
        $this->fetchData['questions'] = feedbackQuestions();
    }
    public function render()
    {
        return view('front::livewire.feed-back.questions');
    }
}
