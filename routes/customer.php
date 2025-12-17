<?php

use App\Http\Controllers\Customer\Auth\CustomerAuthenticatedSessionController;
use App\Http\Controllers\Customer\Dashboard\CustomerDashboardController;
use App\Http\Controllers\Customer\Ticket\TicketController;
use App\Http\Controllers\Customer\Wallet\WalletController;
use App\Http\Controllers\Customer\Review\ReviewController;
use App\Http\Controllers\Customer\Testimonial\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer/')->name('customer:')->group(function () {

    // Guest routes (not authenticated)
    Route::middleware('guest:customer')->group(function () {
        Route::get('login', [CustomerAuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [CustomerAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    // Authenticated customer routes
    Route::middleware(['App\Http\Middleware\CustomerAuthenticate'])->group(function () {
        Route::get('dashboard', [CustomerDashboardController::class, 'dashboard'])->name('dashboard');

        Route::post('logout', [CustomerAuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        // Ticket routes - specific routes must come before resource route
        Route::get('tickets/get-sub-services', [TicketController::class, 'getSubServices'])->name('tickets.get_sub_services');
        Route::get('tickets/get-service-items', [TicketController::class, 'getServiceItems'])->name('tickets.get_service_items');
        Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])
            ->name('tickets.attachments.download');
        Route::resource('tickets', TicketController::class);

        // Wallet routes
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [WalletController::class, 'index'])->name('index');
            Route::get('/deposit', [WalletController::class, 'deposit'])->name('deposit');
            Route::post('/deposit', [WalletController::class, 'processDeposit'])->name('deposit.process');
            Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
            Route::get('/withdrawal', [WalletController::class, 'withdrawal'])->name('withdrawal');
            Route::post('/withdrawal', [WalletController::class, 'processWithdrawal'])->name('withdrawal.process');
        });

        // Reviews routes
        Route::resource('reviews', ReviewController::class)->names('reviews');

        // Testimonials routes
        Route::resource('testimonials', TestimonialController::class)->names('testimonials');
    });

    // Wallet callback route (no authentication required for Paymob redirects)
    Route::match(['get', 'post'], 'wallet/deposit/callback', [WalletController::class, 'depositCallback'])->name('wallet.deposit.callback');
});

