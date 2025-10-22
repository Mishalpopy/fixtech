<?php

use App\Http\Controllers\Customer\Auth\CustomerAuthenticatedSessionController;
use App\Http\Controllers\Customer\Dashboard\CustomerDashboardController;
use App\Http\Controllers\Customer\Ticket\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer/')->name('customer:')->group(function () {

    // Guest routes (not authenticated)
    Route::middleware('guest:customer')->group(function () {
        Route::get('login', [CustomerAuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [CustomerAuthenticatedSessionController::class, 'store']);
    });

    // Authenticated customer routes
    Route::middleware(['App\Http\Middleware\CustomerAuthenticate'])->group(function () {
        Route::get('dashboard', [CustomerDashboardController::class, 'dashboard'])->name('dashboard');

        Route::post('logout', [CustomerAuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        // Ticket routes
        Route::resource('tickets', TicketController::class);
        Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])
            ->name('tickets.attachments.download');
    });
});

