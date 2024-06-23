<div>
    <main class="bg-secondary-100 py-10 md:py-16">
        <section class="dashboard__container">
          <aside class="dashboard__side">
            <ul>
              <li class="dashboard__side-item active">
                <a href="#">
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="../../assets/svg/icon.svg#sprite-user" />
                  </svg>
                  <p>اطلاعات شخصی</p>
                </a>
              </li>
              <li class="dashboard__side-item">
                <a href="#">
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="../../assets/svg/icon.svg#sprite-lock" />
                  </svg>
                  <p>رمز عبور</p>
                </a>
              </li>
              <li class="dashboard__side-item">
                <a href="#">
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="../../assets/svg/icon.svg#sprite-date" />
                  </svg>
                  <p>نوبت‌های من</p>
                </a>
              </li>
              <li class="dashboard__side-item">
                <a href="#">
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="../../assets/svg/icon.svg#sprite-comment" />
                  </svg>
                  <p>نظرات من</p>
                </a>
              </li>
              <li class="dashboard__side-item">
                <a href="#">
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="../../assets/svg/icon.svg#sprite-save" />
                  </svg>
                  <p>لیست پزشکان محبوب</p>
                </a>
              </li>
              <li>
                <hr class="border-secondary-200" />
              </li>
              <li class="dashboard__side-item dashboard__side-item--danger">
                <a href="#">
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="../../assets/svg/icon.svg#sprite-logout" />
                  </svg>
                  <p>خروج از حساب</p>
                </a>
              </li>
            </ul>
          </aside>
          <section class="dashboard__main">
            <form action="" class="flex flex-col gap-4">
              <div class="bg-white p-4 space-y-5 rounded-lg">
                <h3 class="font-bold">ویرایش پروفایل</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label for=""
                      class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">نام
                    </label>
                    <input type="text" id="" class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                      placeholder="نام نمایشی شما" />
                    <p class="text-red text-[10px]">فیلد نباید خالی باشد</p>
                  </div>
                  <div class="space-y-2">
                    <label for=""
                      class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">نام
                      خانوادگی </label>
                    <input type="text" id="" class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                      placeholder="نام خانوادگی نمایشی شما" />
                    <p class="text-red text-[10px]"></p>
                  </div>
                  <div class="space-y-2">
                    <label for=""
                      class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">کد
                      ملی </label>
                    <input type="text" id="" class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                      placeholder="به فارسی" />
                    <p class="text-red text-[10px]"></p>
                  </div>
                  <div class="space-y-2">
                    <label for=""
                      class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">شماره
                      تلفن </label>
                    <input type="text" id="" class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                      placeholder="09123456789" />
                    <p class="text-red text-[10px]"></p>
                  </div>
                  <div class="space-y-2">
                    <label for=""
                      class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">جنسیت
                    </label>
                    <input type="text" id="" class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                      placeholder="انتخاب کنید" />
                  </div>
                  <div class="space-y-2">
                    <label for=""
                      class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">ایمیل
                    </label>
                    <input type="text" id="" class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                      placeholder="بدون www" />
                    <p class="text-red text-[10px]"></p>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn__blue--round-full">ذخیره تغییرات</button>
            </form>
          </section>
        </section>
      </main>
</div>
