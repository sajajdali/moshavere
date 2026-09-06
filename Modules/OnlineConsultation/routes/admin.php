<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineConsultation\Http\Controllers\ConsultationController;
use Modules\OnlineConsultation\Http\Controllers\PractitionerController;

Route::get('/', [ConsultationController::class, 'dashboard'])->name('dashboard');
Route::get('/settings', [ConsultationController::class, 'settings'])->name('settings');
Route::put('/settings', [ConsultationController::class, 'saveSettings'])->name('settings.save');
Route::get('/practitioners', [PractitionerController::class, 'index'])->name('practitioners');
Route::get('/practitioners/create', [PractitionerController::class, 'create'])->name('practitioners.create');
Route::post('/practitioners', [PractitionerController::class, 'store'])->name('practitioners.store');
Route::get('/practitioners/{practitioner}/edit', [PractitionerController::class, 'edit'])->whereNumber('practitioner')->name('practitioners.edit');
Route::put('/practitioners/{practitioner}', [PractitionerController::class, 'update'])->whereNumber('practitioner')->name('practitioners.update');
