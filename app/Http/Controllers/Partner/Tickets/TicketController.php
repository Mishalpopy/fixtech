<?php

namespace App\Http\Controllers\Partner\Tickets;

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
     * Display a listing of assigned complaints.
     */
    public function index(Request $request)
    {
        $partner = Auth::guard('partner')->user();
        
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

        return inertia('Partner/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'category', 'search'])
        ]);
    }

    /**
     * Display the specified complaint.
     */
    public function show(Ticket $ticket)
    {
        // Ensure the complaint is assigned to this partner
        if ($ticket->assigned_partner_id !== Auth::guard('partner')->id()) {
            abort(403, 'You are not authorized to view this complaint.');
        }

        $ticket->load(['customer', 'attachments', 'assignedPartner']);

        return inertia('Partner/Tickets/Show', [
            'ticket' => $ticket
        ]);
    }

    /**
     * Update the status of the complaint.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        // Ensure the complaint is assigned to this partner
        if ($ticket->assigned_partner_id !== Auth::guard('partner')->id()) {
            abort(403, 'You are not authorized to update this complaint.');
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

        return redirect()->route('partner:tickets.show', $ticket->id)
            ->with('success', 'Complaint status updated successfully!');
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        // Ensure the complaint is assigned to this partner
        if ($ticket->assigned_partner_id !== Auth::guard('partner')->id()) {
            abort(403, 'You are not authorized to download this attachment.');
        }

        // Ensure the attachment belongs to this ticket
        if ($attachment->ticket_id !== $ticket->id) {
            abort(404, 'Attachment not found.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }
}