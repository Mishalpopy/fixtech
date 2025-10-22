<?php

namespace App\Http\Controllers\Partner\Profile;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PartnerProfileController extends Controller
{
    public function index()
    {
        /** @var Partner $partner */
        $partner = Auth::guard('partner')->user();
        
        return Inertia::render('Partner/Profile', [
            'partner' => $partner->load(['attachments'])
        ]);
    }

    public function update(Request $request)
    {
        /** @var Partner $partner */
        $partner = Auth::guard('partner')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:partners,email,' . $partner->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'company_name' => 'required|string|max:255',
            'trade_license_no' => 'required|string|max:100',
        ]);

        $partner->update($request->only([
            'name', 'email', 'phone', 'address', 'company_name', 'trade_license_no'
        ]));

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'Profile updated successfully!'
        ]);
    }

    public function uploadDocument(Request $request, $type)
    {
        /** @var Partner $partner */
        $partner = Auth::guard('partner')->user();
        
        $request->validate([
            $type => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240' // 10MB max
        ]);

        $file = $request->file($type);
        
        // Delete existing attachment if any
        $existingAttachment = PartnerAttachment::where('partner_id', $partner->id)
            ->where('type', $type)
            ->first();
            
        if ($existingAttachment) {
            $existingAttachment->deleteFile();
            $existingAttachment->delete();
        }

        // Store the file
        $filePath = $file->store('partner-documents/' . $partner->id, 'public');
        
        // Create attachment record
        PartnerAttachment::create([
            'partner_id' => $partner->id,
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully!'
        ]);
    }

    public function deleteDocument($type)
    {
        /** @var Partner $partner */
        $partner = Auth::guard('partner')->user();
        
        $attachment = PartnerAttachment::where('partner_id', $partner->id)
            ->where('type', $type)
            ->first();
            
        if ($attachment) {
            $attachment->deleteFile();
            $attachment->delete();
            
            return redirect()->back()->with('toast', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Document deleted successfully!'
            ]);
        }
        
        return redirect()->back()->with('toast', [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Document not found!'
        ]);
    }
}
