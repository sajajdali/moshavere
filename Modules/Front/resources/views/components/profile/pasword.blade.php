<section class="dashboard__main" id="passwordSection" wire:ignore.self>
    <form action="" class="flex flex-col gap-4">
        <div class="bg-white p-4 space-y-5 rounded-lg">
            <h3 class="font-bold">رمز ورود به حساب</h3>
            <div class="space-y-2">
                <label for="currentPassword"
                    class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">رمز
                    ورود قبلی
                </label>
                <input type="password" id="currentPassword"
                    class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                    placeholder="تایپ کنید" />
                <p class="text-red text-[10px]">فیلد نباید خالی باشد</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="newPassword"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">رمز
                        ورود جدید
                    </label>
                    <input type="text" id="newPassword"
                        class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="تایپ کنید" />
                    <p class="text-red text-[10px]">فیلد نباید خالی باشد</p>
                </div>
                <div class="space-y-2">
                    <label for="confirmNewPassword"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">نام
                        خانوادگی </label>
                    <input type="text" id="confirmNewPassword"
                        class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="نام خانوادگی نمایشی شما" />
                    <p class="text-red text-[10px]"></p>
                </div>
            </div>
            <button type="submit" class="btn__blue--round-full">تغییر رمز ورود</button>
        </div>
    </form>
</section>
