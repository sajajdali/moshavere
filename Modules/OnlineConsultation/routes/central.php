<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineConsultation\Http\Controllers\TenantModuleController;

Route::get('/', [TenantModuleController::class, 'index'])->name('index');
Route::put('/{tenantId}', [TenantModuleController::class, 'update'])->name('update');
