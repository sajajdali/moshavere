<section class="appointment__modal max-h-min" wire:ignore.self>
    <header class="appointment__modal-header">
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
                <p class="font-semibold mb-3">لطفا مطب مورد نظر خود را امتخاب کنید</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @isset($fetchData['places'])
                        @foreach ($fetchData['places'] as $key => $value)
                            <label for="part-{{ $key }}" class="cart__radio--container">
                                <input type="radio" id="part-{{ $key }}" wire:model='form.place' name="part" />
                                <div class="cart__radio--text">
                                    <h5>{{ $value->title }}</h5>
                                </div>
                            </label>
                        @endforeach
                    @endisset

                </div>
            </div>
        @elseif($fetchData['modalStep'] == 2)
            <div class="space-y-3 ">
                <p class="font-semibold">لطفا بخش مورد نظر خود را امتخاب کنید</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @isset($fetchData['services'])
                        @foreach ($fetchData['services'] as $key => $value)
                            <label for="part1" class="cart__radio--container">
                                <input type="radio" id="part1" name="part" />
                                <div class="cart__radio--text">
                                    <h5>{{ $value }}</h5>
                                    <p>متن توضیحی این بخش را در اینجا بنویسید</p>
                                </div>
                            </label>
                        @endforeach
                    @endisset

                </div>
            </div>
            <div class="space-y-3">
                <p class="font-semibold">ناحیه مورد نظر را انتخاب کنید</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label for="district1" class="cart__radio--container">
                        <input type="radio" id="district1" name="district" />
                        <div class="cart__radio--text">
                            <h5>پا</h5>
                            <p>متن توضیحی این بخش را در اینجا بنویسید</p>
                        </div>
                    </label>
                    <label for="district2" class="cart__radio--container">
                        <input type="radio" id="district2" name="district" />
                        <div class="cart__radio--text">
                            <h5>دست</h5>
                            <p>متن توضیحی این بخش را در اینجا بنویسید</p>
                        </div>
                    </label>
                    <label for="district3" class="cart__radio--container">
                        <input type="radio" id="district3" name="district" />
                        <div class="cart__radio--text">
                            <h5>صورت</h5>
                            <p>متن توضیحی این بخش را در اینجا بنویسید</p>
                        </div>
                    </label>
                </div>
            </div>
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
