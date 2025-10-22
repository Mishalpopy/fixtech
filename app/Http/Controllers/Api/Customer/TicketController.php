<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Display a listing of the customer's complaints.
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        $query = Ticket::with(['attachments'])
            ->where('customer_id', $customer->id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'message' => 'Complaints retrieved successfully'
        ]);
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category' => 'required|in:plumbing,electrical,hvac,appliance,general,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $customer = Auth::guard('customer')->user();

        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open'
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('ticket-attachments', $fileName, 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'original_name' => $originalName,
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $ticket->load('attachments');

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint submitted successfully! Complaint Number: ' . $ticket->ticket_number
        ], 201);
    }

    /**
     * Display the specified complaint.
     */
    public function show(Ticket $ticket)
    {
        // Ensure the complaint belongs to this customer
        if ($ticket->customer_id !== Auth::guard('customer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this complaint.'
            ], 403);
        }

        $ticket->load(['attachments', 'assignedPartner']);

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint retrieved successfully'
        ]);
    }

    /**
     * Update the specified complaint.
     */
    public function update(Request $request, Ticket $ticket)
    {
        // Ensure the complaint belongs to this customer
        if ($ticket->customer_id !== Auth::guard('customer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this complaint.'
            ], 403);
        }

        // Only allow editing if status is open
        if ($ticket->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit complaints that are open.'
            ], 422);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category' => 'required|in:plumbing,electrical,hvac,appliance,general,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $ticket->update([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
        ]);

        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('ticket-attachments', $fileName, 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'original_name' => $originalName,
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $ticket->load('attachments');

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint updated successfully'
        ]);
    }

    /**
     * Remove the specified complaint.
     */
    public function destroy(Ticket $ticket)
    {
        // Ensure the complaint belongs to this customer
        if ($ticket->customer_id !== Auth::guard('customer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this complaint.'
            ], 403);
        }

        // Only allow deleting if status is open
        if ($ticket->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete complaints that are open.'
            ], 422);
        }

        // Delete associated files
        foreach ($ticket->attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Complaint deleted successfully'
        ]);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        // Ensure the complaint belongs to this customer
        if ($ticket->customer_id !== Auth::guard('customer')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to download this attachment.'
            ], 403);
        }

        // Ensure the attachment belongs to this ticket
        if ($attachment->ticket_id !== $ticket->id) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found.'
            ], 404);
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.'
            ], 404);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }
}