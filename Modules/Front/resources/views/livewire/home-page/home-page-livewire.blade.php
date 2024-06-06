<div>
    @include('front::layouts.components.app-header')

    @include('front::layouts.components.app-sidebar')

    {{-- اسلایدر اصلی --}}
    @include('front::components.homepage.mainslider')

    {{-- پربازدید ترین تخصص ها --}}
    @include('front::components.homepage.mostviewedservice')

    {{-- ویزیت فوری --}}
    @include('front::components.homepage.emergencyvisit')
</div>
