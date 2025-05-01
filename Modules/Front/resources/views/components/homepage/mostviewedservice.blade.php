@if(setting(\Modules\Setting\Enum\SettingKeyEnum::MOST_VIEWED_SECTIONS_ICONS_VIEW))
    <div>
        <!-- top view skills -->
        <section class="bg-gray-100 py-12">
            <div class="container space-y-6">
                <div class="flex flex-row items-center justify-between gap-3">
                    <p class="font-semibold">پربازدیدترین بخش ها</p>
                    <a href="{{ route('front.searchPage', ['query' => 'بخش ها']) }}" class="flex items-center gap-4 text-primary-main">
                        <span class="font-semibold">مشاهده همه</span>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="#sprite-arrow-left" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach ($fetchData['service'] as $service)
                        <a href="{{ $service->getServiceRoute() }}"
                           class="flex flex-col items-center gap-4 p-5 border border-secondary-200 bg-white rounded-lg h-[230px]">
                            <img src="{{ assetStorage($service->icon) }}" class="h-[66px]" />
                            <p class="font-semibold">{{ $service->title }}</p>
                            <p class="text-sm text-secondary-400">+{{ $service->user->count() }} پزشک</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

@else
<div>
   <!-- top view skills -->
   <section class="bg-gray-100 py-12">
    <div class="container space-y-6">
       <div class="flex flex-row items-center justify-between gap-3">
          <p class="font-semibold">پربازدیدترین بخش ها</p>
          <a  href="{{route('front.searchPage',['query' => 'بخش ها'])}}" class="flex items-center gap-4 text-primary-main">
             <span class="font-semibold">مشاهده همه</span>
             <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="#sprite-arrow-left" />
             </svg>
          </a>
       </div>
       <div class="swiper swiper-cards-6">
          <div class="swiper-wrapper">
            @foreach ($fetchData['service'] as $service)
             <div class="swiper-slide">
                <a href="{{$service->getServiceRoute()}}"
                   class="flex flex-col items-center gap-4 p-5 border border-secondary-200 bg-white rounded-lg  h-[230px]">
                   <img src="{{assetStorage($service->icon)}}" class="h-[66px]" />
                   <p class="font-semibold">{{$service->title}}</p>
                   <p class="text-sm text-secondary-400">+{{$service->user->count()}} پزشک</p>
                </a>
             </div>
             @endforeach
          </div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
       </div>
    </div>
 </section>
</div>
@endif
