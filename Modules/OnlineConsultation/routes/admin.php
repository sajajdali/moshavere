<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineConsultation\Http\Controllers\ConsultationController;
use Modules\OnlineConsultation\Http\Controllers\PractitionerController;
use Modules\OnlineConsultation\Http\Controllers\VoipLogController;
use Modules\OnlineConsultation\Http\Controllers\CallReportController;
use Modules\OnlineConsultation\Http\Controllers\ConsultantDashboardController;
use Modules\OnlineConsultation\Http\Controllers\ConsultationSmsController;

Route::get('/', [ConsultationController::class, 'dashboard'])->name('dashboard');
Route::get('/settings', [ConsultationController::class, 'settings'])->name('settings');
Route::put('/settings', [ConsultationController::class, 'saveSettings'])->name('settings.save');
Route::get('/practitioners', [PractitionerController::class, 'index'])->name('practitioners');
Route::get('/practitioners/create', [PractitionerController::class, 'create'])->name('practitioners.create');
Route::post('/practitioners', [PractitionerController::class, 'store'])->name('practitioners.store');
Route::get('/practitioners/{practitioner}/edit', [PractitionerController::class, 'edit'])->whereNumber('practitioner')->name('practitioners.edit');
Route::put('/practitioners/{practitioner}', [PractitionerController::class, 'update'])->whereNumber('practitioner')->name('practitioners.update');
Route::get('/voip-logs', [VoipLogController::class, 'index'])->name('voip.logs');
Route::get('/call-reports', [CallReportController::class, 'index'])->name('call-reports.index');
Route::get('/consultants-dashboard', [ConsultantDashboardController::class, 'index'])->name('consultants-dashboard.index');
Route::get('/consultants-dashboard/export', [ConsultantDashboardController::class, 'export'])->name('consultants-dashboard.export');
Route::get('/consultants-dashboard/{practitioner}', [ConsultantDashboardController::class, 'show'])->whereNumber('practitioner')->name('consultants-dashboard.show');
Route::get('/sms-deliveries', [ConsultationSmsController::class, 'index'])->name('sms-deliveries.index');
Route::get('/call-reports/appointments/{appointment}', [CallReportController::class, 'appointment'])->whereNumber('appointment')->name('call-reports.appointment');
Route::get('/call-reports/calls/{callLog}', [CallReportController::class, 'call'])->whereNumber('callLog')->name('call-reports.call');
Route::put('/billing/{billingRecord}/approve', [CallReportController::class, 'approveBilling'])->whereNumber('billingRecord')->name('billing.approve');
Route::post('/billing/{billingRecord}/correct', [CallReportController::class, 'correctBilling'])->whereNumber('billingRecord')->name('billing.correct');
