<?php

namespace App\Repositories\ServiceItem;

use App\Models\ServiceItem;
use Illuminate\Support\Facades\Storage;

class ServiceItemRepository
{

    public function store($data)
    {
        $serviceItemData = [
            'sub_service_id' => $data['sub_service_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'status' => $data['status'] ?? true,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            $serviceItemData['image'] = $this->uploadImage($data['image']);
        }

        $serviceItem = ServiceItem::create($serviceItemData);

        return $serviceItem;
    }

    public function update($data, $serviceItem)
    {
        $serviceItemData = [
            'sub_service_id' => $data['sub_service_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? $serviceItem->price,
            'status' => $data['status'] ?? $serviceItem->status,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            // Delete old image if exists
            if ($serviceItem->image) {
                Storage::disk('public')->delete($serviceItem->image);
            }
            $serviceItemData['image'] = $this->uploadImage($data['image']);
        }

        $serviceItem->update($serviceItemData);

        return $serviceItem;
    }

    public function getAllServiceItems($subServiceId = null)
    {
        $query = ServiceItem::with('subService.service');
        
        if ($subServiceId) {
            $query->where('sub_service_id', $subServiceId);
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
        $path = $image->store('service_items', 'public');
        return $path;
    }
}

