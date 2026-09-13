<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineConsultation\Http\Controllers\ConsultationController;
use Modules\OnlineConsultation\Http\Controllers\PractitionerController;
use Modules\OnlineConsultation\Http\Controllers\VoipLogController;
use Modules\OnlineConsultation\Http\Controllers\CallReportController;
use Modules\OnlineConsultation\Http\Controllers\ConsultantDashboardController;
use Modules\OnlineConsultation\Http\Controllers\ConsultationSmsController;
use Modules\OnlineConsultation\Http\Controllers\ConsultationReminderController;
use Modules\OnlineConsultation\Http\Controllers\ConsultationCaseController;
use Modules\OnlineConsultation\Http\Controllers\ConsultantFinancialReportController;
use Modules\OnlineConsultation\Http\Controllers\ConsultationCallbackController;
use Modules\OnlineConsultation\Http\Controllers\AppointmentAlternatePhoneController;

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
Route::get('/financial-report', [ConsultantFinancialReportController::class, 'index'])->name('financial-report.index');
Route::get('/sms-deliveries', [ConsultationSmsController::class, 'index'])->name('sms-deliveries.index');
Route::get('/sms-reminders', [ConsultationReminderController::class, 'index'])->name('sms-reminders.index');
Route::post('/sms-reminders', [ConsultationReminderController::class, 'store'])->name('sms-reminders.store');
Route::put('/sms-reminders/{rule}', [ConsultationReminderController::class, 'update'])->whereNumber('rule')->name('sms-reminders.update');
Route::delete('/sms-reminders/{rule}', [ConsultationReminderController::class, 'destroy'])->whereNumber('rule')->name('sms-reminders.destroy');
Route::get('/call-reports/appointments/{appointment}', [CallReportController::class, 'appointment'])->whereNumber('appointment')->withTrashed()->name('call-reports.appointment');
Route::put('/call-reports/appointments/{appointment}/time', [CallReportController::class, 'updateAppointmentTime'])->whereNumber('appointment')->name('call-reports.appointment-time.update');
Route::post('/call-reports/appointments/{appointment}/callback', [ConsultationCallbackController::class, 'store'])->whereNumber('appointment')->name('callback.store');
Route::post('/call-reports/appointments/{appointment}/reports', [ConsultationCaseController::class, 'storeReport'])->whereNumber('appointment')->name('case.reports.store');
Route::post('/call-reports/appointments/{appointment}/note', [ConsultationCaseController::class, 'storeNote'])->whereNumber('appointment')->name('case.note.store');
Route::post('/call-reports/appointments/{appointment}/alternate-phones', [AppointmentAlternatePhoneController::class, 'store'])->whereNumber('appointment')->name('alternate-phones.store');
Route::delete('/call-reports/appointments/{appointment}/alternate-phones/{alternatePhone}', [AppointmentAlternatePhoneController::class, 'destroy'])->whereNumber(['appointment', 'alternatePhone'])->name('alternate-phones.destroy');
Route::post('/call-reports/appointments/{appointment}/complete', [ConsultationCaseController::class, 'complete'])->whereNumber('appointment')->name('case.complete');
Route::post('/call-reports/appointments/{appointment}/reopen', [ConsultationCaseController::class, 'reopen'])->whereNumber('appointment')->name('case.reopen');
Route::get('/call-reports/calls/{callLog}', [CallReportController::class, 'call'])->whereNumber('callLog')->name('call-reports.call');
Route::put('/billing/{billingRecord}/approve', [CallReportController::class, 'approveBilling'])->whereNumber('billingRecord')->name('billing.approve');
Route::post('/billing/{billingRecord}/correct', [CallReportController::class, 'correctBilling'])->whereNumber('billingRecord')->name('billing.correct');

Route::post('/call-reports/appointments/{appointment}/patient-no-show', [ConsultationCaseController::class, 'patientNoShow'])->whereNumber('appointment')->name('case.patient-no-show');
