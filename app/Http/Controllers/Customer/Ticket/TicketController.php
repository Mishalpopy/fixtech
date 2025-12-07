<?php

namespace App\Http\Controllers\Customer\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketItem;
use App\Models\Service;
use App\Models\SubService;
use App\Models\ServiceItem;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $services = Service::where('status', true)->get(['id', 'name']);

        return Inertia::render('Customer/Tickets/Create', [
            'services' => $services
        ]);
    }

    public function getSubServices(Request $request)
    {
        $serviceId = $request->get('service_id');
        $subServices = SubService::where('service_id', $serviceId)
            ->where('status', true)
            ->get(['id', 'name', 'service_id']);
        
        return response()->json($subServices);
    }

    public function getServiceItems(Request $request)
    {
        $subServiceId = $request->get('sub_service_id');
        $serviceItems = ServiceItem::where('sub_service_id', $subServiceId)
            ->where('status', true)
            ->get(['id', 'name', 'sub_service_id', 'price']);
        
        return response()->json($serviceItems);
    }

    /**
     * Store a newly created resource in storage.
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
            'category' => 'nullable|in:plumbing,electrical,hvac,appliance,general,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $customer = Auth::guard('customer')->user();

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
                'status' => 'open'
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

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create complaint: ' . $e->getMessage()]);
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

        $ticket->load('attachments', 'service', 'subService', 'ticketItems.serviceItem');

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

        $ticket->load('attachments', 'service', 'subService', 'ticketItems.serviceItem');
        $services = Service::where('status', true)->get(['id', 'name']);
        $subServices = $ticket->service_id ? SubService::where('service_id', $ticket->service_id)->where('status', true)->get(['id', 'name', 'service_id']) : collect();
        $serviceItems = $ticket->sub_service_id ? ServiceItem::where('sub_service_id', $ticket->sub_service_id)->where('status', true)->get(['id', 'name', 'sub_service_id']) : collect();

        return Inertia::render('Customer/Tickets/Edit', [
            'ticket' => $ticket,
            'services' => $services,
            'subServices' => $subServices,
            'serviceItems' => $serviceItems
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
            return back()->withErrors(['error' => 'Failed to update complaint: ' . $e->getMessage()]);
        }

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
