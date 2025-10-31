<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AdminSliderController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use function Pest\Laravel\get;

Route::get('/', [ProductController::class, 'index'])->name('dashboard');
Route::get('/shop', [ProductController::class, 'shop'])->name('shop.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/s/{vendor:shop_name}', [VendorController::class, 'profile'])->name('vendor.profile');
Route::get('/departments/{department:slug}', [ProductController::class, 'productByDepartment'])->name('departments.products');



Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')->name('cart.index');
    Route::post('/cart/store/{product}', 'store')->name('cart.store');
    Route::put('/cart/update/{product}', 'update')->name('cart.update');
    Route::delete('/cart/destroy/{product}', 'destroy')->name('cart.destroy');
    Route::delete('/cart/clear', 'clear')->name('cart.clear');
    Route::post('/cart/coupon/apply', 'applyCoupon')->name('cart.coupon.apply');
    Route::delete('/cart/coupon/remove', 'removeCoupon')->name('cart.coupon.remove');
});

Route::post('stripe/webhook', [StripeController::class, 'webhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
// Allow Stripe CLI default '/webhook' path as well
Route::post('webhook', [StripeController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

// Route::get('/cart/store/{product}', function(){

// })->name('cart.store');

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Ratings
    Route::post('/products/{product}/rate', [ProductController::class, 'rate'])->name('products.rate');


    Route::middleware(['verified'])->group(function () {
            Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
            Route::get('stripe/success', [StripeController::class, 'success'])->name('stripe.success');
            Route::get('stripe/failure', [StripeController::class, 'failure'])->name('stripe.failure');
            Route::post('/become-a-vendor', [VendorController::class, 'store'])->name('vendor.store');
        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
        // Admin slider quick delete route (used by Filament table HTML action)
        Route::delete('/admin/sliders/{slider}', [AdminSliderController::class, 'destroy'])->name('admin.sliders.destroy');
            
    });

});

require __DIR__.'/auth.php';
