<div>
    <main class="py-16 bg-secondary-100">
        @isset($fetchData['alert'])
            <div
                class="bg-rose-400 border border-2 border-rose-400  text-white text-lg	 text-center py-3 px-5 rounded-lg mb-5 mx-auto max-w-[600px]">
                {{ $fetchData['alert'] }}
            </div>
        @endisset
        <form wire:submit='DocLoginForm'
              class="bg-white rounded-lg w-full max-w-[600px] mx-auto p-5 flex flex-col gap-4"
              wire:loading.class='opacity-50'>
            <div class="text-center space-y-2">
                <p class="text-lg font-semibold">ورود
                    @unless(disableUi())
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTOR_REGISTRATION))
                            / ثبت نام پزشک
                        @endif
                    @endunless
                </p>
                <p class="text-secondary-400">شماره تلفن و رمز عبور خود را وارد کنید
                    @if(!disableUi())
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTOR_REGISTRATION))
                        و در صورت نداشتن حساب ، روی گزینه ثبت نام کلیک کنید.
                        @endif
                    @endif
                </p>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="docUserName" class="text-secondary-400 font-semibold">شماره تلفن </label>
                    @unless(disableUi())
                        @if(setting(\Modules\Setting\Enum\SettingKeyEnum::ENABLE_DOCTOR_REGISTRATION))
                            <a href="{{route('front.registration.doctor')}}"
                               class="flex items-center gap-3 py-2 px-4 bg-secondary-100 rounded-lg border border-rose-200 hover:bg-secondary-200 hover:text-sky-800">
                                <p class="text-sky-600">ثبت نام پزشک</p>
                            </a>
                        @endif
                    @endunless
                </div>
            </div>
            <input type="text" id="docUserName"
                   class="border @error('form.mobile') border-rose-300 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 text-center py-2"
                   wire:model='form.mobile' placeholder="مثال: 09123456789"/>
            @error('form.mobile')
            <span class="text-rose-500">{{ $message }}</span>
            @enderror
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label for="docPass" class="text-secondary-400 font-semibold">رمز عبور</label>
                </div>
            </div>
            <input type="password" id="docPass"
                   class="border @error('form.password') border-rose-300 @else border-secondary-300 @enderror rounded-lg bg-primary-tint-100 text-center py-2"
                   wire:model='form.password'/>
            @error('form.password')
            <span class="text-rose-500">{{ $message }}</span>
            @enderror
            @error('authError')
            <span class="text-rose-500">{{ $message }}</span>
            @enderror
            <button type="submit" class="btn__blue--round-full mt-6">
                <div role="status" wire:loading wire:target='DocLoginForm'>
                    <svg aria-hidden="true"
                         class="w-5 h-5 text-white-200 animate-spin dark:text-white-600 fill-gray-700"
                         viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor"/>
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill"/>
                    </svg>
                    <span class="sr-only">Loading...</span>
                </div>
                <span wire:loading.remove wire:target='DocLoginForm'>ورود</span>
            </button>
        </form>
    </main>

</div>
