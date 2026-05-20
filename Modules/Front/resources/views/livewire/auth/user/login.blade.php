<div>
    <main class="pt-16 bg-secondary-100">
        @isset($fetchData['alert'])
            <div class="bg-cyan-300 text-gray-500 text-lg	 text-center py-3 px-5 rounded-lg mb-5 mx-auto max-w-[600px]">
                {{ $fetchData['alert'] }}
            </div>
        @endisset

        <form wire:submit='LoginAuthForm' class="bg-white rounded-lg w-full max-w-[600px] mx-auto p-5 flex flex-col gap-4"
            wire:loading.class='opacity-50'>
            @if ($step == 1)
                <div class="text-center space-y-2">
                    <p class="text-lg font-semibold">ورود به حساب</p>
                    <p class="text-secondary-400">جهت دریافت نوبت به حساب خود وارد شوید</p>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-secondary-400 font-semibold">شماره تلفن خود را وارد کنید</p>
                    </div>
                </div>
                <input type="number" wire:model='form.mobileNmber' id="phoneNumberInput"
                    class="border @error('form.mobileNmber') border-rose-500 @else border-secondary-300 @enderror  rounded-lg bg-primary-tint-100 text-center py-2"
                    placeholder="مثال: 09123456789" />
                @error('form.mobileNmber')
                    <span class="text-rose-500">
                        {{ $message }}
                    </span>
                @enderror
                <button type="submit" class="btn__blue--round-full" wire:loading.attr='disabled'
                    wire:target='LoginAuthForm'>
                    <span wire:loading.remove wire:target='LoginAuthForm'>
                        @if ($login_without_otp)
                            ورود
                        @else
                            ارسال کد تایید
                        @endif
                    </span>
                    <div role="status" wire:loading wire:target='LoginAuthForm'>
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
                </button>
            @elseif($step == 2)
                <div class="text-center space-y-2">
                    <p class="text-lg font-semibold">ورود به حساب</p>
                    <p class="text-secondary-400">جهت دریافت نوبت به حساب خود وارد شوید</p>
                </div>
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-secondary-400 font-semibold">کد ارسال شده را وارد کنید</p>
                        <div class="flex items-center gap-3 py-2 px-4 bg-secondary-100 rounded-lg text-sm">
                            <p dir="ltr">{{ $form['mobileNmber'] }}</p>
                            <button class="text-primary-main" type="button" dir="ltr" wire:click='changeNumber'>
                                <svg aria-hidden="true" wire:loading wire:target='changeNumber'
                                    class="w-5 h-5 text-white-200 animate-spin dark:text-white-600 fill-gray-700"
                                    viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                        fill="currentColor" />
                                    <path
                                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                        fill="currentFill" />
                                </svg>
                                <span wire:loading.remove wire:target='changeNumber'>ویرایش</span>
                            </button>
                        </div>
                    </div>
                </div>
                <input type="number" wire:model='form.code'id="codeInput"
                    class="border border-secondary-300 rounded-lg bg-primary-tint-100 text-center py-2"
                    placeholder="کد 4 رقمی" />
                <div class="flex justify-end text-sm">
                    <span id="countDown"></span>
                </div>
                @error('form.code')
                    <span class="text-rose-500">
                        {{ $message }}
                    </span>
                @enderror
                <div id="resendCode" style="display: none" wire:ignore.self>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 text-sm">
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            <p>کد را دریافت نکردید؟</p>
                            <button type="button" wire:click='resendotpCode'
                                class="text-primary-main font-semibold inline-flex items-center gap-1.5 disabled:opacity-60"
                                wire:loading.attr="disabled" wire:target="resendotpCode">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                    wire:loading.remove wire:target="resendotpCode">
                                    <use xlink:href="#sprite-email" />
                                </svg>
                                <svg class="w-4 h-4 animate-spin" viewBox="0 0 100 101" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true" wire:loading
                                    wire:target="resendotpCode">
                                    <path
                                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                        fill="currentColor" />
                                    <path
                                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                        fill="currentFill" />
                                </svg>
                                <span wire:loading.remove wire:target="resendotpCode">ارسال دوباره پیامک</span>
                                <span wire:loading wire:target="resendotpCode">در حال ارسال...</span>
                            </button>
                            @if ($callLoginTemplate)
                                <span class="text-secondary-400">یا</span>
                                <button type="button" wire:click='resendCallOtpCode'
                                    class="text-primary-main font-semibold inline-flex items-center gap-1.5 disabled:opacity-60"
                                    wire:loading.attr="disabled" wire:target="resendCallOtpCode">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true" wire:loading.remove
                                        wire:target="resendCallOtpCode">
                                        <path
                                            d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1C10.61 21 3 13.39 3 4c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.24.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2Z"
                                            fill="currentColor" />
                                    </svg>
                                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 100 101" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true" wire:loading
                                        wire:target="resendCallOtpCode">
                                        <path
                                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                            fill="currentColor" />
                                        <path
                                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                            fill="currentFill" />
                                    </svg>
                                    <span wire:loading.remove wire:target="resendCallOtpCode">دریافت کد با تماس</span>
                                    <span wire:loading wire:target="resendCallOtpCode">در حال تماس...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn__blue--round-full" id="submitCodeBtn" wire:loading.attr='disabled'
                    wire:target='LoginAuthForm'>
                    <span wire:loading.remove wire:target='LoginAuthForm'>تایید و ارسال</span>
                    <div role="status" wire:loading wire:target='LoginAuthForm'>
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
                </button>
            @endif
        </form>
    </main>
    @if ($step == 1)
        <div class="pt-1 pb-16 bg-secondary-100">
            <div class="rounded-lg w-full max-w-[600px] mx-auto p-5 flex flex-col gap-4">
                <a class="text-blue-700 hover:text-blue-900 flex" href="{{ route('front.login.doctor') }}">
                    <span>ورود پزشک</span>
                    <svg class="w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-chevron-left-circle"></use>
                    </svg>
                </a>
            </div>
        </div>
    @endif

