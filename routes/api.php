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
    //currentUser
    Route::get('/me',[App\Http\Controllers\UserController::class, 'me'])->name('me');
    Route::post('/logout',[App\Http\Controllers\UserController::class, 'logout'])->name('me.logout');
    Route::post('/me/profilePic',[App\Http\Controllers\UserController::class, 'updateProfilePic'])->name('me.updateProfilePic');
    //profiles
    Route::get('/user', [App\Http\Controllers\UserProfileController::class, 'getProfile'])->name('user.profile');
    Route::get('/me/profileViews', [App\Http\Controllers\UserProfileController::class, 'getProfileViews'])->name('me.profileViews');
    //friendships
    Route::get('/me/friends', [App\Http\Controllers\FriendshipController::class, 'getFriends'])->name('me.friends');
    Route::get('/me/blocks', [App\Http\Controllers\FriendshipController::class, 'getBlockList'])->name('me.blocks');
    Route::get('/me/friendRequests', [App\Http\Controllers\FriendshipController::class, 'getFriendRequests'])->name('me.friendRequests');
    Route::post('/user/interact', [App\Http\Controllers\FriendshipController::class, 'interact'])->name('user.interact');
    Route::get('/user/search', [App\Http\Controllers\FriendshipController::class, 'search'])->name('user.search');
    //messages
    Route::post('message',[App\Http\Controllers\MessageController::class, 'sendMessage'])->name('message.send');
    Route::delete('message/{id}',[App\Http\Controllers\MessageController::class, 'deleteMessage'])->name('message.delete');
    Route::get('message',[App\Http\Controllers\MessageController::class, 'getPaginatedMessages'])->name('message.getAll');
});


