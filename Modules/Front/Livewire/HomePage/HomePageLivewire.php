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
use Modules\Front\Enum\CommentShowHomePage;

#[Layout('front::layouts.app')]
#[Title('صفحه اصلی')]
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
        //    Attempt to get the data from the cache
        return Cache::rememberForever($cacheKey,  function () {
            return   User::introductionDoctors()->get()->filter(function ($doc) {
                if ($doc->services()->exists() && $doc->places()->exists()) {
                    return true;
                };
            })->sortBy(function ($user) {
                return $user->metas->where('meta_key', UserMetaEnum::DR_INFO_ORDER)->first()->meta_value ?? 0;
            });
        });
    }
    private function emergencyDoctors()
    {

        // Define a unique cache key
        $cacheKey = 'emergency_doctors';
        // Attempt to get the data from the cache
        return  Cache::rememberForever($cacheKey, function () {
            return User::emergencyDoctors()->get()->filter(function ($doc) {
                if ($doc->services()->exists() && $doc->places()->exists()) {
                    return true;
                };
            })->sortBy(function ($user) {
                return $user->metas->where('meta_key', UserMetaEnum::DR_ENEMRGENCY_ORDER)->first()->meta_value ?? 0;
            });
        });
    }
    private function getNewestDoc()
    {
        return User::newestDocs()->orderByDesc('created_at')->get()->filter(function ($doc) {
            if ($doc->services()->exists() && $doc->places()->exists()) {
                return true;
            };
        })->take(4);
    }
    public function searchWithProvonce()
    {
        $this->validate(['form.province' => 'required|integer']);
        return redirect()->route('front.searchPage', ['province' => $this->form['province']]);
    }

    public function docpage($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            return redirect()->to('/404');
        }
        if ($user->exists() || $user->isDoctor()) {
            $docService = $user->activeServices();
            $docPlace = $user->activePlaces();
            if ($docService->count() <= 1 && $docPlace->count() <= 1) {
                return redirect()->route(
                    'front.setAppointment.days',
                    [
                        'doctor_id' => $user->id,
                        'place_id' => $docPlace->first()->id,
                        'service_id' => $docService->first()->id,
                    ]
                );
            }
            return redirect()->route('front.doctor.profile', ['doctor_id' => $id]);
        }
        return redirect()->to('/404');
    }
    public function mount()
    {
        $this->fetchData['service'] = Service::mostViewedService();

        // Fetch doctors with dr_info_status set to true and order them by dr_info_order
        $this->fetchData['EmergencyDoctors']    = $this->emergencyDoctors();
        $this->fetchData['introductionDoctors'] = $this->getIntrudoceDocList();
        $this->fetchData['newestDocs']          = $this->getNewestDoc();
        $this->fetchData['faqs'] = Faq::all();
        $this->fetchData['comments'] = Cache::rememberForever('homepageComments', function () {
            return Comment::where('status', CommentStatusEnum::ACCEPTED)->where('show_in_homePage', CommentShowHomePage::SHOW)->get()->take(4);
        });
        $this->fetchData['province'] =  cache::rememberForever('front.provinces', function () {
            return  Province::all();
        });
    }
    public function render()
    {
        return view('front::livewire.home-page.home-page-livewire');
    }
}
