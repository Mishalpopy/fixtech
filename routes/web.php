<?php

use App\Http\Controllers\Admin\Agent\AgentController;
use App\Http\Controllers\Admin\Bookings\BookingController;
use App\Http\Controllers\Admin\Customers\CustomerController;
use App\Http\Controllers\Admin\Hotel\HotelController;
use App\Http\Controllers\Admin\Redeem\RedeemCodeController;
use App\Http\Controllers\Admin\Tickets\TicketController;
use App\Http\Controllers\Admin\Voucher\VoucherCodeController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\User\RoleController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin/')->name('admin:')->group(function () {


    Route::middleware('auth')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        //roles
        Route::resource('roles', RoleController::class)->names('roles');

        //user management
        Route::resource('users', UserController::class)->names('users');
        Route::post('update-user-password/{user}', [UserController::class, 'updatePassword'])->name('users.update_password');


        //bookings
        Route::resource('bookings', BookingController::class)->names('bookings');
        Route::get('/admin/bookings/{booking}/invoice', [BookingController::class, 'downloadInvoice'])->name('admin:bookings.invoice');

        //customers
        Route::resource('customers', CustomerController::class)->names('customers');
        Route::post('customers/{customer}/change-status', [CustomerController::class, 'changeStatus'])->name('customers.change_status');

        //partners
        Route::resource('partners', App\Http\Controllers\Admin\Partners\PartnerController::class)->names('partners');
        Route::post('partners/{partner}/change-status', [App\Http\Controllers\Admin\Partners\PartnerController::class, 'changeStatus'])->name('partners.change_status');
        Route::post('partners/{partner}/approve', [App\Http\Controllers\Admin\Partners\PartnerController::class, 'approve'])->name('partners.approve');
        Route::post('partners/{partner}/reject', [App\Http\Controllers\Admin\Partners\PartnerController::class, 'reject'])->name('partners.reject');
        Route::get('partners-pending-approvals', [App\Http\Controllers\Admin\Partners\PartnerController::class, 'pendingApprovals'])->name('partners.pending');

        //complaints/tickets
        Route::resource('tickets', TicketController::class)->names('tickets');
        Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
        Route::post('tickets/{ticket}/update-status', [TicketController::class, 'updateStatus'])->name('tickets.update_status');
        Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])->name('tickets.attachments.download');


        //settings section
        Route::prefix('settings/')->group(function () {

            //profile
            // Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
            // Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
        });
    });
});



// Route::prefix('trainer')->name('trainer:')->group(base_path('routes/trainer.php'));

require __DIR__ . '/auth.php';
require __DIR__ . '/customer.php';
require __DIR__ . '/partner.php';
