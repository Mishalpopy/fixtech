<?php

namespace App\Http\Controllers\Customer\Testimonial;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\Ticket;
use App\Repositories\Testimonial\TestimonialRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class TestimonialController extends Controller
{
    use Toast;
    protected $testimonial_repo;

    public function __construct(TestimonialRepository $testimonial_repo)
    {
        $this->testimonial_repo = $testimonial_repo;
    }

    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $testimonials = $this->testimonial_repo->getTestimonialsByCustomer($customer->id);
        
        return Inertia::render('Customer/Testimonials/Index', [
            'testimonials' => $testimonials
        ]);
    }

    public function create()
    {
        $customer = Auth::guard('customer')->user();
        
        // Check if customer has booked any service (has tickets)
        $hasBookedService = Ticket::where('customer_id', $customer->id)
            ->whereNotNull('service_id')
            ->exists();

        if (!$hasBookedService) {
            $this->toast('error', "Error", "You must book a service before you can create a testimonial");
            return redirect()->route('customer:testimonials.index');
        }

        return Inertia::render('Customer/Testimonials/Create');
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
        ]);

        // Check if customer has booked any service (has tickets)
        $hasBookedService = Ticket::where('customer_id', $customer->id)
            ->whereNotNull('service_id')
            ->exists();

        if (!$hasBookedService) {
            $this->toast('error', "Error", "You must book a service before you can create a testimonial");
            return back();
        }

        DB::beginTransaction();

        try {
            $this->testimonial_repo->store([
                'customer_id' => $customer->id,
                'title' => $request->title,
                'description' => $request->description,
                'photo' => $request->file('photo'),
                'video' => $request->file('video'),
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Testimonial Successfully Created. It will be visible after admin approval.");
        return redirect()->route('customer:testimonials.index');
    }

    public function edit(Testimonial $testimonial)
    {
        $customer = Auth::guard('customer')->user();
        
        // Verify that the testimonial belongs to the customer
        if ($testimonial->customer_id !== $customer->id) {
            abort(403);
        }

        // Only allow editing if status is pending
        if ($testimonial->status !== 'pending') {
            $this->toast('error', "Error", "You can only edit pending testimonials");
            return redirect()->route('customer:testimonials.index');
        }

        return Inertia::render('Customer/Testimonials/Edit', [
            'testimonial' => $testimonial
        ]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $customer = Auth::guard('customer')->user();
        
        // Verify that the testimonial belongs to the customer
        if ($testimonial->customer_id !== $customer->id) {
            abort(403);
        }

        // Only allow editing if status is pending
        if ($testimonial->status !== 'pending') {
            $this->toast('error', "Error", "You can only edit pending testimonials");
            return redirect()->route('customer:testimonials.index');
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $this->testimonial_repo->update($request->all(), $testimonial);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Testimonial Successfully Updated");
        return redirect()->route('customer:testimonials.index');
    }

    public function destroy(Testimonial $testimonial)
    {
        $customer = Auth::guard('customer')->user();
        
        // Verify that the testimonial belongs to the customer
        if ($testimonial->customer_id !== $customer->id) {
            abort(403);
        }

        DB::beginTransaction();

        try {
            $testimonial->delete();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Testimonial Successfully Deleted");
        return redirect()->route('customer:testimonials.index');
    }
}
