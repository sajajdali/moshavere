<?php

use Illuminate\Support\Facades\Route;
use Modules\Reminder\Livewire\Admin\Reminder\ReminderList;
use Modules\Reminder\Livewire\Admin\Reminder\UpdateOrCreate;

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

Route::group([], function () {
    Route::get('reminder/updateOrCreate',UpdateOrCreate::class)->name('reminder.create')->can('viewAny');
    Route::get('reminder/list',ReminderList::class)->name('reminder.list')->can('viewAny');
});
