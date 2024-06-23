<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Livewire\Auth\User\Login;
use Modules\Front\Livewire\Payment\Invoice;
use Modules\Front\Livewire\Auth\User\Logout;
use Modules\Front\Livewire\FeedBack\Questions;
use Modules\Front\Livewire\Auth\User\Registration;
use Modules\Front\Livewire\AboutUs\AboutUsLiveWire;
use Modules\Front\Livewire\SetAppointment\Checkout;
use Modules\Front\Livewire\HomePage\HomePageLivewire;
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
    Route::get('/login', Login::class)->name('front.login');
    Route::get('payment/invoice/{transaction_id}', Invoice::class)->name('front.payment.invoice');
    Route::get('payment/invoice/call-back', [\Modules\Front\Livewire\Payment\Invoice::class, 'zarinCallback'])->name('front.payment.invoice.callBack');
    Route::get('appintment/feedBack/{appointmentUser_id}', Questions::class)->name('front.feedBack');
    Route::get('/aboutus', AboutUsLiveWire::class)->name('front.aboutUs');
    Route::get('/appointment/days', ShowAvailableDayForDoctor::class)->name('setAppointment.days');
    Route::get('/appointment/checkout', Checkout::class)->name('setAppointment.checkout');
    Route::get('/appointment/detail/{tracking_code}', AppointmentDetail::class)->name('front.setAppointment.detail');
    Route::get('/doctor/profile/{doctor_id}', DoctorProfileLivewire::class)->name('front.doctor.profile');
});
Route::middleware(['web', 'auth'])->name('front.')->group(function () {
    Route::get('/logout', Logout::class)->name('logout');
    Route::get('/registration', Registration::class)->name('user.registration');
    Route::get('/profile', UserProfileLivewire::class)->name('user.profile');
});
