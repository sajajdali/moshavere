<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal;

use Livewire\Component;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;

class SpecificDayAppointmentRegistrationModal extends Component
{
    public array $form = [
        "number" => null,
        "document" => null,
        "first_name" => null,
        "last_name" => null,
    ];
    public array $fetchData = [];
    public $step = 1;

    public function numberSet()
    {
        if ($this->step == 1) {
            $this->findeOrCreateUser();
        } elseif ($this->step == 2) {
            $this->validate([
                'form.first_name' => 'required',
                'form.last_name' => 'required',
            ]);
            $this->dispatch('closeModal', true);
            $this->step = 1;
            $this->form = [
                "number" => null,
                "document" => null,
            ];
        }
    }
    private function findeOrCreateUser()
    {
        $this->validate([
            'form.number' => 'required_if:form.document_number,null|digits:11|nullable',
            'form.document_number' => 'required_if:form.number,null',
        ]);

        if (isset($this->form['mobile'])) {
            $user = User::where('mobile', $this->form['mobile'])->first();
            if (!empty($user)) {
                $this->fetchData['user'] = $user;
            } else {
                $this->fetchData['tempUser'] = $this->form['mobile'];
            }
        } elseif (isset($this->form['document_number'])) {

            $user =  User::whereHas('metas', function ($q) {
                return $q->where('meta_key', UserMetaEnum::DOCUMENT_NUMBER)->where('meta_value', $this->form['document_number']);
            })->first();
            if (!empty($user)) {
                $this->fetchData['user'] = $user;
            } else {
                $this->addError('userNotExists', 'کاربری یافت نشد ، لطفا برای ایجاد کاربر با این شماره پرونده ، شماره تلفن را نیز وارد کنید!');
            }
        }
        // $this->step  = $this->step + 1;
    }
    // public function storeAppointmentByAdmin()
    // {
    //     $user = auth()->user();
    //     $appointmentSetting = AppointmentSetting::findOrFail($request->input('appointment_setting_id'));

    //     // If he wants to take the turn for someone else
    //     $foHimself = $request->input('form_himself');
    //     $someoneModel = null;
    //     if ($foHimself == 2) {
    //         $someoneModel = new UserModel(
    //             firstName: $request->input('someone_first_name'),
    //             lastName: $request->input('someone_last_name'),
    //             mobile: $request->input('someone_mobile'),
    //             gender: $request->input('someone_gender'),
    //             age: $request->input('someone_age'),
    //             nationalCode: $request->input('someone_national_code')
    //         );
    //     }

    //     // main user data
    //     $mainUser = new UserModel(
    //         user: $user,
    //         firstName: $request->input('first_name'),
    //         lastName: $request->input('last_name'),
    //         gender: $request->input('gender'),
    //         age: $request->input('age'),
    //         nationalCode: $request->input('national_code'),
    //         address: $request->input('address'),
    //         city: $request->input('city')
    //     );

    //     // full user model
    //     $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel);

    //     // appointment model
    //     $appointmentModel = new AppointmentModel(
    //         timestamp: $request->input('timestamp'),
    //         appointmentVia: AppointmentVia::SELF,
    //         sendSmsToUser: true,
    //         serviceId: $request->input('service_id'),
    //         placeId: $request->input('place_id'),
    //     );

    //     $detail = [];
    //     if ($request->input('question')) {
    //         $detail[AppointmentUser::DETAIL_QUESTION] = $request->input('question');
    //     }

    //     $storeAppointment = app('AppointmentUserService')->storeAppointment($appointmentSetting, $userModelAppointment, $appointmentModel, $detail);
    // }
    public function messages()
    {
        return [
            'form.number.required_if' => 'لطفا یکی از فیلد ها را تکمیل کنید',
            'form.number.digits' => 'شماره موبایل صحیح نیست!',
            'form.document.required_if' => 'لطفا یکی از فیلد ها را تکمیل کنید',
            'form.first_name.required' => 'وارد کردن نام الزامی است',
            'form.last_name.required' => 'وارد کردن نام خانوادگی الزامی است',
        ];
    }

    public function render()
    {
        return view('appointmentuser::livewire.admin.add-appointment.modal.specific-day-appointment-registration-modal');
    }
}
