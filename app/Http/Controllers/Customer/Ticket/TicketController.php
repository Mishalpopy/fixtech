<?php

namespace App\Http\Controllers\Customer\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $tickets = $customer->tickets()
            ->with('attachments')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Customer/Tickets/Index', [
            'tickets' => $tickets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Customer/Tickets/Create');
    }

    /**
     * Store a newly created resource in storage.
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

        return redirect()->route('customer:tickets.index')
            ->with('success', 'Complaint submitted successfully! Complaint Number: ' . $ticket->ticket_number);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $customer = Auth::guard('customer')->user();
        
        // Ensure the ticket belongs to the authenticated customer
        if ($ticket->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to ticket.');
        }

        $ticket->load('attachments');

        return Inertia::render('Customer/Tickets/Show', [
            'ticket' => $ticket
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        $customer = Auth::guard('customer')->user();
        
        // Ensure the ticket belongs to the authenticated customer
        if ($ticket->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to ticket.');
        }

        // Only allow editing if complaint is open
        if ($ticket->status !== 'open') {
            return redirect()->route('customer:tickets.show', $ticket)
                ->with('error', 'Cannot edit complaint that is not in open status.');
        }

        $ticket->load('attachments');

        return Inertia::render('Customer/Tickets/Edit', [
            'ticket' => $ticket
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $customer = Auth::guard('customer')->user();
        
        // Ensure the ticket belongs to the authenticated customer
        if ($ticket->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to ticket.');
        }

        // Only allow updating if complaint is open
        if ($ticket->status !== 'open') {
            return redirect()->route('customer:tickets.show', $ticket)
                ->with('error', 'Cannot update complaint that is not in open status.');
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

        return redirect()->route('customer:tickets.show', $ticket)
            ->with('success', 'Complaint updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        $customer = Auth::guard('customer')->user();
        
        // Ensure the ticket belongs to the authenticated customer
        if ($ticket->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to ticket.');
        }

        // Only allow deletion if complaint is open
        if ($ticket->status !== 'open') {
            return redirect()->route('customer:tickets.index')
                ->with('error', 'Cannot delete complaint that is not in open status.');
        }

        // Delete associated files
        foreach ($ticket->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $ticket->delete();

        return redirect()->route('customer:tickets.index')
            ->with('success', 'Complaint deleted successfully!');
    }

    /**
     * Download ticket attachment
     */
    public function downloadAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        $customer = Auth::guard('customer')->user();
        
        // Ensure the ticket belongs to the authenticated customer
        if ($ticket->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to ticket.');
        }

        // Ensure the attachment belongs to the ticket
        if ($attachment->ticket_id !== $ticket->id) {
            abort(404, 'Attachment not found.');
        }

        $filePath = storage_path('app/public/' . $attachment->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath, $attachment->original_name);
    }
}
