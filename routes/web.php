<?php

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

// Route::get('test_ui', function () {
//     $app = AppointmentUser::find(119) ;
//     $app->user->national_code = '0440668736' ;
//     dd( $app->user->national_code);
// });
