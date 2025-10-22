<?php

namespace App\Http\Controllers\Admin\Partners;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Repositories\Partner\PartnerRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class PartnerController extends Controller
{
    use Toast;
    protected $partner_repo;

    public function __construct(PartnerRepository $partner_repo)
    {
        $this->partner_repo = $partner_repo;
    }

    public function index()
    {
        return Inertia::render('Admin/Partners/Index', [
            'partners' => $this->partner_repo->getAllPartners()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Partners/Create');
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
            'status' => 'nullable|boolean',
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'emirates_id' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        DB::beginTransaction();

        try {
            $partner = $this->partner_repo->store($request->all(), true);
            
            // Handle Emirates ID file upload
            if ($request->hasFile('emirates_id')) {
                $this->partner_repo->storeAttachment($partner, $request->file('emirates_id'), 'emirates_id');
            }
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Partner Successfully Created");
        return redirect()->route('admin:partners.index');
    }

    public function edit(Partner $partner)
    {
        return Inertia::render('Admin/Partners/Edit', [
            'partner' => $partner->load(['emiratesIdAttachment', 'visaAttachment', 'nocAttachment'])
        ]);
    }

    public function update(Request $request, Partner $partner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:partners,email,' . $partner->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'company_name' => 'nullable|string|max:255',
            'trade_license_no' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'emirates_id' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'visa' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'noc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $partner = $this->partner_repo->update($request->all(), $partner);
            
            // Handle file uploads
            if ($request->hasFile('emirates_id')) {
                $this->partner_repo->storeAttachment($partner, $request->file('emirates_id'), 'emirates_id');
            }
            if ($request->hasFile('visa')) {
                $this->partner_repo->storeAttachment($partner, $request->file('visa'), 'visa');
            }
            if ($request->hasFile('noc')) {
                $this->partner_repo->storeAttachment($partner, $request->file('noc'), 'noc');
            }
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Partner Successfully Updated");
        return redirect()->route('admin:partners.index');
    }

    public function show(Partner $partner)
    {
        return Inertia::render('Admin/Partners/Show', [
            'partner' => $partner->load(['emiratesIdAttachment', 'visaAttachment', 'nocAttachment', 'approvedBy'])
        ]);
    }

    public function destroy(Partner $partner)
    {
        try {
            // Delete all attachments
            foreach ($partner->attachments as $attachment) {
                $attachment->deleteFile();
                $attachment->delete();
            }
            
            $partner->delete();
            $this->toast('success', "Success", "Partner Successfully Deleted");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function changeStatus(Partner $partner)
    {
        try {
            $partner->status = !$partner->status;
            $partner->save();
            $this->toast('success', "Success", "Partner Status Updated");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function approve(Partner $partner)
    {
        try {
            $this->partner_repo->approve($partner, Auth::id());
            $this->toast('success', "Success", "Partner Approved Successfully");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function reject(Request $request, Partner $partner)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $this->partner_repo->reject($partner, $request->rejection_reason, Auth::id());
            $this->toast('success', "Success", "Partner Rejected");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function pendingApprovals()
    {
        return Inertia::render('Admin/Partners/PendingApprovals', [
            'partners' => $this->partner_repo->getPendingPartners()
        ]);
    }
}

