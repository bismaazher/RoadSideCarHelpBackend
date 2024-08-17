<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthUserController;

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
Route::prefix('user')
    ->as('user')
    ->controller(AuthUserController::class)->group(function () {
        Route::post('register', 'register')->name('register');
        Route::post('login', 'login')->name('login');
        Route::post('forgot', 'forgot');
    });

Route::prefix('user')
    ->as('user.')
    ->middleware("auth:api")
    ->group(function () {
        Route::controller(AuthUserController::class)->group(function () {
            Route::post('logout', 'logout');
            Route::post('change/password', 'changePassword');
            Route::post('profile/update', 'updateProfile');
            Route::get('list', 'getUserList');
            Route::get('profile/{id}', 'getUserProfile');
        });
});
 

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
