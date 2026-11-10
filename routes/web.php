<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketplaceController::class, 'index'])->name('home');
Route::get('/listings/{listing}', [MarketplaceController::class, 'listing']);
Route::get('/sellers/{user}', [MarketplaceController::class, 'seller']);
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('app', ['page' => 'login', 'payload' => new stdClass]))->name('login');
    Route::get('/register', fn () => view('app', ['page' => 'register', 'payload' => new stdClass]));
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::middleware(['auth', 'not-banned'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [MarketplaceController::class, 'dashboard']);
    Route::put('/profile', [AuthController::class, 'profile']);
    Route::post('/listings', [ListingController::class, 'store']);
    Route::post('/listings/{listing}/edit', [ListingController::class, 'update']);
    Route::patch('/listings/{listing}/status', [ListingController::class, 'status']);
    Route::post('/listings/{listing}/favorite', [ListingController::class, 'favorite']);
    Route::post('/listings/{listing}/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}/checkout', [OrderController::class, 'checkout']);
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->middleware('throttle:10,1');
    Route::post('/orders/{order}/deliver', [OrderController::class, 'deliver']);
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/review', [OrderController::class, 'review']);
    Route::post('/orders/{order}/dispute', [OrderController::class, 'dispute']);
    Route::get('/messages', [MessageController::class, 'inbox']);
    Route::get('/messages/{user}', [MessageController::class, 'index']);
    Route::post('/messages/{user}', [MessageController::class, 'store'])->middleware('throttle:30,1');
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/categories', [AdminController::class, 'category']);
        Route::post('/users/{user}/ban', [AdminController::class, 'ban']);
        Route::post('/listings/{listing}', [AdminController::class, 'listing']);
        Route::post('/disputes/{dispute}/resolve', [AdminController::class, 'resolve']);
    });
});
