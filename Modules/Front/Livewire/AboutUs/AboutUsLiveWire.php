<?php

namespace Modules\Front\Livewire\AboutUs;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Modules\User\Entities\User;
use Modules\Front\app\Models\Faq;
use Illuminate\Support\Facades\Cache;
use Modules\Front\app\Models\Comment;
use Modules\Service\app\Models\Service;
use Modules\Front\enum\CommentStatusEnum;
use Modules\Front\Enum\CommentShowHomePage;

#[Layout('front::layouts.app')]
#[Title('درباره ما')]
class AboutUsLiveWire extends Component
{

    public array $fetchData = [];

    public function mount()
    {
        if (config('app.without_cache')) {
            $this->fetchData['comments'] = Comment::where('status', CommentStatusEnum::ACCEPTED)->where('show_in_homePage', CommentShowHomePage::SHOW)->get()->take(4);
        } else {
            $this->fetchData['comments'] = Cache::rememberForever('homepageComments', function () {
                return Comment::where('status', CommentStatusEnum::ACCEPTED)->where('show_in_homePage', CommentShowHomePage::SHOW)->get()->take(4);
            });
        }
        // Define a unique cache key
        $cacheKey = 'emergency_doctors';
        // Attempt to get the data from the cache
        $this->fetchData['EmergencyDoctors'] =   Cache::rememberForever($cacheKey, function () {
            return User::emergencyDoctors()->get()->filter(function ($doc) {
                if ($doc->services()->exists() && $doc->places()->exists() && $doc->appointmentSettings()->exists()) {
                    return true;
                };
            })->sortBy(function ($model) {
                return $model->dr_emergencyvisit_order;
            });
        });
        $this->fetchData['service'] = Service::mostViewedService();
        $this->fetchData['faqs'] = Faq::all();
    }
    public function render()
    {
        return view('front::livewire.about-us.about-us-live-wire');
    }
}
