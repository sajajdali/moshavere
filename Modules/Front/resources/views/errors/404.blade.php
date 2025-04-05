@extends('front::layouts.app')

@section('content')
<main class="py-24 bg-secondary-100">
    <section class="mx-auto w-full max-w-[400px] flex flex-col items-center gap-8">
      <img src="{{front_asset('assets/images/404.png')}}" alt="404-not-found" class="w-[200px]" />
      <h3 class="font-bold text-lg">
        متاسفانه صفحه مورد نظر شما یافت نشد!
      </h3>
      <a href="{{route('front.homePage')}}" class="self-stretch btn__blue--round-full">
        رفتن به صفحه اصلی
      </a>
    </section>
  </main>
@endsection