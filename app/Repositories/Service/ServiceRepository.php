<?php

namespace App\Repositories\Service;

use App\Models\Service;
use Illuminate\Support\Facades\Storage;

class ServiceRepository
{

    public function store($data)
    {
        $serviceData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? true,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            $serviceData['image'] = $this->uploadImage($data['image']);
        }

        $service = Service::create($serviceData);

        return $service;
    }

    public function update($data, $service)
    {
        $serviceData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? $service->status,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            // Delete old image if exists
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $serviceData['image'] = $this->uploadImage($data['image']);
        }

        $service->update($serviceData);

        return $service;
    }

    public function getAllServices()
    {
        return Service::orderBy('created_at', 'desc')->get();
    }

    private function uploadImage($image)
    {
        if (is_string($image)) {
            // If it's already a path, return it
            return $image;
        }

        // Upload new image
        $path = $image->store('services', 'public');
        return $path;
    }
}

