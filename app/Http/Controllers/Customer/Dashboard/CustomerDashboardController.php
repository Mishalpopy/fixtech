<?php

namespace App\Http\Controllers\Customer\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerDashboardController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Customer/Dashboard', [
            'customer' => Auth::guard('customer')->user()
        ]);
    }
}

