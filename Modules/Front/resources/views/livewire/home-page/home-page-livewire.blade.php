<div>
    @include('front::layouts.components.app-sidebar')

    {{-- اسلایدر اصلی --}}
    @include('front::components.homepage.mainslider')

    {{-- پربازدید ترین بخش ها --}}
    @include('front::components.homepage.mostviewedservice')

    {{-- ویزیت فوری --}}
    @include('front::components.homepage.emergencyvisit')

    {{-- معرفی پزشکان --}}
    @include('front::components.homepage.docintroducttion')

    {{-- نظر مخاطبین --}}
    @include('front::components.homepage.comments')

    {{-- آخرین مطالب --}}
    @include('front::components.homepage.blogs')

    {{-- جدیدترین پزشکان--}}
    @include('front::components.homepage.newestdoc')

    {{-- جست و جو بر اساس شهر--}}
    @include('front::components.homepage.searchbyprovince')

    {{-- سوالات متداول--}}
    @include('front::components.homepage.faq')
</div>
