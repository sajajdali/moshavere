<div>

    {{-- اسلایدر اصلی --}}
    @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ACTIVE_HEADER) == null ||
    setting(\Modules\Setting\Enum\SettingKeyEnum::ACTIVE_HEADER) == 'search_header')
        @include('front::components.homepage.mainslider')
    @elseif(setting(\Modules\Setting\Enum\SettingKeyEnum::ACTIVE_HEADER) == 'image_header')
    @include('front::components.homepage.headers.header1')
    @elseif(setting(\Modules\Setting\Enum\SettingKeyEnum::ACTIVE_HEADER) == 'ba_image')
    @include('front::components.homepage.headers.header2')
    @endif

    {{-- پربازدید ترین بخش ها --}}
    @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_MOST_VIEWED_SECTIONS))
        @include('front::components.homepage.mostviewedservice')
    @endif

    {{-- لیست اول پزشکان --}}
    @include('front::components.homepage.emergencyvisit')

    {{-- لیست دوم پزشکان --}}
    @include('front::components.homepage.docintroducttion')

    {{-- نظر مخاطبین --}}
    @if(isset($fetchData['comments']) && $fetchData['comments']->isNotEmpty())
        @include('front::components.homepage.comments')
    @endif

    @if (env('DISABLED_BLOGS'))
        {{-- آخرین مطالب --}}
        @include('front::components.homepage.blogs')
    @endif

    {{-- جدیدترین پزشکان --}}
    @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_LATEST_DOCTORS))
        @include('front::components.homepage.newestdoc')
    @endif

    {{-- جست و جو بر اساس شهر --}}
    @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_CITY_SEARCH))
        @include('front::components.homepage.searchbyprovince')
    @endif

    {{-- سوالات متداول --}}
    @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_CITY_SEARCH))
        @include('front::components.homepage.faq')
    @endif
</div>
