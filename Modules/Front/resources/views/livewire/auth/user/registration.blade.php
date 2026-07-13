<div>
    <main class="py-16 bg-secondary-100">
        <form wire:submit='completeUserInfo'
            class="bg-white rounded-lg w-full max-w-[600px] mx-auto p-5 flex flex-col gap-4" wire:loading.class='opacity-50'>
            <p class="text-lg text-center font-semibold">تکمیل اطلاعات شخصی</p>
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
                        <option value="" selected>انتخاب کنید...</option>
                        <option value="male">اقا</option>
                        <option value="female">خانم</option>
                    </select>
                    @error('form.gender')
                        <p class="text-red text-sm">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for=""
                        class="after:content-['*'] after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">شماره
                        تلفن </label>
                    <input type="text" value="{{ $user->mobile }}" disabled
                        class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3" />

                </div>
                <div class="space-y-2">
                    <label for=""
                        class=" @if($fetchData['is_national_code_required']) after:content-['*'] @endif after:ml-0.5 after:text-red-500 block text-sm font-medium text-slate-700">کد
                        ملی </label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="10"
                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                        wire:model='form.national_code'
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
            <button type="submit" class="btn__blue--round-full" wire:loading.atrr='disabled'>
                <div role="status" wire:loading wire:target='completeUserInfo'>
                    <svg aria-hidden="true"
                        class="w-5 h-5 text-white-200 animate-spin dark:text-white-600 fill-gray-700"
                        viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor" />
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill" />
                    </svg>
                    <span class="sr-only">Loading...</span>
                </div>
                <span wire:loading.remove wire:target='completeUserInfo'>تایید و ارسال</span>
            </button>
        </form>
    </main>
</div>
