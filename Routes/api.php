<?php

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\Api\AuthController;
use Modules\Member\Http\Controllers\Api\MemberController;

Route::prefix('v1')->middleware(['api'])->group(function () {

    Route::prefix('auth')->controller(AuthController::class)->name('auth.')->group(function () {
        Route::POST('register', 'register')->name('register');
        Route::POST('login', 'login')->name('login');
        Route::POST('send_code', 'sendCode')->name('send_code');
        Route::GET('refresh', 'refresh')->name('refresh')->middleware(['auth:member']);
        Route::GET('logout', 'logout')->name('logout')->middleware(['auth:member']);
    });

    Route::prefix('member')
        ->controller(MemberController::class)
        ->name('member.')
        ->middleware(['auth:member'])->group(function () {
            Route::GET('info', 'info')->name('info');
            Route::POST('update_password', 'updatePassword')->name('update_password');
        });

});