</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            Livewire.on('startCountDown', function() {
                $('#resendCode').fadeOut();
                // Clear any existing interval
                if (typeof interval !== 'undefined') {
                    clearInterval(interval);
                }
                var timer = "02:00";
                interval = setInterval(function() {
                    var timeArray = timer.split(':');
                    var minutes = parseInt(timeArray[0], 10);
                    var seconds = parseInt(timeArray[1], 10);
                    seconds--;
                    if (seconds < 0) {
                        if (minutes > 0) {
                            minutes--;
                            seconds = 59;
                        } else {
                            clearInterval(interval);
                            $('#countDown').hide();
                            $('#resendCode').fadeIn();
                        }
                    }
                    seconds = (seconds < 10) ? '0' + seconds : seconds;
                    $('#countDown').text(minutes + ':' + seconds);
                    timer = minutes + ':' + seconds;
                }, 1000);
                setTimeout(() => {
                    $('#codeInput').focus();
                    $("#codeInput").on("input", function() {
                        let inputValue = $(this).val();
                        if (inputValue.length == 4) {
                            $('#submitCodeBtn').click();
                        }
                        $(this).val(inputValue);
                    });
                }, 500);
            });
            async function startWebOtp() {
                if (!('OTPCredential' in window)) {
                    return; // Browser doesn't support WebOTP
                }

                const ac = new AbortController();
                // Optional: cancel after 1 minute so it doesn’t hang forever
                setTimeout(() => ac.abort(), 60_000);

                try {
                    const content = await navigator.credentials.get({
                        otp: {
                            transport: ['sms']
                        },
                        signal: ac.signal
                    });

                    if (content && content.code) {
                        const otpInput = document.getElementById('codeInput');
                        otpInput.value = content.code;
                        @this.set('form.code', content.code);
                        @this.LoginAuthForm();
                    }
                } catch (err) {

                }
            }
            Livewire.on('waitForCode', function() {
                setTimeout(() => {
                    startWebOtp();
                }, 1000);
            });
        });
    </script>
@endpush
