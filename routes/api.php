<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\EmailVerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Route::get('/email/verify', [EmailVerificationController::class, 'sendEmailVerification'])->middleware('auth:sanctum');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])->middleware('auth:sanctum')->name('verification.verify');

Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
    Route::get('user', [AuthController::class, 'getUser']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
});

Route::middleware(['guestOrVerified'])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add/{product:id}', [CartController::class, 'add']);
    Route::put('/cart/update/{product:id}', [CartController::class, 'update']);
    Route::delete('/cart/delete/{product:id}', [CartController::class, 'remove']);
});