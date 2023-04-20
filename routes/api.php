<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware'=>['api']],function(){

    Route::post('/login', [App\Http\Controllers\UserController::class, 'login'])->name('users.login');
    Route::post('/register', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('/verify', [App\Http\Controllers\UserController::class, 'confirmAccount'])->name('users.confirmAccount');
    Route::post('/forget', [App\Http\Controllers\UserController::class, 'forget'])->name('users.forget');
    Route::post('/resetPassword', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.resetPassword');
    Route::get('/auth/google',[App\Http\Controllers\UserController::class, 'googleAuthRedirect'])->name('users.googleAuthRedirect');
    Route::get('/auth/googleCallback', [App\Http\Controllers\UserController::class, 'googleAuthCallback'])->name('users.googleAuthCallback');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [App\Http\Controllers\UserController::class, 'me'])->name('users.me');
    Route::get('/user', [App\Http\Controllers\UserProfileController::class, 'getProfile'])->name('users.getProfile');
});


