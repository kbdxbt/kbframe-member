<?php

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\Api\AuthController;
use Modules\Member\Http\Controllers\Api\MemberController;

Route::prefix('v1')->middleware(['api'])->group(function () {

    Route::prefix('auth')->controller(AuthController::class)->name('auth.')->group(function () {
        Route::post('register', 'register')->name('register');
        Route::post('login', 'login')->name('login');
        Route::post('send_code', 'sendCode')->name('send_code');
        Route::get('refresh', 'refresh')->name('refresh')->middleware(['auth:member']);
        Route::get('logout', 'logout')->name('logout')->middleware(['auth:member']);
    });

    Route::prefix('member')
        ->controller(MemberController::class)
        ->name('member.')
        ->middleware(['auth:member'])->group(function () {
            Route::get('detail', 'detail')->name('detail');
            Route::post('update', 'update')->name('update');
        });

});
