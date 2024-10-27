<?php

namespace Modules\Front\Livewire\DoctorProfile;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Modules\Front\app\Models\Comment;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Front\enum\CommentStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

#[Layout('front::layouts.app')]
class DoctorProfileLivewire extends Component
{

    #[Locked]
    public User $doc;
    #[Locked]
    public array $fetchData = [];
    public array $form = [
        'comment' => ['rate' => 5]
    ];

    #[Locked]
    public string $appointmentType;
    public function reserveAppointment($type = 'IN_PERSON')
    {
        $this->appointmentType = $type ;
        // check if doctor was not banned
        if ($this->isDocAvaiable()) {
            // check for palce count
            if ($this->doc->activePlaces()->count() > 1) {
                // check if place has selected in route
                if (!isset($this->form['place']) || empty($this->form['place'])) {
                    $this->fetchData['places'] = $this->doc->activePlaces();
                    $this->fetchData['modalStep'] = 1;
                    if (isset($this->fetchData['services'])) {
                        unset($this->fetchData['services']);
                    }
                    if (isset($this->fetchData['place'])) {
                        unset($this->fetchData['place']);
                    }
                }
                return  $this->lunchModal();
            } elseif ($this->doc->activePlaces()->count() <= 1) {

                // if ONE place exist
                $place = $this->doc->activePlaces()->first();
                $this->form['place_name'] = $place->title;
                $this->form['place'] = $place->id;

                // check for service count
                if ($this->doc->activeServices()->count() <= 1) {
                        return  $this->redirectToAppointmentDays(
                            $this->doc->id,
                            $place->id,
                            $this->doc->activeServices()->first()->id
                        );
                }
                $this->fetchData['services'] = $this->doc->activeServices();
                $this->fetchData['modalStep'] = 2;
                return $this->lunchModal();
            }

            // if less than ONE service exist , redirect to appointment days list
                $this->redirectToAppointmentDays(
                    $this->doc->id,
                    $this->doc->places()->first()->id,
                    $this->doc->services()->first()->id
                );

        }
    }
    public function reserveOnlineAppointment() {}
    public function lunchModal()
    {
        return $this->dispatch('lucnhModal', true);
    }
    private function redirectToAppointmentDays($doctor_id, $place_id, $service_id, $segment = null)
    {
        $param = [
            'doctor_id'     => $doctor_id,
            'place_id'      => $place_id,
            'service_id'    => $service_id

        ];
        if (!empty($segment)) {
            $param['segment'] = $segment;
        }
        if($this->appointmentType == 'IN_PERSON') {
            $route = 'front.setAppointment.days';
        }else{
            $route = 'front.setAppointment.online.description' ;
        }
        return redirect()->route(
            $route,
            $param
        );
    }
    public function modalSubmit()
    {
        if ($this->fetchData['modalStep'] == 1) {
            if (isset($this->form['place'])) {
                $this->form['place_name'] = Place::find($this->form['place'])?->title ?? '';
                $this->fetchData['services'] = $this->doc->activeServices();
                if (isset($this->form['service']) && !empty($this->form['service'])) {
                    // user selected service on privous page
                    $this->serviceHasSelected();
                    $this->fetchData['modalStep']++;
                } else {
                    $this->form['place'] = $this->doc->activePlaces()->first()->id;
                    $this->fetchData['modalStep']++;
                    if ($this->doc->activeServices()->count() <= 1) {
                        $this->redirectToAppointmentDays(
                            $this->doc->id,
                            $this->form['place'],
                            $this->doc->activeServices()->first()->id
                        );
                    }
                }
            } else {
                $this->fetchData['modalStep'] =  1;
            }
        } elseif ($this->fetchData['modalStep'] == 2) {
            if (isset($this->form['segment'])) {
                if (count($this->form['segment']) > 1) {
                    foreach ($this->form['segment'] as $segmentId => $status) {
                        if ($status) {
                            $this->form['selectedSegmentForRoute'][] = $segmentId;
                        }
                    }
                } else {
                    $this->form['selectedSegmentForRoute'] = $this->form['segment'];
                }
                $this->redirectToAppointmentDays(
                    $this->doc->id,
                    $this->form['place'],
                    $this->form['service'],
                    $this->form['selectedSegmentForRoute'],
                );
            } else {
                if (isset($this->form['service'])) {
                    $this->redirectToAppointmentDays(
                        $this->doc->id,
                        $this->form['place'],
                        $this->form['service']
                    );
                } else {
                    $this->dispatch('swalError', msg: 'لطفا بخش مورد نظر خود را انتخاب کنید!');
                }
            }
        }
    }

