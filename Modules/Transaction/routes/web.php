<?php

use Illuminate\Support\Facades\Route;
use Modules\Transaction\Livewire\TransactionLivewire;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('admin')
    ->middleware(['web', 'admin','can:Transction'])->as('admin.')->group(function () {
        Route::get('/trnasction', TransactionLivewire::class)->name('Transction');
    });
