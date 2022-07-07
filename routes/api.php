<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login')->name('auth.login');
    Route::post('register', 'register')->name('auth.register');
    Route::post('logout', 'logout')->name('auth.logout');
    Route::post('refresh', 'refresh')->name('auth.refresh');
    Route::get('me', 'me')->name('auth.me');
});

Route::controller(AttendanceController::class)->prefix('attendance')->group(function () {
    Route::middleware(['user-access:user'])->group(function () {
        Route::post('time_in', 'time_in')->name('attendance.time_in');
        Route::post('time_out', 'time_out')->name('attendance.time_out');
    });

    Route::middleware(['user-access:admin'])->group(function () {
        Route::get('', 'data')->name('attendance.data');
    });
});

Route::controller(UserController::class)->prefix('user')->group(function () {
    Route::middleware(['user-access:admin'])->group(function () {
        Route::get('', 'data')->name('user.data');
    });
});