    public function serviceHasSelected()
    {
        if (isset($this->form['service'])) {
            $app_setting = AppointmentSetting::where('user_id', $this->doc->id)->where('place_id', $this->form['place'])->where('service_id', $this->form['service'])->first();
            if (empty($app_setting)) {
                $app_setting = AppointmentSetting::where('user_id', $this->doc->id)->whereNull('place_id')->whereNull('service_id')->first();
            }
            if ($app_setting->segments->count()) {
                $segment = $app_setting->segments()->first();
                if ($segment->multiple_choice == "1") {
                    // segment has one choise
                    $this->fetchData['multiple_choice'] = false;
                } else {
                    // segment has multiple choise
                    $this->fetchData['multiple_choice'] = true;
                }
                $this->fetchData['segments'] = $segment->items()->where('display_on_site', true)->orderBy('priority')->get();
            } else {
                $this->redirectToAppointmentDays(
                    $this->doc->id,
                    $this->form['place'],
                    $this->form['service']
                );
            }
        }
    }
    public function editPlace()
    {
        if (isset($this->fetchData['services'])) {
            unset($this->fetchData['services']);
        }
        if (isset($this->fetchData['place'])) {
            unset($this->fetchData['place']);
        }
        $this->fetchData['places'] = $this->doc->activePlaces();
        $this->fetchData['modalStep'] = 1;
    }

    // check is user redirect to this page with service_id and place_id
    private function routeHasServiceOrPlace()
    {
        if (! $this->doc->isDoctorActive()) {
            // if doc is de active , prevent modal from opening
            return;
        }
        if (request()->has('service_id')) {
            $santetizeService = htmlspecialchars(request()->input('service_id'), ENT_QUOTES, 'UTF-8');
            $this->form['service'] =  Service::where('active', ActiveEnum::ACTIVE)->firstWhere('id', $santetizeService)?->id ?? null;
        }
        if (request()->has('place_id')) {
            $santetizeService = htmlspecialchars(request()->input('place_id'), ENT_QUOTES, 'UTF-8');
            $place = Place::where('active', ActiveEnum::ACTIVE)->where('id', $santetizeService)->first() ?? null;
            if (isset($place) && !empty($place)) {
                $this->form['place'] = $place->id;
                $this->form['place_name'] = $place->title;
                $this->fetchData['services'] = $this->doc->activeServices();
                $this->fetchData['modalStep'] = 2;
            }
        }
        // service has selected
        if (isset($this->form['service']) && !isset($this->form['place_id'])) {
            $this->fetchData['modalStep'] = 1;
            $this->fetchData['places'] = $this->doc->activePlaces();
        }
        if (isset($this->form['service']) && isset($this->form['place_id'])) {
            $this->serviceHasSelected();
            $this->fetchData['modalStep'] = 2;
        }
    }
    public function addComment()
    {
        // Check if the user is logged in
        if (!auth()->check()) {
            $route = route('front.doctor.profile', ['doctor_id' =>  $this->doc->id, 'doctor_name' => str_replace(' ', '_', $this->doc->full_name)]);
            session()->put('url.intended', $route);
            return redirect()->route('front.login.user', ['comment' => true]);
        }

        $this->validate([
            'form.comment.body' => 'required|string|max:500',
            'form.comment.rate' => 'nullable',
        ]);
        $userHasComment = Comment::where('user_id', auth()->user()->id)->where('doctor_id', $this->doc->id)->where('status', CommentStatusEnum::PENDING)->exists();
        if (isset($userHasComment) && $userHasComment == true) {
            $this->addError('CommentSuccess', 'شما قبلا یک نظر برای این پزشک ثبت کرده اید برای ثبت نظر مجدد، صبر کنید تا نظر قبلی شما تایید شود.');
        } else {
            $commentModel = [
                'user_id' => auth()->user()->id,
                'doctor_id' => $this->doc->id,
                'body' => $this->form['comment']['body'],
                'status' => CommentStatusEnum::PENDING,
            ];
            if (isset($this->form['comment']['rate'])) {
                $commentModel['star'] = $this->form['comment']['rate'];
            }
            Comment::create($commentModel);
            $this->addError('CommentSuccess', 'نظر شما با موفقیت ثبت شد و بعد از تایید در سایت نمایش داده میشود');
        }
    }

