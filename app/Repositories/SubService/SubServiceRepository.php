<?php

namespace App\Repositories\SubService;

use App\Models\SubService;
use Illuminate\Support\Facades\Storage;

class SubServiceRepository
{

    public function store($data)
    {
        $subServiceData = [
            'service_id' => $data['service_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? true,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            $subServiceData['image'] = $this->uploadImage($data['image']);
        }

        $subService = SubService::create($subServiceData);

        return $subService;
    }

    public function update($data, $subService)
    {
        $subServiceData = [
            'service_id' => $data['service_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? $subService->status,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            // Delete old image if exists
            if ($subService->image) {
                Storage::disk('public')->delete($subService->image);
            }
            $subServiceData['image'] = $this->uploadImage($data['image']);
        }

        $subService->update($subServiceData);

        return $subService;
    }

    public function getAllSubServices($serviceId = null)
    {
        $query = SubService::with('service');
        
        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    private function uploadImage($image)
    {
        if (is_string($image)) {
            // If it's already a path, return it
            return $image;
        }

        // Upload new image
        $path = $image->store('sub_services', 'public');
        return $path;
    }
}

