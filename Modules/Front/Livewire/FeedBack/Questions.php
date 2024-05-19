<?php

namespace Modules\Front\Livewire\FeedBack;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\Front\app\Models\FeedBack;
use Modules\AppointmentUser\app\Models\AppointmentUser;

#[Layout('front::layouts.app')]
class Questions extends Component
{
    #[Locked]
    public array $fetchData = [
        'formAlreadyCopelete' => false
    ];
    public  $step;

    #[Locked]
    public array $form = [];

    #[Locked]
    public bool $feedBackCompelete = false;

    public $appintment_user_id;

    public function nxtQuestion($key)
    {
        if ($key === null) {
            $this->addError('selectAwnser', true);
        } else {
            $this->form[$this->fetchData['questions'][$this->step]['id']->value] = $key;
            if (count($this->fetchData['questions']) > $this->step + 1) {
                $this->step++;
            } else {
                $this->storeAnswers();
                $this->feedBackCompelete = true;
            }
        }
    }
    private function storeAnswers()
    {
        foreach ($this->form as $question => $answer) {
            FeedBack::create([
                'appointment_user_id' => $this->appintment_user_id,
                'question'            => $question,
                'answer'              => $answer,
            ]);
        }
    }
    public function mount()
    {
        $app_id = request()->route('appointmentUser_id');
        $appId =  AppointmentUser::find($app_id);
        if (!isset($app_id) ||  empty($appId)) {
            return abort('404');
        }
        if ($appId->feedbacks()->exists() ){
            $this->fetchData['formAlreadyCopelete'] = true ;
        }
        $this->appintment_user_id = $app_id;
        $this->step = 0;
        $this->fetchData['questions'] = feedbackQuestions();
    }
    public function render()
    {
        return view('front::livewire.feed-back.questions');
    }
}
