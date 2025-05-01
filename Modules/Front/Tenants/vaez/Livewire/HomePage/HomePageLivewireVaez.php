<?php

namespace Modules\Front\Tenants\vaez\Livewire\HomePage;

use File;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\Front\Livewire\HomePage\HomePageLivewire;
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
use View;

#[Layout('front::layouts.app')]
class HomePageLivewireVaez extends HomePageLivewire
{

    public function render()
    {
        $tenantId = tenant('id');
        $tenantViewPath = module_path('Front', 'Tenants/'.$tenantId.'/resources/views/livewire/home-page');

        if (File::exists($tenantViewPath)) {
            view()->addLocation($tenantViewPath);
        }

        return view('home-page-livewire');
    }

}
