<?php

use App\Http\Controllers\Api\Customer\CustomerAuthController;
use App\Http\Controllers\Api\Customer\TicketController as CustomerTicketController;
use App\Http\Controllers\Api\Partner\PartnerAuthController;
use App\Http\Controllers\Api\Partner\TicketController as PartnerTicketController;
use App\Http\Controllers\Api\Admin\TicketController as AdminTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Public API routes
Route::prefix('v1')->group(function () {
    
    // Customer API Routes
    Route::prefix('customer')->name('api.customer.')->group(function () {
        // Public routes (no authentication required)
        Route::post('register', [CustomerAuthController::class, 'register'])->name('register');
        Route::post('login', [CustomerAuthController::class, 'login'])->name('login');
        
        // Protected routes (authentication required)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [CustomerAuthController::class, 'logout'])->name('logout');
            Route::get('profile', [CustomerAuthController::class, 'profile'])->name('profile');
            
            // Complaint routes
            Route::apiResource('complaints', CustomerTicketController::class);
            Route::get('complaints/{ticket}/attachments/{attachment}/download', [CustomerTicketController::class, 'downloadAttachment'])->name('complaints.attachments.download');
        });
    });

    // Partner API Routes
    Route::prefix('partner')->name('api.partner.')->group(function () {
        // Public routes (no authentication required)
        Route::post('register', [PartnerAuthController::class, 'register'])->name('register');
        Route::post('login', [PartnerAuthController::class, 'login'])->name('login');
        
        // Protected routes (authentication required)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [PartnerAuthController::class, 'logout'])->name('logout');
            Route::get('profile', [PartnerAuthController::class, 'profile'])->name('profile');
            
            // Assigned complaints routes
            Route::apiResource('complaints', PartnerTicketController::class)->only(['index', 'show']);
            Route::post('complaints/{ticket}/update-status', [PartnerTicketController::class, 'updateStatus'])->name('complaints.update_status');
            Route::get('complaints/{ticket}/attachments/{attachment}/download', [PartnerTicketController::class, 'downloadAttachment'])->name('complaints.attachments.download');
        });
    });

    // Admin API Routes
    Route::prefix('admin')->name('api.admin.')->group(function () {
        // Protected routes (authentication required)
        Route::middleware('auth:sanctum')->group(function () {
            // Complaint management routes
            Route::apiResource('complaints', AdminTicketController::class);
            Route::post('complaints/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('complaints.assign');
            Route::post('complaints/{ticket}/update-status', [AdminTicketController::class, 'updateStatus'])->name('complaints.update_status');
            Route::get('complaints/{ticket}/attachments/{attachment}/download', [AdminTicketController::class, 'downloadAttachment'])->name('complaints.attachments.download');
            
            // Helper routes
            Route::get('customers', [AdminTicketController::class, 'getCustomers'])->name('customers');
            Route::get('partners', [AdminTicketController::class, 'getPartners'])->name('partners');
        });
    });
});

// Health check endpoint
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()->toDateTimeString(),
    ]);
});


