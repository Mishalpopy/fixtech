<?php

namespace App\Http\Controllers\Admin\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\SubService;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'tickets' => $tickets,
        ]);
    }

    /**
     * Show the form for creating a new complaint
     */
    public function create()
    {
        $customers = Customer::where('status', true)->get(['id', 'name', 'email']);
        $partners = Partner::where('status', true)->get(['id', 'name', 'email']);
        $services = Service::where('status', true)->get(['id', 'name']);

        return Inertia::render('Admin/Tickets/Create', [
            'customers' => $customers,
            'partners' => $partners,
            'services' => $services,
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
     * Store a newly created complaint
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
                'assigned_by' => Auth::id(),
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
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to create complaint: '.$e->getMessage()]);
        }

        return redirect()->route('admin:tickets.index')
            ->with('success', 'Complaint created successfully!');
    }

    /**
     * Display the specified complaint
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['customer', 'service', 'subService', 'assignedPartner', 'assignedBy', 'attachments', 'ticketItems.serviceItem']);
        $partners = Partner::where('status', true)->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'partners' => $partners,
        ]);
    }

    /**
     * Show the form for editing the specified complaint
     */
    public function edit(Ticket $ticket)
    {
        $ticket->load(['customer', 'service', 'subService', 'assignedPartner', 'assignedBy', 'attachments', 'ticketItems.serviceItem']);
        $customers = Customer::where('status', true)->get(['id', 'name', 'email']);
        $partners = Partner::where('status', true)->get(['id', 'name', 'email']);
        $services = Service::where('status', true)->get(['id', 'name']);
        $subServices = $ticket->service_id ? SubService::where('service_id', $ticket->service_id)->where('status', true)->get(['id', 'name', 'service_id']) : collect();
        $serviceItems = $ticket->sub_service_id ? ServiceItem::where('sub_service_id', $ticket->sub_service_id)->where('status', true)->get(['id', 'name', 'sub_service_id']) : collect();

        return Inertia::render('Admin/Tickets/Edit', [
            'ticket' => $ticket,
            'customers' => $customers,
            'partners' => $partners,
            'services' => $services,
            'subServices' => $subServices,
            'serviceItems' => $serviceItems,
        ]);
    }

    /**
     * Update the specified complaint
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
            'total_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:WALLET,COD',
            'category' => 'nullable|in:plumbing,electrical,hvac,appliance,general,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:open,assigned,in_progress,resolved,closed,cancelled',
            'assigned_partner_id' => 'nullable|exists:partners,id',
            'admin_notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

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
                'assigned_by' => Auth::id(),
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
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to update complaint: '.$e->getMessage()]);
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
        $filePath = storage_path('app/public/'.$attachment->file_path);

        if (! file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath, $attachment->original_name);
    }
}
