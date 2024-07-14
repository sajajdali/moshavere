<section class="dashboard__main" id="personalInfoSection" wire:ignore.self>
    <form wire:submit='changePersonalInfo' class="flex flex-col gap-4">
        <div class="bg-white p-4 space-y-5 rounded-lg">
            <h3 class="font-bold">ویرایش پروفایل</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for=""
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">نام
                    </label>
                    <input type="text" wire:model='form.first_name'
                        class="w-full border   @error('form.first_name') border-rose-500 @else  border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="نام نمایشی شما" />
                    @error('form.first_name')
                        <p class="text-red text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for=""
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">نام
                        خانوادگی </label>
                    <input type="text" wire:model='form.last_name'
                        class="w-full border  @error('form.last_name') border-rose-500 @else  border-secondary-300 @enderror  rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="نام خانوادگی نمایشی شما" />
                    <p class="text-red text-[10px]"></p>
                    @error('form.last_name')
                        <p class="text-red text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for="gender"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">
                        جنسیت
                    </label>
                    <select id="gender" name="gender" wire:model='form.gender'
                        class="w-full border  @error('form.gender') border-rose-500 @else  border-secondary-300 @enderror  rounded-lg bg-primary-tint-100 py-2 px-3 focus:outline-none focus:border-primary-500">
                        <option value="">انتخاب کنید...</option>
                        <option value="male">مرد</option>
                        <option value="female">زن</option>
                    </select>
                    @error('form.gender')
                        <p class="text-red text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for=""
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">شماره
                        تلفن </label>
                    <input type="text" wire:model='form.mobile'
                        class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3" />

                </div>
                <div class="space-y-2">
                    <label for=""
                        class="after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">کد
                        ملی </label>
                    <input type="text" wire:model='form.national_code'
                        class="w-full border @error('form.national_code') border-rose-500 @else  border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="کد ملی شما" />
                    @error('form.national_code')
                        <p class="text-red text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for=""
                        class=" after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">ایمیل
                    </label>
                    <input type="text" wire:model='form.email'
                        class="w-full border  @error('form.email') border-rose-500 @else  border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="email@info.com" />
                    @error('form.email')
                        <p class="text-red text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        <button type="submit" class="btn__blue--round-full">ذخیره تغییرات</button>
    </form>
</section>
