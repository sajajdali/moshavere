<div>
    <!-- header -->
    <header class="bg-primary-main p-4">
        <h1 class="font-semibold text-center text-2xl text-white">
            تماس با ما
        </h1>
    </header>

    <main class="bg-secondary-100 py-10 md:py-16 space-y-10 md:space-y-16">
        <!-- description -->
        @if ($fetchData['first_section_show'])
            <section class="container py-7 flex flex-col gap-4">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                    <h2 class="font-semibold text-lg">
                        {{ $fetchData['first_section_title'] }}
                    </h2>
                </div>
                <p class="leading-7 text-secondary-400">
                    {{ $fetchData['first_section_body'] }}
                </p>
            </section>
        @endif
        <!-- form container -->
        <section class="container flex flex-col md:flex-row gap-8">
            @if ($fetchData['formActiveStatus'])
                <form wire:submit='sendSupportMessage'
                    class="w-full md:w-[60%] flex flex-col gap-4 bg-white rounded-lg p-4" wire:loading.class>
                    <h4 class="text-lg font-bold">به ما پیام بدهید</h4>
                    <div class="space-y-4">
                        @guest
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="emailorphone" class="block text-sm font-medium text-slate-700">ایمیل / شماره
                                        تلفن</label>
                                    <input type="text" id="emailorphone" wire:model='form.mobile'
                                        class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                                        placeholder="ایمیل یا شماره تلفن" />
                                    @error('form.mobile')
                                        <span class="text-rose-500">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label for="namefamily" class="block text-sm font-medium text-slate-700">نام و نام
                                        خانوادگی</label>
                                    <input type="text" id="namefamily" wire:model='form.full_name'
                                        class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3"
                                        placeholder="به فارسی" />
                                    @error('form.full_name')
                                        <span class="text-rose-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endguest
                        <div class="space-y-2">
                            <label for="bof" class="block text-sm font-medium text-slate-700">پیام شما</label>
                            <textarea id="bof" wire:model='form.message'
                                class="w-full border border-secondary-300 rounded-lg bg-primary-tint-100 py-2 px-3 resize-none"
                                placeholder="پیام شما چیست؟" rows="4"></textarea>
                            @error('form.message')
                                <span class="text-rose-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button class="btn__blue--round-full" type="submit">
                            <div role="status" wire:loading wire:target='sendSupportMessage'>
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
                            <span wire:loading.remove wire:target='sendSupportMessage'>ارسال پیام</span>
                        </button>
                    </div>
                </form>
            @endif
            <div class="w-full md:w-[40%] space-y-8">
                @if (isset($fetchData['address']))
                    <div class="bg-white rounded-lg p-4 space-y-4">
                        <div class="w-[64px] h-[64px] bg-secondary-100 rounded-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-primary-main" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-location" />
                            </svg>
                        </div>
                        <div class="space-y-3">
                            <h4 class="font-semibold">آدرس </h4>
                            <h5 class="text-secondary-400">{{ $fetchData['address'] }}</h5>
                        </div>
                    </div>
                @endif
                @if (isset($fetchData['email']))
                    <div class="bg-white rounded-lg p-4 space-y-4">
                        <div class="w-[64px] h-[64px] bg-secondary-100 rounded-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-primary-main" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-email" />
                            </svg>
                        </div>
                        <div class="space-y-3">
                            <h4 class="font-semibold">ایمیل پشتیبانی</h4>
                            <h5 class="text-secondary-400">{{ $fetchData['email'] }}</h5>
                        </div>
                    </div>
                @endif
            </div>
        </section>
        <!-- FAQ -->
        @include('front::components.homepage.faq')
    </main>
    @error('*')
{{$message}}
    @enderror
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            Livewire.on('swalSuccess', function() {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "نظر شما با موفقیت ثبت شد!",
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        });
    </script>
@endpush
