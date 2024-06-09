<?php

use Modules\Front\Admin\Livewire\faq\FaqLivewire ;
use Modules\Front\Livewire\Admin\Comment\Commentlivewire;
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
    Route::get('faq', FaqLivewire::class)->name('faq');
    Route::get('comment', Commentlivewire::class)->name('comment');
});

