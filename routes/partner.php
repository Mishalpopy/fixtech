<?php

use App\Http\Controllers\Partner\Auth\PartnerAuthenticatedSessionController;
use App\Http\Controllers\Partner\Auth\PartnerRegistrationController;
use App\Http\Controllers\Partner\Dashboard\PartnerDashboardController;
use App\Http\Controllers\Partner\Profile\PartnerProfileController;
use App\Http\Controllers\Partner\Tickets\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('partner/')->name('partner:')->group(function () {

    // Guest routes (not authenticated)
    Route::middleware('guest:partner')->group(function () {
        Route::get('login', [PartnerAuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [PartnerAuthenticatedSessionController::class, 'store'])->name('login.store');

        Route::get('register', [PartnerRegistrationController::class, 'create'])
            ->name('register');

        Route::post('register', [PartnerRegistrationController::class, 'store'])->name('register.store');
    });

    // Authenticated partner routes
    Route::middleware(['App\Http\Middleware\PartnerAuthenticate'])->group(function () {
        Route::get('dashboard', [PartnerDashboardController::class, 'dashboard'])->name('dashboard');
        
        // Profile routes
        Route::get('profile', [PartnerProfileController::class, 'index'])->name('profile');
        Route::put('profile', [PartnerProfileController::class, 'update'])->name('profile.update');
        Route::post('documents/{type}/upload', [PartnerProfileController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('documents/{type}/delete', [PartnerProfileController::class, 'deleteDocument'])->name('documents.delete');

        // Complaint routes
        Route::resource('tickets', TicketController::class)->names('tickets');
        Route::post('tickets/{ticket}/update-status', [TicketController::class, 'updateStatus'])->name('tickets.update_status');
        Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])->name('tickets.attachments.download');

        Route::post('logout', [PartnerAuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
    });
});

