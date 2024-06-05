<div>
   <!-- header -->
   <header class="bg-primary-main pt-16 pb-8 space-y-10 md:space-y-16">
    <section class="w-full max-w-[770px] mx-auto px-5 flex flex-col items-center gap-6 md:gap-8">
       <h1 class="text-2xl md:text-3xl text-center leading-[2.5rem] font-bold text-white">{{setting(Modules\Setting\Enum\SettingKeyEnum::SITE_SLIDER_TITLE)}}</h1>
       <form class="flex items-center p-3 w-full relative bg-white rounded-3xl border-4 md:border-8 border-blue-sky">
          <input type="text" class="text-sm md:text-base flex-grow border-none outline-none pr-7 md:pr-12"
             placeholder="جستجوی پزشک، کلینیک یا تخصص..." />
          <svg class="absolute top-1/2 -translate-y-1/2 right-3 md:right-5 text-gray-700 w-5 h-5 md:w-6 md:h-6"
             xmlns="http://www.w3.org/2000/svg">
             <use xlink:href="#sprite-search" />
          </svg>
          <button type="submit" class="btn__blue">
             <svg class="w-5 h-5 md:w-6 md:h-6" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="#sprite-location" />
             </svg>
             <span class="text-sm md:text-base">تهران</span>
          </button>
       </form>
    </section>
    <section class="container">
       <div class="flex flex-col md:flex-row gap-5 items-center justify-between rounded-xl bg-white py-4 px-5">
          <div class="flex items-center gap-4">
             <div class="w-[40px] h-[40px] bg-primary-tint-100 rounded-full flex items-center justify-center">
                <svg class="text-primary-main w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                   <use xlink:href="#sprite-time" />
                </svg>
             </div>
             <div class="w-[calc(100%-40px-1rem)] md:w-auto space-y-2">
                <h2 class="text-xl font-semibold">دریافت نوبت حضوری</h2>
                <p>دریافت نوبت اینترنتی برای مراجعه حضوری به مطب پزشکان</p>
             </div>
          </div>
          <a href="#" class="flex items-center gap-3 text-primary-main">
             <span class="font-semibold">مشاهده لیست پزشکان</span>
             <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="#sprite-arrow-left" />
             </svg>
          </a>
       </div>
    </section>
 </header>
</div>
