<?php

namespace Modules\Front\Livewire\DoctorProfile;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Artesaos\SEOTools\Facades\JsonLd;
use Modules\Front\app\Models\Comment;
use Artesaos\SEOTools\Facades\SEOTools;
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
    public string $appointmentType = 'IN_PERSON';
    public function reserveAppointment($type = 'IN_PERSON')
    {
        $this->appointmentType = $type;
        $place   =  data_get($this->form, 'place', null);
        $service =  data_get($this->form, 'service', null);
        $activePlaceCount = $this->doc->activePlaces()->count() ;
        // check if doctor was not banned
        if ($this->isDocAvailable()) {
            // check for palce count
            if ($activePlaceCount > 1) {
                // check if place has selected in route
                if ($place) {
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
            } elseif ($activePlaceCount <= 1) {
                // if ONE place exist
                $place = $this->doc->activePlaces()->first();
                $this->form['place_name'] = $place->title;
                $this->form['place'] = $place->id;
                // check for service count
                if ($this->doc->activeServices()->count() <= 1) {
                    $this->form['service'] = $this->doc->activeServices()->first()->id;
                    $this->checkForOperator();
                    return;
                }
                $this->fetchData['services'] = $this->doc->activeServices();
                $this->fetchData['modalStep'] = 2;
                return $this->lunchModal();
            }
            // if less than ONE service exist , redirect to appointment days list
            return  $this->checkForOperator();
        }
    }
    public function reserveOnlineAppointment() {}
    public function lunchModal()
    {
        return $this->dispatch('lucnhModal', true);
    }
    private function redirectToAppointmentDays($doctor_id, $place_id, $service_id, $segment = null, $operator = null)
    {
        $param = [
            'doctor_id'     => $doctor_id,
            'place_id'      => $place_id,
            'service_id'    => $service_id
        ];
        if (!empty($segment)) {
            $param['segment'] = $segment;
        }
        if (! is_null($operator)) {
            $param['operator'] = $operator;
        }
        if ($this->appointmentType == 'IN_PERSON') {
            $route = 'front.setAppointment.days';
        } else {
            $route = 'front.setAppointment.online.description';
        }
        return redirect()->route(
            $route,
            $param
        );
    }
    protected function lvlOneModal($place, $service)
    {
        if (isset($place)) {
            $this->form['place_name']    = Place::find($place)?->title ?? '';
            $this->fetchData['services'] = $this->doc->activeServices();
            if (! is_null($service)) {
                // user selected service on privous page
                $this->serviceHasSelected();
            } else {
                $place = $this->doc->activePlaces()->first()->id;
                if ($this->doc->activeServices()->count() <= 1) {
                    $this->checkForOperator();
                }
            }
            $this->fetchData['modalStep']++;
        } else {
            $this->fetchData['modalStep'] =  1;
        }
    }
    protected function lvlTwoModal($place, $service)
    {
        $segmentIds = data_get($this->form, 'segment', null);
        if (! is_null($segmentIds)) {
            if (count($segmentIds) > 1) {
                foreach ($segmentIds as $segmentId => $status) {
                    if ($status) {
                        $this->form['selectedSegmentForRoute'][] = $segmentId;
                    }
                }
            } else {
                $this->form['selectedSegmentForRoute'] = $segmentIds;
            }
            $this->checkForOperator();
        } else {
            if (! is_null($service)) {
                $this->checkForOperator();
            } else {
                $this->dispatch('swalError', msg: 'لطفا بخش مورد نظر خود را انتخاب کنید!');
            }
        }
    }
    protected function lvlThreeModal()
    {
        $segments =  data_get($this->form, 'selectedSegmentForRoute');
        $operator =  data_get($this->form, 'operator');
        $this->redirectToAppointmentDays(
            $this->doc->id,
            $this->form['place'],
            $this->form['service'],
            $segments,
            $operator
        );
    }
    public function modalSubmit()
    {
        $step    = data_get($this->fetchData, 'modalStep', 1);
        $place   = data_get($this->form, 'place', null);
        $service = data_get($this->form, 'service', null);
        return match ($step) {
            1 => $this->lvlOneModal($place, $service),
            2 => $this->lvlTwoModal($place, $service),
            3 => $this->lvlThreeModal(),
        };
    }

    public function checkForOperator()
    {
        $app_setting = AppointmentSetting::findSettingId($this->doc->id, $this->form['service'], $this->form['place']);
        $operator = null;
        if (AppointmentSetting::doseSettingHasOperator($app_setting)) {
            $operator = $this->fetchData['operators'] = AppointmentSetting::findOperators($app_setting);
            if (count($this->fetchData['operators']) > 1) {
                $this->fetchData['modalStep'] = 3;
                $this->lunchModal();
                return;
            }
            $operator = $operator->first();
        }
        $segments =  data_get($this->form, 'selectedSegmentForRoute');
        $this->redirectToAppointmentDays(
            $this->doc->id,
            $this->form['place'],
            $this->form['service'],
            $segments,
            $operator
        );
    }
    public function serviceHasSelected()
    {
        $app_setting = AppointmentSetting::findSettingId($this->doc->id, $this->form['service'], $this->form['place']);
        if (isset($this->form['service'])) {
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
                $this->checkForOperator();
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
                $this->form['place_id'] = $place->id;
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
    private function isDocAvailable()
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
    public function editservice()
    {
        unset($this->fetchData['segments']);
        unset($this->form['service']);
        $this->fetchData['modalStep'] = 2;
    }
    public function ignoreSelected()
    {
        unset($this->fetchData['segments']);
        unset($this->form['service']);
        $this->fetchData['modalStep'] = 1;
    }
    private function fetchData()
    {
        $this->fetchData['comments'] = Comment::doctroComments($this->doc->id);
        $averageRating = $this->fetchData['comments']->avg('star');
        $minRating     = $this->fetchData['comments']->min('star');
        $maxRating     = $this->fetchData['comments']->max('star');
        JsonLd::addValue('aggregateRating', [
            "@type" => "AggregateRating",
            "ratingValue" => $averageRating,
            "bestRating"  => $maxRating,
            "worstRating" => $minRating,
            "ratingCount" => 5
        ]);

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
        $this->fetchData['is_app_available'] = $this->isDocAvailable();
        $this->routeHasServiceOrPlace();
        // bread crumb

        $this->fetchData['site_title'] = Setting(SettingKeyEnum::SITE_TITLE);
        $this->fetchData['gallery'] = json_decode($this->doc->dr_gallery, true);
    }
    public function mount()
    {
        $doctor_id =   request()->route('doctor_id');
        $checkExistensOfdoctor =  User::findOrFail($doctor_id);
        if ($checkExistensOfdoctor->isDoctor()) {
            $this->doc = $checkExistensOfdoctor;
            SEOTools::setTitle($this->doc->fullName);
            SEOTools::setDescription($this->doc->dr_biography);
        } else {
            abort(404);
        }
        $this->fetchData();
    }
    public function render()
    {
        return view('front::livewire.doctor-profile.doctor-profile-livewire');
    }
}
