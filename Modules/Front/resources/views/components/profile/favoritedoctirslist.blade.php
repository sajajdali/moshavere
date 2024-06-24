<section class="dashboard__main" id="favoriteDoctorListSection" wire:ignore.self>
@if (isset($form['favorite_doctors'] ))
    <div class="bg-white p-4 space-y-5 rounded-lg">
        <h3 class="font-bold">لیست پزشکان محبوب شما</h3>
        @forelse($form['favorite_doctors'] as $key => $doctor)
            <div class="flex flex-col gap-3">
                <div
                    class="border-2 border-secondary-200 rounded-lg flex flex-col sm:flex-row items-center justify-between gap-3 p-4">
                    <div
                        class="w-[70px] h-[70px] overflow-hidden rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-sky">
                        <img src="{{$doctor->avatar}}" alt="doctor-image-name" />
                    </div>
                    <div
                        class="w-[calc(100%-70px-1.25rem)] flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="space-y-3 text-center sm:text-right">
                            <p class="text-lg font-bold">دکتر {{$doctor->full_name}}</p>
                            <p class="bg-secondary-200 text-center rounded-lg py-2 px-3 text-sm">
                                {{$doctor->DocSpecialities()}}
                            </p>
                        </div>
                        <div class="flex flex-col items-center sm:items-end gap-4">
                            <div
                                class="flex items-center gap-3 bg-primary-tint-100 text-primary-main rounded-full py-2 px-5">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="#sprite-save" />
                                </svg>
                                <p> نشان شده </p>
                            </div>
                            <p class="text-secondary-400 text-sm">شماره نظام پزشکی: {{$doctor->dr_licence_number}}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
        @endforelse
    </div>
@endif
</section>
