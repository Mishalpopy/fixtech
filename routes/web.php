<?php

use App\Http\Controllers\Admin\Agent\AgentController;
use App\Http\Controllers\Admin\Bookings\BookingController;
use App\Http\Controllers\Admin\Customers\CustomerController;
use App\Http\Controllers\Admin\Hotel\HotelController;
use App\Http\Controllers\Admin\Redeem\RedeemCodeController;
use App\Http\Controllers\Admin\Services\ServiceController;
use App\Http\Controllers\Admin\Services\SubServiceController;
use App\Http\Controllers\Admin\Services\ServiceItemController;
use App\Http\Controllers\Admin\Services\ServiceProcessController;
use App\Http\Controllers\Admin\Services\SubServicePriceChartController;
use App\Http\Controllers\Admin\Services\SubServiceFaqController;
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

        //complaints/tickets - specific routes must come before resource route
        Route::get('tickets/get-sub-services', [TicketController::class, 'getSubServices'])->name('tickets.get_sub_services');
        Route::get('tickets/get-service-items', [TicketController::class, 'getServiceItems'])->name('tickets.get_service_items');
        Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])->name('tickets.attachments.download');
        Route::resource('tickets', TicketController::class)->names('tickets');
        Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
        Route::post('tickets/{ticket}/update-status', [TicketController::class, 'updateStatus'])->name('tickets.update_status');

        //services
        Route::resource('services', ServiceController::class)->names('services');
        Route::post('services/{service}/change-status', [ServiceController::class, 'changeStatus'])->name('services.change_status');

        //sub services
        Route::resource('sub-services', SubServiceController::class)->names('sub-services');
        Route::post('sub-services/{subService}/change-status', [SubServiceController::class, 'changeStatus'])->name('sub-services.change_status');

        //service items
        Route::resource('service-items', ServiceItemController::class)->names('service-items');
        Route::post('service-items/{serviceItem}/change-status', [ServiceItemController::class, 'changeStatus'])->name('service-items.change_status');

        //service processes
        Route::resource('processes', ServiceProcessController::class)->names('processes');
        Route::get('processes/bulk/create', [ServiceProcessController::class, 'bulkCreate'])->name('processes.bulk.create');
        Route::post('processes/bulk/store', [ServiceProcessController::class, 'bulkStore'])->name('processes.bulk.store');
        Route::post('processes/{process}/change-status', [ServiceProcessController::class, 'changeStatus'])->name('processes.change_status');

        //price charts
        Route::resource('price-charts', SubServicePriceChartController::class)->names('price-charts');
        Route::get('price-charts/bulk/create', [SubServicePriceChartController::class, 'bulkCreate'])->name('price-charts.bulk.create');
        Route::post('price-charts/bulk/store', [SubServicePriceChartController::class, 'bulkStore'])->name('price-charts.bulk.store');
        Route::post('price-charts/{priceChart}/change-status', [SubServicePriceChartController::class, 'changeStatus'])->name('price-charts.change_status');

        //faqs
        Route::resource('faqs', SubServiceFaqController::class)->names('faqs');
        Route::get('faqs/bulk/create', [SubServiceFaqController::class, 'bulkCreate'])->name('faqs.bulk.create');
        Route::post('faqs/bulk/store', [SubServiceFaqController::class, 'bulkStore'])->name('faqs.bulk.store');
        Route::post('faqs/{faq}/change-status', [SubServiceFaqController::class, 'changeStatus'])->name('faqs.change_status');


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
