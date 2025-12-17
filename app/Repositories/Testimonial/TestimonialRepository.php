<?php

namespace App\Repositories\Testimonial;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;

class TestimonialRepository
{
    public function store($data)
    {
        $testimonialData = [
            'customer_id' => $data['customer_id'],
            'title' => $data['title'] ?? null,
            'description' => $data['description'],
            'status' => 'pending', // Always starts as pending
        ];

        // Handle photo upload
        if (isset($data['photo']) && $data['photo']) {
            $testimonialData['photo'] = $this->uploadPhoto($data['photo']);
        }

        // Handle video upload
        if (isset($data['video']) && $data['video']) {
            $testimonialData['video'] = $this->uploadVideo($data['video']);
        }

        return Testimonial::create($testimonialData);
    }

    public function update($data, $testimonial)
    {
        $testimonialData = [
            'title' => $data['title'] ?? $testimonial->title,
            'description' => $data['description'] ?? $testimonial->description,
        ];

        // Handle photo upload
        if (isset($data['photo']) && $data['photo']) {
            // Delete old photo if exists
            if ($testimonial->photo) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $testimonialData['photo'] = $this->uploadPhoto($data['photo']);
        }

        // Handle video upload
        if (isset($data['video']) && $data['video']) {
            // Delete old video if exists
            if ($testimonial->video) {
                Storage::disk('public')->delete($testimonial->video);
            }
            $testimonialData['video'] = $this->uploadVideo($data['video']);
        }

        $testimonial->update($testimonialData);

        return $testimonial;
    }

    public function approve($testimonial, $adminId)
    {
        $testimonial->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return $testimonial;
    }

    public function reject($testimonial, $adminId)
    {
        $testimonial->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return $testimonial;
    }

    public function getAllTestimonials()
    {
        return Testimonial::with(['customer', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getApprovedTestimonials()
    {
        return Testimonial::with(['customer', 'approvedBy'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingTestimonials()
    {
        return Testimonial::with(['customer', 'approvedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTestimonialsByCustomer($customerId)
    {
        return Testimonial::with(['customer', 'approvedBy'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function uploadPhoto($photo)
    {
        if (is_string($photo)) {
            // If it's already a path, return it
            return $photo;
        }

        // Upload new photo
        $path = $photo->store('testimonials/photos', 'public');
        return $path;
    }

    private function uploadVideo($video)
    {
        if (is_string($video)) {
            // If it's already a path, return it
            return $video;
        }

        // Upload new video
        $path = $video->store('testimonials/videos', 'public');
        return $path;
    }
}

