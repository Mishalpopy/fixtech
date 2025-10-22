<?php

namespace App\Repositories\Partner;

use App\Models\Partner;
use App\Models\PartnerAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PartnerRepository
{

    public function store($data, $isAdminCreating = true)
    {
        $partnerData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'company_name' => $data['company_name'] ?? null,
            'trade_license_no' => $data['trade_license_no'] ?? null,
            'status' => $data['status'] ?? true,
        ];

        // Set approval status based on who creates it
        if ($isAdminCreating) {
            // Admin creating partner - can be approved directly
            $partnerData['approval_status'] = $data['approval_status'] ?? 'pending';
            if ($partnerData['approval_status'] === 'approved') {
                $partnerData['approved_at'] = now();
                $partnerData['approved_by'] = Auth::id();
            }
        } else {
            // Self-registration - always pending
            $partnerData['approval_status'] = 'pending';
        }

        // Add password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $partnerData['password'] = Hash::make($data['password']);
        }

        $partner = Partner::create($partnerData);

        // Handle Emirates ID attachment (required)
        if (isset($data['emirates_id'])) {
            $this->storeAttachment($partner, $data['emirates_id'], 'emirates_id');
        }

        return $partner;
    }

    public function update($data, $partner)
    {
        $partnerData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'company_name' => $data['company_name'] ?? $partner->company_name,
            'trade_license_no' => $data['trade_license_no'] ?? $partner->trade_license_no,
            'status' => $data['status'] ?? $partner->status,
        ];

        // Update password only if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $partnerData['password'] = Hash::make($data['password']);
        }

        $partner->update($partnerData);

        return $partner;
    }

    public function storeAttachment($partner, $file, $type)
    {
        // Delete old attachment if exists
        $oldAttachment = PartnerAttachment::where('partner_id', $partner->id)
            ->where('type', $type)
            ->first();
        
        if ($oldAttachment) {
            $oldAttachment->deleteFile();
            $oldAttachment->delete();
        }

        // Store new file
        $fileName = time() . '_' . $type . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('partners/' . $partner->id . '/attachments', $fileName, 'public');

        // Create attachment record
        return PartnerAttachment::create([
            'partner_id' => $partner->id,
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    public function approve($partner, $userId)
    {
        $partner->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $userId,
            'rejection_reason' => null,
        ]);

        return $partner;
    }

    public function reject($partner, $reason, $userId)
    {
        $partner->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => $userId,
        ]);

        return $partner;
    }

    public function getAllPartners()
    {
        return Partner::with(['emiratesIdAttachment', 'approvedBy'])->get();
    }

    public function getPendingPartners()
    {
        return Partner::where('approval_status', 'pending')
            ->with(['emiratesIdAttachment'])
            ->get();
    }
}

