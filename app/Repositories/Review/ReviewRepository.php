<?php

namespace App\Repositories\Review;

use App\Models\Review;

class ReviewRepository
{
    public function store($data)
    {
        return Review::create([
            'customer_id' => $data['customer_id'],
            'service_id' => $data['service_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'status' => $data['status'] ?? 'active',
        ]);
    }

    public function update($data, $review)
    {
        $review->update([
            'rating' => $data['rating'] ?? $review->rating,
            'comment' => $data['comment'] ?? $review->comment,
            'status' => $data['status'] ?? $review->status,
        ]);

        return $review;
    }

    public function getAllReviews()
    {
        return Review::with(['customer', 'service'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getReviewsByService($serviceId)
    {
        return Review::with(['customer', 'service'])
            ->where('service_id', $serviceId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getReviewsByCustomer($customerId)
    {
        return Review::with(['customer', 'service'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

