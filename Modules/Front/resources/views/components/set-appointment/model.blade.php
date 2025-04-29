<section class="appointment__modal max-h-min" wire:ignore.self>
    <header class="appointment__modal-header  ">
        @if ($fetchData['modalStep'] == 2)
            @isset($fetchData['segments'])
            <button type="button" wire:click='editservice'
                class="bg-white hover:bg-sky-100 hover:text-gray-700 border-2 border-blue-100 text-sky-400 flex items-center py-3 px-5 rounded-xl gap-3">
                <span>ویرایش بخش</span>
            </button>
            @else
            <button type="button" wire:click='editPlace'
                class="bg-white hover:bg-sky-100 hover:text-gray-700 border-2 border-blue-100 text-sky-400 flex items-center py-3 px-5 rounded-xl gap-3">
                <span>ویرایش مطب</span>
            </button>
            @endisset
        @elseif($fetchData['modalStep'] == 1)
            <span></span>
        @endif
        <button type="button"
            class="bg-white border-2 border-red text-red flex items-center py-3 px-5 rounded-xl gap-3 dismissmodal">
            <span>بستن</span>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="#sprite-x" />
            </svg>
        </button>
    </header>
    <main class="appointment__modal-container p-10 flex flex-column justify-between ">
        @if ($fetchData['modalStep'] == 1)
            <div class="space-y-3 ">
                <p class="font-semibold mb-3">لطفا مطب مورد نظر خود را انتخاب کنید</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @isset($fetchData['places'])
                        @foreach ($fetchData['places'] as $key => $value)
                            <label for="part-{{ $key }}" class="cart__radio--container place">
                                <input type="radio" id="part-{{ $key }}" value="{{ $value->id }}"
                                    wire:model='form.place' name="part" />
                                <div class="cart__radio--text">
                                    <h5>{{ $value->title }}</h5>
                                </div>
                            </label>
                        @endforeach
                    @endisset
                </div>
            </div>
        @elseif($fetchData['modalStep'] == 2)
            <div class="space-y-3 @isset($fetchData['segments'])  hidden @endisset">
                <div class="w-full flex justify-center">
                    <p class=" text-xl font-semibold">مطب :
                        <a wire:click='editPlace'
                            class="text-blue-400 hover:text-blue-700 cursor-pointer">{{ $form['place_name'] }}</a>
                    </p>
                </div>
                <p class="font-semibold">لطفا بخش مورد نظر خود را امتخاب کنید</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @isset($fetchData['services'])
                        @foreach ($fetchData['services'] as $key => $item)
                            <label for="part-{{ $key }}" class="cart__radio--container servicechoices">
                                <input type="radio" id="part-{{ $key }}" name="part"
                                    value="{{ $item->id }}" wire:model='form.service' />
                                <div class="cart__radio--text">
                                    <h5>{{ $item->title }}</h5>
                                </div>
                            </label>
                        @endforeach
                    @endisset

                </div>
            </div>
            @isset($fetchData['segments'])
                <div class="space-y-3">
                    <p class="font-semibold">لطفا ناحیه مورد نظر خو را انتخاب کنید!</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach ($fetchData['segments'] as $index_key => $segmentsItem)
                            <label for="district-{{ $index_key }}" class="cart__radio--container">
                                <input
                                    @if ($fetchData['multiple_choice']) type="checkbox" @else type="radio" name="district" @endif
                                    value="{{ $segmentsItem->id }}" id="district-{{ $index_key }}"
                                    wire:model='form.segment.{{ $segmentsItem->id }}' />
                                <div class="cart__radio--text">
                                    <h5>{{ $segmentsItem->title }}</h5>
                                    <p>قیمت : {{ number_format($segmentsItem->price) }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endisset

        @endif
        {{-- <div class="warning_badge">
            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="#sprite-warning" />
            </svg>
            <p>
                مراجعین محترم نوبت های ویزیت اسفند ماه پر شده است لطفا جهت دریافت
                نوبت برای فروردین ماه از۲۷اسفند ساعت ۱۲شب به بعد به سایت مراجعه کنید
            </p>
        </div> --}}
        <button type="button" wire:click='modalSubmit' class="btn__blue--round-full">مرحله بعد</button>
    </main>
</section>
