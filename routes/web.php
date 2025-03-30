<?php

use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Shetabit\Payment\Facade\Payment;
use Illuminate\Support\Facades\Route;
use Modules\Setting\Enum\SettingKeyEnum;
use App\Http\Controllers\ShortLinkController;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/s/{param}', [ShortLinkController::class, 'index']);
Route::get('tenant', function () {
    dd("Sa");
});
Route::get('/new_site', function () {
    $tenant1 = App\Models\Tenant::create(['id' => 'nobat1']);
    $tenant1 = App\Models\Tenant::find('nobat1');
    $tenant1->domains()->create(['domain' => 'nobat1.test']);
});
