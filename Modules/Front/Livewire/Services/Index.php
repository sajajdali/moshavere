<?php

namespace Modules\Front\Livewire\Services;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\Front\app\Models\Faq;
use Modules\Front\Traits\SearchPage;
use Illuminate\Support\Facades\Cache;
use Modules\Front\app\Models\Comment;
use Modules\Front\app\Models\Province;
use Artesaos\SEOTools\Facades\SEOTools;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Front\enum\CommentStatusEnum;
use Modules\Front\Enum\CommentShowHomePage;
#[Layout('front::layouts.app')]
class Index extends Component
{
    use SearchPage;
    #[Locked]
    public array $fetchData = [];
    #[Locked]
    public array $filter = [];
    public array $form = [];

    #[Url]
    public $query;

    public function messages()
    {
        return [
            'query.required' => 'متن جست و جو را وارد کنید!',
            'query.string' => 'فرمت وارد شده صحیح نیست!',
            'query.max' => 'متن وارد شده از حداکثر کاراکتر مجاز بیشتر است!',
        ];
    }
    public function render()
    {
        return view('front::livewire.services.index');
    }

    public function mount() {
        $serviceName = request()->route('service_id');
        $service  = Service :: find($serviceName);
        $this->fetchData['service_id'] = $service->id;
        $this->fetchData['service_name'] = $service->title;
        SEOTools::setTitle($service->title);
        $description ="بخش $service->title | نوبت‌دهی آنلاین از برترین پزشکان $service->title ، سریع و آسان
در بخش $service->title ، بهترین متخصصان $service->title را پیدا کنید و نوبت خود را به‌صورت اینترنتی، سریع و بدون معطلی رزرو کنید. با سیستم نوبت‌دهی آنلاین، می‌توانید به‌راحتی پزشک موردنظر خود را انتخاب کرده و در کوتاه‌ترین زمان، وقت معاینه بگیرید. تجربه‌ای آسان، مطمئن و بدون دغدغه در دریافت خدمات پزشکی ارتوپدی!";
        SEOTools::setDescription($description);
        // when disable ui template
        if (disableUi()) {
            return redirect()->route('front.login.doctor');
        }
        $this->query = request()->get('query');
        if (request()->has('service_id')) {
            $this->fetchData['service_id'] =  htmlspecialchars(request()->input('service_id'), ENT_QUOTES, 'UTF-8');
            $this->fetchData['settApp']['service'] =  $this->fetchData['service_id'];
        }
        if (request()->has('province')) {
            $this->fetchData['province_id'] =  htmlspecialchars(request()->input('province'), ENT_QUOTES, 'UTF-8');
        }
        $this->fillTheFilters();
        $this->searchIn();
    }
}
