<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Livewire\FinanceDashboard;
use Modules\Finance\Livewire\PatientReport;
use Modules\Finance\Livewire\PaymentList;
use Modules\Finance\Livewire\PurposeManager;

Route::prefix('admin')
    ->middleware(['web', 'admin', 'can:finance', \Modules\Finance\app\Http\Middleware\EnsureFinanceModuleEnabled::class])->as('admin.')->group(function () {
        Route::get('finance', FinanceDashboard::class)->name('finance.dashboard');
        Route::get('finance/payments', PaymentList::class)->name('finance.payments');
        Route::get('finance/patients', PatientReport::class)->name('finance.patients');
        // the finance of a patient is a tab of the patient file
        Route::get('finance/patient/{user}', fn ($user) => redirect()->to(route('admin.user.document', $user) . '#finance'))->name('finance.patient');
        Route::get('finance/purposes', PurposeManager::class)->middleware('can:finance.purposes')->name('finance.purposes');
    });
