<div>
    <main class="py-16 bg-secondary-100">
        @isset($fetchData['alert'])
        <div class="bg-cyan-300 text-gray-500 text-lg	 text-center py-3 px-5 rounded-lg mb-5 mx-auto max-w-[600px]">
           {{ $fetchData['alert'] }}
        </div>
        @endisset
        <form wire:submit='createDocotr' class="bg-white rounded-lg w-full max-w-[600px] mx-auto p-5 flex flex-col gap-4"
            wire:loading.class='opacity-50'>
            <p class="text-lg text-center font-semibold">ایجاد حساب برای پزشک</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="drFirst_name"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">نام
                    </label>
                    <input type="text" id="drFirst_name" wire:model='form.first_name'
                        class="w-full border @error('form.first_name') border-rose-500 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="به فارسی" />
                    @error('form.first_name')
                        <span class="text-rose-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for="drLast_name"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">نام
                        خانوادگی </label>
                    <input type="text" id="drLast_name" wire:model='form.last_name'
                        class="w-full border @error('form.last_name') border-rose-500 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="به فارسی" />
                    @error('form.last_name')
                        <span class="text-rose-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="drPassword"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">
                        رمز عبور</label>
                    <input type="password" id="drPassword" wire:model='form.password'
                        class="w-full border @error('form.last_name') border-rose-500 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="حداقل 4 کاراکتر" />
                    @error('form.password')
                        <span class="text-rose-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for="drPasswordConfirm"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">تکرار
                        رمز عبور</label>
                    <input type="password" id="drPasswordConfirm" wire:model='form.passwordConfirm'
                        class="w-full border @error('form.passwordConfirm') border-rose-500 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="تکرار کلمه ی عبور" />
                    @error('form.passwordConfirm')
                        <span class="text-rose-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="licenceNumber"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">شماره
                        نظام پزشکی </label>
                    <input type="text" id="licenceNumber" wire:model='form.licenceNumber'
                        class="w-full border  @error('form.licenceNumber') border-rose-500 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="مثال 123456" />
                    @error('form.licenceNumber')
                        <span class="text-rose-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for="mobile"
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">شماره
                        تلفن</label>
                    <input type="number" id="mobile" wire:model='form.mobile'
                        class="w-full border @error('form.licenceNumber') border-rose-500 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 py-2 px-3"
                        placeholder="*********09" />
                    @error('form.mobile')
                        <span class="text-rose-500">{{ $message }}</span>
                    @enderror
                </div>

            </div>
            <div class="grid grid-cols-1 gap-4">
                <div class="space-y-2">
                    <label for="description"
                        class=" after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">
                        توضیحات </label>
                    <textarea id="description" rows="3" maxlength="500" placeholder="شامل بیوگرافی،تخصص،و..."
                        wire:model='form.description' class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3" /> </textarea>
                    @error('form.description')
                        <span class="text-rose-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            @error('authError')
                <div class="grid grid-cols-1 gap-4">
                    <div class="space-y-2">
                        <span class="text-rose-500">{{ $message }}</span>
                    </div>
                </div>
            @enderror
            <button type="submit" class="btn__blue--round-full">
                ایجاد حساب
            </button>
        </form>
    </main>
</div>
