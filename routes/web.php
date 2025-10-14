<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StripeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ProductController::class, 'index'])->name('dashboard');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')->name('cart.index');
    Route::post('/cart/store/{product}', 'store')->name('cart.store');
    Route::put('/cart/update/{product}', 'update')->name('cart.update');
    Route::delete('/cart/destroy/{product}', 'destroy')->name('cart.destroy');
    Route::delete('/cart/clear', 'clear')->name('cart.clear');
});

Route::post('stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

// Route::get('/cart/store/{product}', function(){

// })->name('cart.store');

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['verified'])->group(function () {
            Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
            Route::get('stripe/success', [StripeController::class, 'success'])->name('stripe.success');
            Route::get('stripe/failure', [StripeController::class, 'failure'])->name('stripe.failure');
            
    });

});

require __DIR__.'/auth.php';
