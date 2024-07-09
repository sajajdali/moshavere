<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Livewire\Auth\User\Login;
use Modules\Front\Livewire\Payment\Invoice;
use Modules\Front\Livewire\Auth\User\Logout;
use Modules\Front\Livewire\FeedBack\Questions;
use Modules\Front\Livewire\Auth\User\Registration;
use Modules\Front\Livewire\AboutUs\AboutUsLiveWire;
use Modules\Front\Livewire\Auth\Doctor\DoctorLogin;
use Modules\Front\Livewire\HomePage\SearchLivewire;
use Modules\Front\Livewire\SetAppointment\Checkout;
use Modules\Front\Livewire\HomePage\HomePageLivewire;
use Modules\Front\Livewire\ContactUs\ContactUsLivewire;
use Modules\Front\Livewire\Profile\UserProfileLivewire;
use Modules\Front\Livewire\Auth\Doctor\DoctorRegistration;
use Modules\Front\Livewire\SetAppointment\AppointmentDetail;
use Modules\Front\Livewire\DoctorProfile\DoctorProfileLivewire;
use Modules\Front\Livewire\SetAppointment\ShowAvailableDayForDoctor;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::get('/', HomePageLivewire::class)->name('front.homePage');
    Route::get('/search', SearchLivewire::class)->name('front.searchPage')->middleware('throttle:5,1');
    Route::get('/login', Login::class)->name('front.login.user')->middleware('throttle:20,1');
    Route::get('/login-doctor', DoctorLogin::class)->name('front.login.doctor');
    Route::get('/registration-doctor', DoctorRegistration::class)->name('front.registration.doctor');
    Route::get('payment/invoice/{transaction_id}', Invoice::class)->name('front.payment.invoice');
    Route::get('payment/invoice/call-back', [\Modules\Front\Livewire\Payment\Invoice::class, 'zarinCallback'])->name('front.payment.invoice.callBack');
    Route::get('/aboutus', AboutUsLiveWire::class)->name('front.aboutUs');
    Route::get('/contact-us', ContactUsLivewire::class)->name('front.contactUs');
    Route::get('/appointment/days', ShowAvailableDayForDoctor::class)->name('front.setAppointment.days');
    Route::get('/appointment/checkout', Checkout::class)->name('setAppointment.checkout');
    Route::get('/appointment/detail/{tracking_code}', AppointmentDetail::class)->name('front.setAppointment.detail');
    Route::get('/appointment/detail/{tracking_code}/call-back', [\Modules\Front\Livewire\SetAppointment\AppointmentDetail::class, 'bankCallback'])->name('front.setAppointment.detail.zarinpal');
    Route::get('/doctor/profile/{doctor_id}', DoctorProfileLivewire::class)->name('front.doctor.profile');
});
Route::middleware(['web', 'auth'])->name('front.')->group(function () {
    Route::get('/logout', Logout::class)->name('logout');
    Route::get('/registration', Registration::class)->middleware('throttle:20,1')->name('user.registration');
    Route::get('/profile', UserProfileLivewire::class)->middleware('throttle:20,1')->name('user.profile');
    Route::get('appintment/feedBack/{appointmentUser_id}', Questions::class)->middleware('throttle:20,1')->name('front.feedBack');
});
