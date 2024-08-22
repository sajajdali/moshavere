<?php

use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortLinkController;

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

// Route::get('teettt', function () {
//     $user = Modules\User\Entities\User::doctors(); // Assuming this returns a collection
//     $filteredUsers = $user->reject(function ($q) {
//         return $q->metas->contains(function ($meta) {
//             return $meta->meta_key === Modules\User\Enum\UserMetaEnum::LAST_NAME &&
//                 in_array($meta->meta_value, ['تاجپور', 'دهقانی زاده', 'گلشن', 'یوسفی', 'خلیل پور', 'درببان', 'کاظم زاده', 'شایسته', 'اقبالی']);
//         });
//     });
//     $filteredUsers->each(function ($q) {
//         $q->active_appointment = false;
//     });
//     echo 'done';
// });
