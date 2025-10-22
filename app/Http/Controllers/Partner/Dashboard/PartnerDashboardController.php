<?php

namespace App\Http\Controllers\Partner\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PartnerDashboardController extends Controller
{
    public function dashboard()
    {
        $partner = Auth::guard('partner')->user();
        
        return Inertia::render('Partner/Dashboard', [
            'partner' => $partner->load(['emiratesIdAttachment', 'visaAttachment', 'nocAttachment', 'attachments'])
        ]);
    }
}

