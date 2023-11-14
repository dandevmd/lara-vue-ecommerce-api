<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\EmailVerificationController;




Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


//PASSWORD VERIFICATION
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->middleware('guest')->name('password.update');


Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
    Route::get('user', [AuthController::class, 'getUser']);

    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    Route::get('/orders/admin', [AdminController::class, 'allUsersOrders']);
    Route::put('/orders/{order:id}/update', [AdminController::class, 'updateStatus']);
    Route::delete('/orders/{order:id}/delete', [AdminController::class, 'deleteOrder']);

    Route::get('admin/stats', [AdminController::class, 'getStatistics']);
    Route::put('/admin/make-admin/{user:id}', [AdminController::class, 'makeAdmin']);
    Route::delete('/admin/delete-user/{user:id}', [AdminController::class, 'deleteUser']);

});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/orders', [OrdersController::class, 'index']);
    Route::get('/orders/{order:id}', [OrdersController::class, 'getOrderById']);
    Route::post('/orders/store', [OrdersController::class, 'store']);

    //EMAIL VERIFICATION// For api enough one route
// Route::get('/email/verify', [EmailVerificationController::class, 'sendEmailVerification'])->middleware('auth:sanctum');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])->middleware('auth:sanctum')->name('verification.verify');

    Route::post('/profile/update', [ProfileController::class, 'updateCredentials']);
    Route::delete('/profile/delete', [ProfileController::class, 'deleteAccount']);
    Route::post('logout', [AuthController::class, 'logout']);

});

Route::middleware(['guestOrVerified'])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add/{product:id}', [CartController::class, 'add']);
    Route::put('/cart/update/{product:id}', [CartController::class, 'update']);
    Route::delete('/cart/delete/{product:id}', [CartController::class, 'remove']);
});