<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Display a listing of all complaints.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['customer', 'service', 'subService', 'attachments', 'assignedPartner', 'assignedBy', 'ticketItems.serviceItem']);

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

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('assigned_partner_id')) {
            $query->where('assigned_partner_id', $request->assigned_partner_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'message' => 'Complaints retrieved successfully',
        ]);
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'required|exists:services,id',
            'sub_service_id' => 'required|exists:sub_services,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'items' => 'nullable|array',
            'items.*.service_item_id' => 'required|exists:service_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'is_urgent' => 'nullable|boolean',
            'scheduled_date_time' => 'nullable|date',
            'address' => 'required|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:WALLET,COD',
            'category' => 'nullable|in:plumbing,electrical,hvac,appliance,general,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_partner_id' => 'nullable|exists:partners,id',
            'admin_notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $admin = $request->user('sanctum');

        DB::beginTransaction();

        try {
            $ticket = Ticket::create([
                'customer_id' => $request->customer_id,
                'service_id' => $request->service_id,
                'sub_service_id' => $request->sub_service_id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'priority' => $request->is_urgent ? 'urgent' : $request->priority,
                'is_urgent' => $request->is_urgent ?? false,
                'scheduled_date_time' => $request->scheduled_date_time ? date('Y-m-d H:i:s', strtotime($request->scheduled_date_time)) : null,
                'address' => $request->address,
                'location' => $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'status' => $request->assigned_partner_id ? 'assigned' : 'open',
                'admin_notes' => $request->admin_notes,
                'assigned_partner_id' => $request->assigned_partner_id,
                'assigned_by' => $admin->id,
                'assigned_at' => $request->assigned_partner_id ? now() : null,
            ]);

            // Handle ticket items
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    TicketItem::create([
                        'ticket_id' => $ticket->id,
                        'service_item_id' => $item['service_item_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $fileName = time().'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
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

            DB::commit();

            $ticket->load(['customer', 'service', 'subService', 'attachments', 'assignedPartner', 'assignedBy', 'ticketItems.serviceItem']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create complaint: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint created successfully',
        ], 201);
    }

    /**
     * Display the specified complaint.
     */
    public function show(Request $request, Ticket $ticket)
    {
        $ticket->load(['customer', 'service', 'subService', 'attachments', 'assignedPartner', 'assignedBy', 'ticketItems.serviceItem']);

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint retrieved successfully',
        ]);
    }

    /**
     * Update the specified complaint.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'required|exists:services,id',
            'sub_service_id' => 'required|exists:sub_services,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'items' => 'nullable|array',
            'items.*.service_item_id' => 'required|exists:service_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'is_urgent' => 'nullable|boolean',
            'scheduled_date_time' => 'nullable|date',
            'address' => 'required|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'category' => 'nullable|in:plumbing,electrical,hvac,appliance,general,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,assigned,in_progress,resolved,closed,cancelled',
            'assigned_partner_id' => 'nullable|exists:partners,id',
            'admin_notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $admin = $request->user('sanctum');

        DB::beginTransaction();

        try {
            $ticket->update([
                'customer_id' => $request->customer_id,
                'service_id' => $request->service_id,
                'sub_service_id' => $request->sub_service_id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'priority' => $request->is_urgent ? 'urgent' : $request->priority,
                'is_urgent' => $request->is_urgent ?? false,
                'scheduled_date_time' => $request->scheduled_date_time ? date('Y-m-d H:i:s', strtotime($request->scheduled_date_time)) : null,
                'address' => $request->address,
                'location' => $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'assigned_partner_id' => $request->assigned_partner_id,
                'assigned_by' => $admin->id,
                'assigned_at' => $request->assigned_partner_id ? now() : null,
            ]);

            // Delete existing ticket items
            TicketItem::where('ticket_id', $ticket->id)->delete();

            // Handle ticket items
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    TicketItem::create([
                        'ticket_id' => $ticket->id,
                        'service_item_id' => $item['service_item_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            // Handle new file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $fileName = time().'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
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

            DB::commit();

            $ticket->load(['customer', 'service', 'subService', 'attachments', 'assignedPartner', 'assignedBy', 'ticketItems.serviceItem']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update complaint: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint updated successfully',
        ]);
    }

    /**
     * Remove the specified complaint.
     */
    public function destroy(Request $request, Ticket $ticket)
    {
        // Delete associated files
        foreach ($ticket->attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Complaint deleted successfully',
        ]);
    }

    /**
     * Assign complaint to partner.
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_partner_id' => 'required|exists:partners,id',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $admin = $request->user('sanctum');

        $ticket->update([
            'assigned_partner_id' => $request->assigned_partner_id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
            'admin_notes' => $request->admin_notes,
        ]);

        $ticket->load(['customer', 'service', 'subService', 'attachments', 'assignedPartner', 'assignedBy', 'ticketItems.serviceItem']);

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint assigned successfully',
        ]);
    }

    /**
     * Update complaint status.
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

        $ticket->load(['customer', 'service', 'subService', 'attachments', 'assignedPartner', 'assignedBy', 'ticketItems.serviceItem']);

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'message' => 'Complaint status updated successfully',
        ]);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        // Ensure the attachment belongs to this ticket
        if ($attachment->ticket_id !== $ticket->id) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found.',
            ], 404);
        }

        if (! Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.',
            ], 404);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    /**
     * Get customers for dropdown.
     */
    public function getCustomers(Request $request)
    {
        $customers = Customer::select('id', 'name', 'email')->get();

        return response()->json([
            'success' => true,
            'data' => $customers,
            'message' => 'Customers retrieved successfully',
        ]);
    }

    /**
     * Get partners for dropdown.
     */
    public function getPartners(Request $request)
    {
        $partners = Partner::select('id', 'name', 'email')->get();

        return response()->json([
            'success' => true,
            'data' => $partners,
            'message' => 'Partners retrieved successfully',
        ]);
    }
}
