<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Http\Controllers\NewAppController;
use Modules\Front\Livewire\Auth\Doctor\DoctorRegistration;
use Modules\Front\Livewire\Auth\User\Logout;
use Modules\Front\Livewire\FeedBack\Questions;
use Modules\Front\Livewire\SetAppointment\Checkout;

/*
|--------------------------------------------------------------------------
| مسیرهای قالب جدید (اپ React)
|--------------------------------------------------------------------------
|
| وقتی قالب جدید در تنظیمات فعال باشد، این فایل به جای livewire.php بارگذاری
| می شود. صفحات سمت بیمار را اپ React مدیریت می کند و مسیریابی در مرورگر
| انجام می شود؛ بنابراین همه آنها به یک صفحه shell می رسند.
|
| نکته: نام مسیرها باید همان نام های قبلی بماند، چون قالب های سمت سرور
| (مثل هدر و فوتر پنل پزشک) با route('front.*') به آنها ارجاع می دهند.
*/

Route::middleware(['web'])->group(function () {
    // مسیرهایی که همچنان سمت سرور رندر می شوند
    Route::get('/appointment/checkout', Checkout::class)->name('setAppointment.checkout');
    Route::get('/registration-doctor', DoctorRegistration::class)->name('front.registration.doctor');
    Route::get('feed/{appointmentUser_id}/{user_id}', Questions::class)
        ->middleware('throttle:20,1')->name('front.feedBack');
});

/*
 * ورود پزشک با طرح جدید؛ صفحه ورود را اپ React نمایش می دهد.
 * پزشکی که از قبل وارد شده، مستقیم به پیشخوان می رود.
 */
Route::middleware(['web'])->get('/login-doctor', function (NewAppController $controller) {
    if (auth()->user()?->can('ADMIN_ACCESS')) {
        return redirect()->route('admin.dashboard');
    }

    // کنترلر یک View برمیگرداند؛ برای تنظیم هدرها باید در response پیچیده شود
    return response(app()->call($controller))->withHeaders([
        'Cache-Control' => 'no-store, private',
        'Pragma' => 'no-cache',
        'X-Frame-Options' => 'DENY',
    ]);
})->name('front.login.doctor');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/logout', Logout::class)->name('front.logout');
});

/*
 * صفحات سمت بیمار که اپ React آنها را رندر می کند.
 * نام ها عمداً با نسخه Livewire یکسان نگه داشته شده اند تا لینک های موجود
 * در قالب های سمت سرور نشکنند.
 */
Route::middleware(['web'])->group(function () {
    // لینک های قالب قبلی خدمت و انتخاب روز، به صفحه های معادل در اپ React هدایت می شوند
    Route::get('/service/{service_id}/{service_name?}', NewAppController::class)->name('front.services');
    Route::get('/appointment/days', function (\Illuminate\Http\Request $request) {
        $doctorId = $request->query('doctor_id');

        return $doctorId ? redirect()->route('front.doctor.profile', ['doctor_id' => $doctorId]) : redirect('/');
    })->name('front.setAppointment.days');
    Route::get('/doctor/profile/{doctor_id}/{doctor_name?}', function (string $doctor_id, ?string $doctor_name = null) {
        return redirect()->route('front.doctor.profile', ['doctor_id' => $doctor_id, 'doctor_name' => $doctor_name]);
    })->whereNumber('doctor_id');
    Route::get('/', NewAppController::class)->name('front.homePage');
    Route::get('/search', NewAppController::class)->name('front.searchPage');
    Route::get('/login', NewAppController::class)->name('front.login.user');
    Route::get('/aboutus', NewAppController::class)->name('front.aboutUs');
    Route::get('/contact-us', NewAppController::class)->name('front.contactUs');
    Route::get('/profile', NewAppController::class)->name('front.user.profile');
    Route::get('/chat/{onlineAppId}', NewAppController::class)->name('front.user.chatroom');
    // امضای این مسیر با نسخه قبلی یکسان است تا لینک های موجود کار کنند
    Route::get('/doctor/{doctor_id}/{doctor_name?}', NewAppController::class)->name('front.doctor.profile');
    Route::get('/appointment/{tracking_code}', NewAppController::class)
        ->where('tracking_code', '[A-Za-z0-9_-]+')
        ->name('front.setAppointment.detail');
    // لینک های قالب قبلی (مثلاً پیامک های ارسال شده پیش از فعال شدن قالب جدید)
    Route::get('/appointment/detail/{tracking_code}', function (string $tracking_code) {
        return redirect()->route('front.setAppointment.detail', [
            'tracking_code' => preg_replace('/[^0-9]/', '', $tracking_code),
        ]);
    })->name('front.setAppointment.detail.legacy');
});

/*
 * هر مسیر دیگری هم به اپ React می رسد تا مسیریابی سمت مرورگر کار کند.
 * الگوی زیر مسیرهای admin، api، livewire و فایل های استاتیک را کنار می گذارد
 * تا پنل مدیریت و درخواست های داخلی دست نخورده بمانند.
 * لینک های کوتاه پیامک (/s/{code} در routes/tenant.php) هم نباید اینجا گرفته شوند؛
 * وگرنه middleware «غیر فعال سازی ui برای بیماران» بیمار را به صفحه ورود میفرستد.
 */
Route::middleware(['web'])
    ->get('/{any}', NewAppController::class)
    ->where('any', '^(?!admin|api|livewire|storage|build|assets|vendor|_debugbar|s(?:/|$)).*$');

