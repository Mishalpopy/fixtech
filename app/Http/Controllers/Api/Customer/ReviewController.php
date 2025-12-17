<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Ticket;
use App\Repositories\Review\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    protected $review_repo;

    public function __construct(ReviewRepository $review_repo)
    {
        $this->review_repo = $review_repo;
    }

    /**
     * Get all reviews for the authenticated customer
     */
    public function index(Request $request)
    {
        $customer = $request->user('sanctum');
        $reviews = $this->review_repo->getReviewsByCustomer($customer->id);

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'message' => 'Reviews retrieved successfully'
        ]);
    }

    /**
     * Get reviews for a specific service
     */
    public function getServiceReviews($serviceId)
    {
        $reviews = $this->review_repo->getReviewsByService($serviceId);

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'message' => 'Service reviews retrieved successfully'
        ]);
    }

    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $customer = $request->user('sanctum');

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
            return response()->json([
                'success' => false,
                'message' => 'You must book this service before you can leave a review'
            ], 422);
        }

        // Check if customer already reviewed this service
        $existingReview = Review::where('customer_id', $customer->id)
            ->where('service_id', $request->service_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this service'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $review = $this->review_repo->store([
                'customer_id' => $customer->id,
                'service_id' => $request->service_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'active',
            ]);

            $review->load(['customer', 'service']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $review,
                'message' => 'Review created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, Review $review)
    {
        $customer = $request->user('sanctum');

        // Verify that the review belongs to the customer
        if ($review->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $this->review_repo->update($request->all(), $review);
            $review->load(['customer', 'service']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $review,
                'message' => 'Review updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy(Request $request, Review $review)
    {
        $customer = $request->user('sanctum');

        // Verify that the review belongs to the customer
        if ($review->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $review->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review: ' . $e->getMessage()
            ], 500);
        }
    }
}
