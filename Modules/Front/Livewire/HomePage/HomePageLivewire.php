<?php

namespace Modules\Front\Livewire\HomePage;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Front\app\Models\Faq;
use Modules\User\Enum\UserMetaEnum;
use Illuminate\Support\Facades\Cache;
use Modules\Front\app\Models\Comment;
use Modules\Front\app\Models\Province;
use Modules\Service\app\Models\Service;
use Modules\Front\enum\CommentStatusEnum;
use Modules\Front\enum\CommentShowHomePage;
use Modules\Setting\Enum\SettingKeyEnum;
use Artesaos\SEOTools\Facades\SEOTools;

#[Layout('front::layouts.app')]
class HomePageLivewire extends Component
{

    #[Locked]
    public array $fetchData = [];
    public array $form = [];

    public function searchFor()
    {
        if (isset($this->form['searchProp'])) {
            $sanitizedInput = htmlspecialchars($this->form['searchProp'], ENT_QUOTES, 'UTF-8');
            return $this->redirect(route('front.searchPage', ['query' => $sanitizedInput]), true);
        }
    }
    private function getIntrudoceDocList()
    {
        //    Define a unique cache key
        $cacheKey = 'Introduction_doctors';
        if (app()->environment('local')) {
            return   User::introductionDoctors()->get()->filter(function ($doc) {
                if ($doc->services()->exists() && $doc->places()->exists() && $doc->appointmentSettings()->exists()) {
                    return true;
                } else {
                    return false;
                };
            })->sortBy(function ($model) {
                return $model->dr_info_order;
            });
        } else {
            return Cache::rememberForever($cacheKey,  function () {
                return   User::introductionDoctors()->get()->filter(function ($doc) {
                    if ($doc->services()->exists() && $doc->places()->exists() && $doc->appointmentSettings()->exists()) {
                        return true;
                    } else {
                        return false;
                    };
                })->sortBy(function ($model) {
                    return $model->dr_info_order;
                });
            });
        }
    }
    private function emergencyDoctors()
    {

        // Define a unique cache key
        $cacheKey = 'emergency_doctors';
        // Attempt to get the data from the cache
        if (app()->environment('local')) {
            return User::emergencyDoctors()->get()->filter(function ($doc) {
                if ($doc->services()->exists() && $doc->places()->exists() && $doc->appointmentSettings()->exists()) {
                    return true;
                } else {
                    return false;
                };
            })->sortBy(function ($model) {
                return $model->dr_emergencyvisit_order;
            });
        } else {
            return  Cache::rememberForever($cacheKey, function () {
                return User::emergencyDoctors()->get()->filter(function ($doc) {
                    if ($doc->services()->exists() && $doc->places()->exists() && $doc->appointmentSettings()->exists()) {
                        return true;
                    } else {
                        return false;
                    };
                })->sortBy(function ($model) {
                    return $model->dr_emergencyvisit_order;
                });
            });
        }
    }
    private function getNewestDoc()
    {
        return User::newestDocs()->orderByDesc('created_at')->get()->filter(function ($doc) {
            if ($doc->services()->exists() && $doc->places()->exists() && $doc->appointmentSettings()->exists()) {
                return true;
            } else {
                return false;
            };
        })->take(4);
    }
    public function searchWithProvonce()
    {
        $this->validate(['form.province' => 'required|integer']);
        return redirect()->route('front.searchPage', ['province' => $this->form['province']]);
    }

    public function docpage(User $user)
    {
        if (empty($user)) {
            return redirect()->to('/404');
        }
        if ($user->exists() || $user->isDoctor()) {
            return redirect()->route('front.doctor.profile', ['doctor_id' => $user->id, 'doctor_name' => str_replace(' ', '_', $user->full_name)]);
        }
        return redirect()->to('/404');
    }
    public function mount()
    {
        SEOTools::setTitle(setting(SettingKeyEnum::SITE_TITLE));
        // when disable ui template
        if (disableUi()) {
            return redirect()->route('front.login.doctor');
        }

        if (filter_var(setting(SettingKeyEnum::DISABLE_ONLINE_APPOINTMENT), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $this->fetchData['service'] = Service::show()->mostViewedService();

        // Fetch doctors with dr_info_status set to true and order them by dr_info_order
        $this->fetchData['EmergencyDoctors']    = $this->emergencyDoctors();
        $this->fetchData['introductionDoctors'] = $this->getIntrudoceDocList();
        $this->fetchData['newestDocs']          = $this->getNewestDoc();
        $this->fetchData['faqs'] = Faq::all();
        if (app()->environment('local')) {
            $this->fetchData['comments'] = Comment::where('status', CommentStatusEnum::ACCEPTED)
                ->where('show_in_homePage', CommentShowHomePage::SHOW)->get()->take(4);
        } else {
            $this->fetchData['comments'] = Cache::rememberForever('homepageComments', function () {
                Comment::where('status', CommentStatusEnum::ACCEPTED)->where('show_in_homePage', CommentShowHomePage::SHOW)->get()->take(4);
            });
        }
        $this->fetchData['province'] =
            Province::all();
    }
    public function render()
    {
        if (filter_var(setting(SettingKeyEnum::DISABLE_ONLINE_APPOINTMENT), FILTER_VALIDATE_BOOL)) {
            return view('front::livewire.home-page.voip-home-page-livewire');
        }

        return view('front::livewire.home-page.home-page-livewire');
    }
}
