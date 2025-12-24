<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
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
     * Display a listing of the customer's complaints.
     */
    public function index(Request $request)
    {
        $customer = $request->user('sanctum');

        $query = Ticket::with(['attachments', 'service', 'subService', 'ticketItems.serviceItem'])
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
            'message' => 'Complaints retrieved successfully',
        ]);
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request)
    {
        $request->validate([
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
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $customer = $request->user('sanctum');

        DB::beginTransaction();

        try {
            $ticket = Ticket::create([
                'customer_id' => $customer->id,
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
                'status' => 'open',
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

            $ticket->load('attachments', 'service', 'subService', 'ticketItems.serviceItem');
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
            'message' => 'Complaint submitted successfully! Complaint Number: '.$ticket->ticket_number,
        ], 201);
    }

    /**
     * Display the specified complaint.
     */
    public function show(Request $request, Ticket $ticket)
    {
        // Ensure the complaint belongs to this customer
        $customer = $request->user('sanctum');
        if ($ticket->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this complaint.',
            ], 403);
        }

        $ticket->load(['attachments', 'assignedPartner', 'service', 'subService', 'ticketItems.serviceItem']);

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
        // Ensure the complaint belongs to this customer
        $customer = $request->user('sanctum');
        if ($ticket->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this complaint.',
            ], 403);
        }

        // Only allow editing if status is open
        if ($ticket->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit complaints that are open.',
            ], 422);
        }

        $request->validate([
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
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $ticket->update([
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

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update complaint: '.$e->getMessage(),
            ], 500);
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

        $ticket->load('attachments', 'service', 'subService', 'ticketItems.serviceItem');

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
        // Ensure the complaint belongs to this customer
        $customer = $request->user('sanctum');
        if ($ticket->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this complaint.',
            ], 403);
        }

        // Only allow deleting if status is open
        if ($ticket->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete complaints that are open.',
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
            'message' => 'Complaint deleted successfully',
        ]);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        // Ensure the complaint belongs to this customer
        $customer = $request->user('sanctum');
        if ($ticket->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to download this attachment.',
            ], 403);
        }

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
}
