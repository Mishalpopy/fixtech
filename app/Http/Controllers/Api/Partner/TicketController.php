<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * Display a listing of assigned complaints.
     */
    public function index(Request $request)
    {
        $partner = $request->user('sanctum');
        
        $query = Ticket::with(['customer', 'attachments'])
            ->where('assigned_partner_id', $partner->id);

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
            'message' => 'Assigned complaints retrieved successfully'
        ]);
    }

    /**
     * Display the specified complaint.
     */
    public function show(Request $request, Ticket $ticket)
    {
        // Ensure the complaint is assigned to this partner
        $partner = $request->user('sanctum');
        if ($ticket->assigned_partner_id !== $partner->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this complaint.'
            ], 403);
        }

        $ticket->load(['customer', 'attachments', 'assignedPartner']);

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint retrieved successfully'
        ]);
    }

    /**
     * Update the status of the complaint.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        // Ensure the complaint is assigned to this partner
        $partner = $request->user('sanctum');
        if ($ticket->assigned_partner_id !== $partner->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this complaint.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:open,assigned,in_progress,resolved,closed,cancelled',
            'partner_notes' => 'nullable|string|max:1000'
        ]);

        $ticket->update([
            'status' => $request->status,
            'partner_notes' => $request->partner_notes,
            'resolved_at' => $request->status === 'resolved' ? now() : null
        ]);

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint status updated successfully'
        ]);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        // Ensure the complaint is assigned to this partner
        $partner = $request->user('sanctum');
        if ($ticket->assigned_partner_id !== $partner->id) {
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