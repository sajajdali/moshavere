<div>
    <div wire:loading>
        <div class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-50 z-50 ">
            <div class="animate-spin rounded-full h-32 w-32 border-t-4 border-blue-500"></div>
        </div>
    </div>
    <main class="py-16 bg-secondary-100">
        <div class="list_of_available_day_container">
            <div class="appointment__modal-right">
                <div class="bg-secondary-100 rounded-lg p-4 flex items-center gap-5 mb-4">
                    <div
                        class="w-[70px] h-[70px] overflow-hidden rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-sky">
                        <img src="{{ $fetchData['doc']->avatar }}" alt="doctor-image-name" />
                    </div>
                    <div class="w-[calc(100%-70px-1.25rem)] space-y-3">
                        <a href="{{ route('front.doctor.profile', ['doctor_id' => $fetchData['doc']->id, 'doctor_name' => str_replace(' ', '_', $fetchData['doc']->full_name)]) }}"
                            class="font-bold">دکتر {{ $fetchData['doc']->full_name }}</a>
                        <p class="bg-secondary-200 rounded-lg py-2 px-3 text-sm">
                            {{ $fetchData['doc']->DocSpecialities() }}
                        </p>
                    </div>
                </div>
                <div class="space-y-3 border border-secondary-200 rounded-lg p-4">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                          </svg>
                          <p class="w-[calc(100%-2.25rem)] leading-6 text-sm">
                              @if ($fetchData['isAppAvailable'])
                              در نظر داشته باشد که نوبت شما برای فردا میباشد ، لطفا بعد از دریافت نوبت ، سوال خود را مطرح کنید تا در سریع ترین زمان به آن پاسخ داده شود
                              @else
                              در حال حاضر تمامی نوبت های فردا تکمیل میباشد ، لطفا در روز دیگری اقدام برای دریافت نوبت نمایید
                              @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="appointment__modal-left px-3 leading-7" wire:loading.class='opacity-75'>
                <p>
                 {{ $this->fetchData['desriptions']}}
                </p>
            </div>
            <button type="button" class="btn__blue--round-full mt-4 w-60" id="nextstep_btn" wire:click='setOnlineApp'  @if (!$fetchData['isAppAvailable']) disabled @endif >
                @if ($fetchData['isAppAvailable'])
                <span>تایید و رفتن به مرحله بعد</span>
                @else
                <span>نوبت ها تکمیل میباشد!</span>
                @endif
            </button>
        </div>
    </main>
</div>
