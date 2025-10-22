<?php

namespace App\Http\Controllers\Partner\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Partner\PartnerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class PartnerRegistrationController extends Controller
{
    protected $partner_repo;

    public function __construct(PartnerRepository $partner_repo)
    {
        $this->partner_repo = $partner_repo;
    }

    public function create()
    {
        return Inertia::render('Partner/Auth/Register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:partners,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'company_name' => 'nullable|string|max:255',
            'trade_license_no' => 'nullable|string|max:255',
            'emirates_id' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        DB::beginTransaction();

        try {
            // Create partner with pending status (self-registration)
            $partner = $this->partner_repo->store($request->all(), false);
            
            // Handle Emirates ID file upload
            if ($request->hasFile('emirates_id')) {
                $this->partner_repo->storeAttachment($partner, $request->file('emirates_id'), 'emirates_id');
            }
        } catch (Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed. Please try again.']);
        }

        DB::commit();

        return redirect()->route('partner:login')
            ->with('status', 'Registration successful! Your account is pending approval.');
    }
}

