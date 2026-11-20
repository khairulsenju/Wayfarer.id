
<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderProcessingController as AdminOrderProcessingController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RentalReturnController as AdminRentalReturnController;
use App\Http\Controllers\Admin\RentalVerificationController as AdminRentalVerificationController;
use App\Http\Controllers\Admin\FinanceController as AdminFinanceController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CatalogController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\RentalVerificationController as CustomerRentalVerificationController;

use App\Http\Controllers\MidtransWebhookController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer / Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $user = auth()->user();

    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    $products = Product::active()
        ->with('category')
        ->latest()
        ->take(6)
        ->get();

    return view('customer.home', [
        'products' => $products,
    ]);
})->name('home');

Route::get('/catalog', [CatalogController::class, 'index'])
    ->name('catalog.index');

Route::get('/explore', [CatalogController::class, 'explore'])
    ->name('explore');

Route::get('/catalog/{product:slug}', [CatalogController::class, 'show'])
    ->name('catalog.show');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.attempt');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'register'])
        ->name('register.store');

});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Midtrans
|--------------------------------------------------------------------------
*/

Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.notification');

/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:customer'])->group(function () {

    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');

    Route::post('/cart', [CartController::class, 'store'])
        ->name('cart.store');

    Route::delete('/cart', [CartController::class, 'clear'])
        ->name('cart.clear');

    Route::patch('/cart/items/{item}', [CartController::class, 'update'])
        ->whereNumber('item')
        ->name('cart.update');

    Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])
        ->whereNumber('item')
        ->name('cart.destroy');

    /*
    |--------------------------------------------------------------------------
    | Rental Verification
    |--------------------------------------------------------------------------
    */

    Route::get('/rental-verification', [
        CustomerRentalVerificationController::class,
        'index'
    ])->name('rental-verification.index');

    Route::post('/rental-verification', [
        CustomerRentalVerificationController::class,
        'store'
    ])->name('rental-verification.store');

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    Route::get('/checkout', [CheckoutController::class, 'show'])
        ->name('checkout.show');

    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('checkout.store');

    Route::get('/checkout/{order}/pay', [CheckoutController::class, 'pay'])
        ->whereNumber('order')
        ->name('checkout.pay');

    Route::get('/checkout/{order}/finish', [CheckoutController::class, 'finish'])
        ->whereNumber('order')
        ->name('checkout.finish');

    /*
    |--------------------------------------------------------------------------
    | Customer Orders
    |--------------------------------------------------------------------------
    */

    Route::get('/orders', [CustomerOrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])
        ->whereNumber('order')
        ->name('orders.show');

    Route::get('/orders/{order}/invoice', [CustomerOrderController::class, 'invoice'])
        ->whereNumber('order')
        ->name('orders.invoice');

});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('categories', AdminCategoryController::class)
            ->except(['show']);

        Route::resource('products', AdminProductController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Rental Verification Admin
        |--------------------------------------------------------------------------
        */

        Route::get('/rental-verifications', [
            AdminRentalVerificationController::class,
            'index'
        ])->name('rental-verifications.index');

        Route::get('/rental-verifications/{verification}/ktp', [
            AdminRentalVerificationController::class,
            'showKtp'
        ])->name('rental-verifications.ktp');

        Route::post('/rental-verifications/{verification}/approve', [
            AdminRentalVerificationController::class,
            'approve'
        ])->name('rental-verifications.approve');

        Route::post('/rental-verifications/{verification}/reject', [
            AdminRentalVerificationController::class,
            'reject'
        ])->name('rental-verifications.reject');

        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        Route::get('/orders', [AdminOrderController::class, 'index'])
            ->name('orders.index');

        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])
            ->whereNumber('order')
            ->name('orders.show');

        Route::post('/orders/{order}/sync', [AdminOrderController::class, 'syncPayment'])
            ->whereNumber('order')
            ->name('orders.sync');

        /*
        |--------------------------------------------------------------------------
        | Order Processing
        |--------------------------------------------------------------------------
        */

        Route::get('/processing', [AdminOrderProcessingController::class, 'index'])
            ->name('processing.index');

        Route::patch('/processing/{order}', [AdminOrderProcessingController::class, 'update'])
            ->whereNumber('order')
            ->name('processing.update');

        /*
        |--------------------------------------------------------------------------
        | Rental Returns
        |--------------------------------------------------------------------------
        */

        Route::get('/returns', [AdminRentalReturnController::class, 'index'])
            ->name('returns.index');

        Route::post('/returns/{order}', [AdminRentalReturnController::class, 'store'])
            ->whereNumber('order')
            ->name('returns.store');
                    // KEUANGAN WAYFARER
        Route::get('/finance', [AdminFinanceController::class, 'index'])
            ->name('finance.index');

        Route::post('/finance/expenses', [AdminFinanceController::class, 'storeExpense'])
            ->name('finance.expenses.store');

        Route::delete('/finance/expenses/{expense}', [AdminFinanceController::class, 'destroyExpense'])
            ->whereNumber('expense')
            ->name('finance.expenses.destroy');

    });
