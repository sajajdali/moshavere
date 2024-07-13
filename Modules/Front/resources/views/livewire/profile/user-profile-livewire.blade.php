<div>
    @include('front::layouts.components.alert')
    <main class="bg-secondary-100 py-10 md:py-16">
        <section class="dashboard__container">
            <aside class="dashboard__side">
                <ul>
                    <li class="dashboard__side-item active" data-section='personalInfoSection' wire:ignore.self>
                        <a href="#">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-user" />
                            </svg>
                            <p>اطلاعات شخصی</p>
                        </a>
                    </li>
                    {{-- <li class="dashboard__side-item" data-section='passwordSection'>
                        <a href="#">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-lock" />
                            </svg>
                            <p>رمز عبور</p>
                        </a>
                    </li> --}}
                    <li class="dashboard__side-item" data-section='userAppointmentSection' wire:ignore.self>
                        <a href="#">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-date" />
                            </svg>
                            <p>نوبت‌های من</p>
                        </a>
                    </li>
                    <li class="dashboard__side-item" data-section='myCommentSection' wire:ignore.self>
                        <a href="#">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-comment" />
                            </svg>
                            <p>نظرات من</p>
                        </a>
                    </li>
                    <li class="dashboard__side-item" data-section='favoriteDoctorListSection'>
                        <a href="#">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-save" />
                            </svg>
                            <p>لیست پزشکان محبوب</p>
                        </a>
                    </li>
                    <li>
                        <hr class="border-secondary-200" />
                    </li>
                    <li class="dashboard__side-item dashboard__side-item--danger">
                        <a href="{{route('front.logout')}}">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-logout" />
                            </svg>
                            <p>خروج از حساب</p>
                        </a>
                    </li>
                </ul>
            </aside>
            @include('front::components.profile.personalinfo')
            {{-- @include('front::components.profile.pasword') --}}
            @include('front::components.profile.myappointment')
            @include('front::components.profile.mycomments')
            @include('front::components.profile.favoritedoctirslist')
        </section>
    </main>
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            // Hide all sections initially except the first one
            $('.dashboard__main').hide();
            $('.dashboard__main').first().show();

            $('.dashboard__side-item').on('click', function() {
                // Remove active class from all menu items and add to the clicked one
                $('.dashboard__side-item').removeClass('active');
                $(this).addClass('active');

                // Hide all sections
                $('.dashboard__main').hide();

                // Show the section corresponding to the clicked menu item
                var sectionToShow = $(this).data('section');
                $('#' + sectionToShow).fadeIn();
            });
        });
    </script>
@endpush
