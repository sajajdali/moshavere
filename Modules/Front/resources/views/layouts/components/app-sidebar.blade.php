
    <!-- start sidebar -->
    <aside class="sidebar__container">
        <ul class="sidebar__menu">
            <li class="sidebar__menu-item">
                <a href="{{ route('front.homePage') }}">خانه</a>
            </li>
            <li class="sidebar__menu-item">
                <a href="{{ route('front.aboutUs') }}">درباره ما</a>
            </li>
            <li class="sidebar__menu-item">
                <a href="{{ route('front.aboutUs') }}">ارتباط با ما</a>
            </li>
            <li class="sidebar__menu-item">
                <a href=""{{route('front.searchPage',['query' => 'پزشکان'])}}">لیست پزشکان</a>
            </li>
            <li class="sidebar__menu-item">
                <a href="{{ route('front.contactUs') }}">ثبت شکایات</a>
            </li>
        </ul>
    </aside>
    <!-- end sidebar -->
