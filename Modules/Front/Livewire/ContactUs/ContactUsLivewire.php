<?php

namespace Modules\Front\Livewire\ContactUs;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\Front\app\Models\Contactus;
use Modules\User\Entities\User;
use Modules\Front\app\Models\Faq;
use Modules\Setting\Enum\SettingKeyEnum;


#[Layout('front::layouts.app')]
#[Title('تماس با ما')]

class ContactUsLivewire extends Component
{
    #[Locked]
    public array $fetchData = [];
    public array $form = [];

    public ?User $user;

    public function sendSupportMessage()
    {
        if (isset($user)) {
            $rules = [
                'form.message' => 'required|string|max:1500'
            ];
        } else {
            $rules = [
                'form.mobile' => 'required|digits:11',
                'form.full_name' => 'required|string|max:225',
                'form.message' => 'required|string|max:1500',
            ];
        }
        $this->validate($rules);
        if (isset($user)) {
            $model = [
                'user_id' => $this->user->id,
                'body' => $this->form['message'],
            ];
        } else {
            $model = [
                'name' => $this->form['full_name'],
                'mobile' => $this->form['mobile'],
                'body' => $this->form['message'],
            ];
        }
        Contactus::create($model);
        $this->form = [];
        // TODO::addAlert
    }
    public function mount()
    {
        $this->fetchData['first_section_show']    = setting(SettingKeyEnum::CONTACTUS_FIRST_SECTION_STATUS);
        $this->fetchData['first_section_title']   = setting(SettingKeyEnum::CONTACTUS_FIRST_SECTION_TITLE);
        $this->fetchData['first_section_body']    = setting(SettingKeyEnum::CONTACTUS_FIRST_SECTION_BODY);
        $this->fetchData['formActiveStatus']      = setting(SettingKeyEnum::CONTACTUS_FORM_STATUS);
        $this->fetchData['address']               = setting(SettingKeyEnum::CONTACTUS_FORM_ADDRESS);
        $this->fetchData['email']                 = setting(SettingKeyEnum::CONTACTUS_FORM_SUPPORT_EMAIL);
        $this->fetchData['faqs'] = Faq::all();
        $this->user = auth()->user();
    }
    public function render()
    {
        return view('front::livewire.contact-us.contact-us-livewire');
    }
}
