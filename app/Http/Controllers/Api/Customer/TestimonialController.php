<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\Ticket;
use App\Repositories\Testimonial\TestimonialRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestimonialController extends Controller
{
    protected $testimonial_repo;

    public function __construct(TestimonialRepository $testimonial_repo)
    {
        $this->testimonial_repo = $testimonial_repo;
    }

    /**
     * Get all testimonials for the authenticated customer
     */
    public function index(Request $request)
    {
        $customer = $request->user('sanctum');
        $testimonials = $this->testimonial_repo->getTestimonialsByCustomer($customer->id);

        return response()->json([
            'success' => true,
            'data' => $testimonials,
            'message' => 'Testimonials retrieved successfully'
        ]);
    }

    /**
     * Get approved testimonials (public endpoint)
     */
    public function getApproved()
    {
        $testimonials = $this->testimonial_repo->getApprovedTestimonials();

        return response()->json([
            'success' => true,
            'data' => $testimonials,
            'message' => 'Approved testimonials retrieved successfully'
        ]);
    }

    /**
     * Store a new testimonial
     */
    public function store(Request $request)
    {
        $customer = $request->user('sanctum');

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
            return response()->json([
                'success' => false,
                'message' => 'You must book a service before you can create a testimonial'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $testimonial = $this->testimonial_repo->store([
                'customer_id' => $customer->id,
                'title' => $request->title,
                'description' => $request->description,
                'photo' => $request->file('photo'),
                'video' => $request->file('video'),
            ]);

            $testimonial->load(['customer']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $testimonial,
                'message' => 'Testimonial created successfully. It will be visible after admin approval.'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create testimonial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a testimonial
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $customer = $request->user('sanctum');

        // Verify that the testimonial belongs to the customer
        if ($testimonial->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Only allow editing if status is pending
        if ($testimonial->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit pending testimonials'
            ], 422);
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
            $testimonial->load(['customer']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $testimonial,
                'message' => 'Testimonial updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update testimonial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a testimonial
     */
    public function destroy(Testimonial $testimonial)
    {
        $customer = $request->user('sanctum');

        // Verify that the testimonial belongs to the customer
        if ($testimonial->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $testimonial->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Testimonial deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete testimonial: ' . $e->getMessage()
            ], 500);
        }
    }
}
