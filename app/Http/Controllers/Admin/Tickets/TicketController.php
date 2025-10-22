<?php

namespace App\Http\Controllers\Admin\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Display a listing of all complaints
     */
    public function index()
    {
        $tickets = Ticket::with(['customer', 'assignedPartner', 'assignedBy', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets
        ]);
    }

    /**
     * Show the form for creating a new complaint
     */
    public function create()
    {
        $customers = Customer::where('status', true)->get(['id', 'name', 'email']);
        $partners = Partner::where('status', true)->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Tickets/Create', [
            'customers' => $customers,
            'partners' => $partners
        ]);
    }

    /**
     * Store a newly created complaint
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category' => 'required|in:plumbing,electrical,hvac,appliance,general,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_partner_id' => 'nullable|exists:partners,id',
            'admin_notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $ticket = Ticket::create([
            'customer_id' => $request->customer_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => $request->assigned_partner_id ? 'assigned' : 'open',
            'admin_notes' => $request->admin_notes,
            'assigned_partner_id' => $request->assigned_partner_id,
            'assigned_by' => Auth::id(),
            'assigned_at' => $request->assigned_partner_id ? now() : null,
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

        return redirect()->route('admin:tickets.index')
            ->with('success', 'Complaint created successfully!');
    }

    /**
     * Display the specified complaint
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['customer', 'assignedPartner', 'assignedBy', 'attachments']);
        $partners = Partner::where('status', true)->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'partners' => $partners
        ]);
    }

    /**
     * Show the form for editing the specified complaint
     */
    public function edit(Ticket $ticket)
    {
        $ticket->load(['customer', 'assignedPartner', 'assignedBy', 'attachments']);
        $customers = Customer::where('status', true)->get(['id', 'name', 'email']);
        $partners = Partner::where('status', true)->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Tickets/Edit', [
            'ticket' => $ticket,
            'customers' => $customers,
            'partners' => $partners
        ]);
    }

    /**
     * Update the specified complaint
     */
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category' => 'required|in:plumbing,electrical,hvac,appliance,general,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,assigned,in_progress,resolved,closed,cancelled',
            'assigned_partner_id' => 'nullable|exists:partners,id',
            'admin_notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $ticket->update([
            'customer_id' => $request->customer_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'assigned_partner_id' => $request->assigned_partner_id,
            'assigned_by' => Auth::id(),
            'assigned_at' => $request->assigned_partner_id ? now() : null,
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

        return redirect()->route('admin:tickets.show', $ticket)
            ->with('success', 'Complaint updated successfully!');
    }

    /**
     * Assign complaint to partner
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_partner_id' => 'required|exists:partners,id',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $ticket->update([
            'assigned_partner_id' => $request->assigned_partner_id,
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
            'status' => 'assigned',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin:tickets.show', $ticket)
            ->with('success', 'Complaint assigned to partner successfully!');
    }

    /**
     * Update complaint status
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,assigned,in_progress,resolved,closed,cancelled',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $ticket->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'resolved_at' => $request->status === 'resolved' ? now() : null,
        ]);

        return redirect()->route('admin:tickets.show', $ticket)
            ->with('success', 'Complaint status updated successfully!');
    }

    /**
     * Remove the specified complaint
     */
    public function destroy(Ticket $ticket)
    {
        // Delete associated files
        foreach ($ticket->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $ticket->delete();

        return redirect()->route('admin:tickets.index')
            ->with('success', 'Complaint deleted successfully!');
    }

    /**
     * Download ticket attachment
     */
    public function downloadAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        $filePath = storage_path('app/public/' . $attachment->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath, $attachment->original_name);
    }
}
