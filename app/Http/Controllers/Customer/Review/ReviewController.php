<?php

namespace App\Http\Controllers\Customer\Review;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\Service;
use App\Repositories\Review\ReviewRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class ReviewController extends Controller
{
    use Toast;
    protected $review_repo;

    public function __construct(ReviewRepository $review_repo)
    {
        $this->review_repo = $review_repo;
    }

    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $reviews = $this->review_repo->getReviewsByCustomer($customer->id);
        
        return Inertia::render('Customer/Reviews/Index', [
            'reviews' => $reviews
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
            $this->toast('error', "Error", "You must book a service before you can leave a review");
            return redirect()->route('customer:reviews.index');
        }

        // Get all services that the customer has booked
        $services = Service::whereHas('tickets', function($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })->get();

        return Inertia::render('Customer/Reviews/Create', [
            'services' => $services
        ]);
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        // Check if customer has booked this service (has tickets for this service)
        $hasBookedService = Ticket::where('customer_id', $customer->id)
            ->where('service_id', $request->service_id)
            ->exists();

        if (!$hasBookedService) {
            $this->toast('error', "Error", "You must book this service before you can leave a review");
            return back();
        }

        // Check if customer already reviewed this service
        $existingReview = Review::where('customer_id', $customer->id)
            ->where('service_id', $request->service_id)
            ->first();

        if ($existingReview) {
            $this->toast('error', "Error", "You have already reviewed this service");
            return back();
        }

        DB::beginTransaction();

        try {
            $this->review_repo->store([
                'customer_id' => $customer->id,
                'service_id' => $request->service_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'active',
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Review Successfully Created");
        return redirect()->route('customer:reviews.index');
    }

    public function edit(Review $review)
    {
        $customer = Auth::guard('customer')->user();
        
        // Verify that the review belongs to the customer
        if ($review->customer_id !== $customer->id) {
            abort(403);
        }

        $review->load(['service']);
        return Inertia::render('Customer/Reviews/Edit', [
            'review' => $review
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $customer = Auth::guard('customer')->user();
        
        // Verify that the review belongs to the customer
        if ($review->customer_id !== $customer->id) {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $this->review_repo->update($request->all(), $review);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Review Successfully Updated");
        return redirect()->route('customer:reviews.index');
    }

    public function destroy(Review $review)
    {
        $customer = Auth::guard('customer')->user();
        
        // Verify that the review belongs to the customer
        if ($review->customer_id !== $customer->id) {
            abort(403);
        }

        DB::beginTransaction();

        try {
            $review->delete();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Review Successfully Deleted");
        return redirect()->route('customer:reviews.index');
    }
}
