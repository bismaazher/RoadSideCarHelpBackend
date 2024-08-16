<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ShippingAddressController;
use App\Http\Controllers\Api\AuthUserController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserHabitController;

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
//Route::post('user/register',[AuthUserController::class,'register']);
Route::prefix('user')
    ->as('user')
    ->controller(AuthUserController::class)->group(function () {
        Route::post('register', 'register')->name('register');
        Route::post('login', 'login')->name('login');
        Route::post('forgot', 'forgot');
        Route::post('social/login','getSocialData')->name('sociallogin');
    });

Route::controller(AuthUserController::class)->group(function (){
    Route::get('habit','getHabitList');
    Route::get('activity','getActivityList');
    Route::get('field','getFieldList');
});

Route::prefix('user')
    ->as('user.')
    ->middleware("auth:api")
    ->group(function () {
        Route::controller(AuthUserController::class)->group(function () {
            Route::post('logout', 'logout');
            Route::post('verify/otp', 'verifyOtp');
            Route::get('resend/otp', 'resendOtp');
            Route::post('change/password', 'changePassword');
            Route::post('profile/update', 'updateProfile');
            Route::get('list', 'getUserList');
            Route::get('profile/{id}', 'getUserProfile');
            Route::post('profile', 'userProfileDetails');
            Route::post('settings/notification', 'notificationSetting');
            Route::post('settings/delete-account', 'deleteAccount');
        });
});

Route::middleware("auth:api")->group(function(){
        Route::controller(UserHabitController::class)->group(function (){
            Route::get('user_habit/get','getMyHabit');
            Route::post('user_habit/add','createHabit');
            Route::post('user_habit/update','habitRecordTime');
        });

        Route::controller(PostController::class)->group(function (){
            Route::get('post', 'getPost');
            Route::post('post/create','createPost');
            Route::delete('post/{id}', 'deletePost');
        });
        
        Route::controller(BookingController::class)->group(function (){
            Route::post('booking/create', 'createBooking');
        });
});

        // Route::controller(CartController::class)->group(function (){
        //     Route::post('cart/add','store');
        //     Route::get('cart','showCart');
        //     Route::post('cart/delete','deleteCart');
        //     Route::post('cart/update-quantity','updateProductQuantity');
        //     Route::post('cart/delete/product','deleteProduct');
        // });

        // Route::controller(ShippingAddressController::class)->group(function (){
        //     Route::post('shipping-address/add','store');
        //     Route::get('shipping-address','getShippingAddress');
        // });

        // Route::controller(OrderController::class)->group(function (){
        //     Route::post('order/checkout','checkout');
        //     Route::get('order','getOrders');
        //     Route::get('order/delivered','getDeliveredOrders');
        //     Route::get('order/detail','orderDetailSummary');
        // });

        // Route::controller(SubscriptionController::class)->group(function (){
        //     Route::get('subscriptions','index');
        //     Route::post('subscribe','subscribe');
        // });

        // Route::controller(ReviewController::class)->group(function (){
        //     Route::post('reviews/add','create');
        //     Route::get('reviews','index');
        // });

        // Route::controller(SettingController::class)->group(function (){
        //     Route::get('terms_and_condition','getTermsAndCondition');
        //     Route::get('privacy-policy','getPrivacyPolicy');
        // });

        // Route::resource('categories', CategoryController::class)->only(['index']);
        // Route::resource('products', ProductController::class)->only(['index', 'show']);
        // Route::post('apply-promo',[PromoController::class,'applyPromoCode']);
        // Route::get('notifications',[NotificationController::class,'index']);

 

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
