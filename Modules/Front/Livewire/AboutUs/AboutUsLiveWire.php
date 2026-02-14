<?php

namespace Modules\Front\Livewire\AboutUs;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Front\app\Models\Comment;
use Modules\Front\app\Models\Faq;
use Modules\Front\enum\CommentShowHomePage;
use Modules\Front\enum\CommentStatusEnum;
use Modules\Service\app\Models\Service;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\User\Entities\User;

#[Layout('front::layouts.app')]
#[Title('درباره ما')]
class AboutUsLiveWire extends Component
{

    public array $fetchData = [];

    public function mount()
    {
        if (config('app.without_cache')) {
            $this->fetchData['comments'] = Comment::where('status', CommentStatusEnum::ACCEPTED)->where('show_in_homePage', CommentShowHomePage::SHOW)->get()->take(4);
            $this->fetchData['EmergencyDoctors'] = User::emergencyDoctors()->get()->filter(function ($doc) {
                if ($doc->services()->exists() && $doc->places()->exists() && $doc->appointmentSettings()->exists()) {
                    return true;
                };
            })->sortBy(function ($model) {
                return $model->dr_emergencyvisit_order;
            });
        } else {
            $this->fetchData['comments'] = Cache::rememberForever('homepageComments', function () {
                return Comment::where('status', CommentStatusEnum::ACCEPTED)->where('show_in_homePage', CommentShowHomePage::SHOW)->get()->take(4);
            });
            $cacheKey = 'emergency_doctors';
            $this->fetchData['EmergencyDoctors'] =   Cache::rememberForever($cacheKey, function () {
                return User::emergencyDoctors()->get()->filter(function ($doc) {
                    if ($doc->services()->exists() && $doc->places()->exists() && $doc->appointmentSettings()->exists()) {
                        return true;
                    };
                })->sortBy(function ($model) {
                    return $model->dr_emergencyvisit_order;
                });
            });
        }
        $this->fetchData['service'] = Service::mostViewedService();
        $this->fetchData['faqs'] = Faq::all();
        $this->fetchData['settings'] = Setting::getSettingByArray([
            SettingKeyEnum::ABOUT_US_FIRST_SECTION_TITLE,
            SettingKeyEnum::ABOUT_US_FIRST_SECTION_DESCRIPTION,
            SettingKeyEnum::ABOUT_US_SECEND_SECTION_TITLE,
            SettingKeyEnum::ABOUT_US_SECEND_SECTION_DESCRIPTION,
            SettingKeyEnum::ABOUT_US_SECEND_SECTION_IMAGE,
            SettingKeyEnum::ABOUT_US_THIRD_SECTION_TITLE,
            SettingKeyEnum::ABOUT_US_THIRD_SECTION_DESCRIPTION,
            SettingKeyEnum::ABOUT_US_THIRD_SECTION_IMAGE,
            SettingKeyEnum::ABOUT_US_FOURTH_SECTION_TITLE,
            SettingKeyEnum::ABOUT_US_FOURTH_SECTION_DESCRIPTION,
            SettingKeyEnum::ABOUT_US_FOURTH_SECTION_IMAGE,
        ]);
        $this->fetchData['settingsModel'] = new Setting();
    }
    public function render()
    {
        return view('front::livewire.about-us.about-us-live-wire');
    }
}
