<div>
    @include('front::layouts.components.app-sidebar')

    {{-- اسلایدر اصلی --}}
    @include('front::components.homepage.mainslider')

    {{-- پربازدید ترین بخش ها --}}
    @include('front::components.homepage.mostviewedservice')

    {{-- لیست اول پزشکان --}}
    @include('front::components.homepage.emergencyvisit')

    {{-- لیست دوم پزشکان --}}
    @include('front::components.homepage.docintroducttion')

    {{-- نظر مخاطبین --}}
    @if(isset($fetchData['comments']) && $fetchData['comments']->isNotEmpty())
        @include('front::components.homepage.comments')
    @endif
    {{-- آخرین مطالب --}}
    @include('front::components.homepage.blogs')

    {{-- جدیدترین پزشکان --}}
    @include('front::components.homepage.newestdoc')

    {{-- جست و جو بر اساس شهر --}}
    @include('front::components.homepage.searchbyprovince')

    {{-- سوالات متداول --}}
    @include('front::components.homepage.faq')
</div>