    public function loadMoreComment()
    {
        $totallComments =  count($this->fetchData['comments']);
        if ($totallComments >  $this->fetchData['iteratorComments'] + 2) {
            $this->fetchData['iteratorComments']  = $this->fetchData['iteratorComments'] + 2;
        } else {
            $this->fetchData['iteratorComments'] = $totallComments;
            $this->fetchData['iteratorStop'] = true;
        }
    }
    public function addFavarite()
    {
        // Check if the user is logged in
        if (!auth()->check()) {
            $route = route('front.doctor.profile', ['doctor_id' =>  $this->doc->id, 'doctor_name' => str_replace(' ', '_', $this->doc->full_name)]);
            session()->put('url.intended', $route);
            return redirect()->route('front.login.user', ['favariteDr' => true]);
        }
        $privius_docs = auth()->user()->favorite_dr;

        // Retrieve the current user's favorite doctors
        $user = auth()->user();
        $privius_docs = $user->favorite_dr;
        // Ensure $privius_docs is an array
        if (!is_array($privius_docs)) {
            $privius_docs = json_decode($privius_docs, true);

            if (!is_array($privius_docs)) {
                $privius_docs = [];
            }
        }

        // Add the new doctor's ID to the array
        $privius_docs[] = $this->doc->id;

        // Save the updated array back to the user's profile
        $user->favorite_dr = $privius_docs;
        $this->fetchData['isFavarite'] = true;
    }

    public function removeFromFavarite()
    {
        // Check if the user is logged in
        if (!auth()->check()) {
            $route = route('front.doctor.profile', ['doctor_id' =>  $this->doc->id, 'doctor_name' => str_replace(' ', '_', $this->doc->full_name)]);
            session()->put('url.intended', $route);
            return redirect()->route('front.login.user', ['favariteDr' => true]);
        }
        $privius_docs = auth()->user()->favorite_dr;
        // Retrieve the current user's favorite doctors
        $user = auth()->user();
        $privius_docs = $user->favorite_dr;

        // Ensure $privius_docs is an array
        if (!is_array($privius_docs)) {
            $privius_docs = json_decode($privius_docs, true);

            if (!is_array($privius_docs)) {
                $privius_docs = [];
            }
        }

        if (($key = array_search($this->doc->id, $privius_docs)) !== false) {
            unset($privius_docs[$key]);
        }
        // Re-index the array to avoid potential issues with JSON encoding
        $privius_docs = array_values($privius_docs);
        $user->favorite_dr = json_encode($privius_docs);

        if (isset($this->fetchData['isFavarite'])) {
            unset($this->fetchData['isFavarite']);
        }
    }
    private function isDocAvaiable()
    {
        if (setting(SettingKeyEnum::APPOINTMENT_STATUS) != true) {
            return false;
        }
        // check if doctor active and has setting
        $status =  $this->doc->isDoctorActive();
        $hasSetting = AppointmentSetting::activeSetting()->where('user_id', $this->doc->id)->exists();
        if ($status && $hasSetting) {
            return true;
        }
        return false;
    }
    public function mount()
    {
        $doctor_id =   request()->route('doctor_id');
        $checkExistensOfdoctor =  User::find($doctor_id);
        if (isset($checkExistensOfdoctor) && $checkExistensOfdoctor->isDoctor()) {
            $this->doc = $checkExistensOfdoctor;
        } else {
            abort(404);
        }
        $this->fetchData['comments'] = Comment::doctroComments($this->doc->id);
        if (count($this->fetchData['comments']) > 2) {
            $this->fetchData['iteratorComments'] = 2;
        } else {
            $this->fetchData['iteratorComments'] = count($this->fetchData['comments']);
            $this->fetchData['iteratorStop'] = true;
        }
        if (auth()->check()) {
            if (isset(auth()->user()->favorite_dr) &&  in_array($this->doc->id, auth()->user()->favorite_dr)) {
                $this->fetchData['isFavarite'] = true;
            }
        }
        $place = $this->doc->Places()->where('active', ActiveEnum::ACTIVE)->first();
        if (isset($place->detail[Place::DETAIL_KEY_NUMBERS])) {
            $this->fetchData['tel'] = implode(',', $place->detail[Place::DETAIL_KEY_NUMBERS]);
        }
        if (isset($place->detail[Place::DETAIL_ADDRESS])) {
            $this->fetchData['address'] = $place->detail[Place::DETAIL_ADDRESS];
        }
        if (isset($place->detail[Place::DETAIL_KEY_LOCATION])) {
            $this->fetchData['navigate'] = "https://maps.google.com/maps?daddr=" . $place->detail[Place::DETAIL_KEY_LOCATION][Place::DETAIL_KEY_LOCATION_LAT] . ',' . $place->detail[Place::DETAIL_KEY_LOCATION][Place::DETAIL_KEY_LOCATION_LNG];
        }
        $this->fetchData['modalStep'] = 1;
        $this->fetchData['is_app_available'] = $this->isDocAvaiable();
        $this->routeHasServiceOrPlace();
        // bread crumb
        $this->fetchData['site_title'] = Setting(SettingKeyEnum::SITE_TITLE);
        $this->fetchData['gallery'] = json_decode($this->doc->dr_gallery, true);
    }
    public function render()
    {
        return view('front::livewire.doctor-profile.doctor-profile-livewire');
    }
}
