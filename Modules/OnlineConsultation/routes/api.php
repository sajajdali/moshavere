<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineConsultation\Http\Controllers\PractitionerAppController;

Route::get('/me', [PractitionerAppController::class, 'show'])->name('me');
Route::put('/availability', [PractitionerAppController::class, 'availability'])->name('availability');
