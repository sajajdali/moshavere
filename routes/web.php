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

Route::get('/support-expired', function () {
    $tenantId = \App\Models\Domain::query()->where('domain', request()->getHost())->value('tenant_id');
    $expiredTenant = $tenantId ? \App\Models\Tenant::findOrFail($tenantId) : null;

    abort_if($expiredTenant === null, 404);

    return view('support-expired', compact('expiredTenant'));
})->name('tenant.support-expired');

Route::get('/site-disabled', function () {
    $tenantId = \App\Models\Domain::query()->where('domain', request()->getHost())->value('tenant_id');
    $disabledTenant = $tenantId ? \App\Models\Tenant::findOrFail($tenantId) : null;

    abort_if($disabledTenant === null || ! $disabledTenant->disabled, 404);

    return view('site-disabled', compact('disabledTenant'));
})->name('tenant.site-disabled');

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

Route::get('tenant', function () {
    dd("Sa");
});
Route::get('/new_site', function () {
    $tenant1 = App\Models\Tenant::create(['id' => 'nobat1']);
    $tenant1 = App\Models\Tenant::find('nobat1');
    $tenant1->domains()->create(['domain' => 'nobat1.test']);
});
