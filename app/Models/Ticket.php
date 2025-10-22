<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'admin_notes',
        'partner_notes',
        'assigned_partner_id',
        'assigned_by',
        'assigned_at',
        'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_created_at',
        'formatted_resolved_at'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function assignedPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'assigned_partner_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d-m-Y H:i');
    }

    public function getFormattedResolvedAtAttribute()
    {
        return $this->resolved_at ? $this->resolved_at->format('d-m-Y H:i') : null;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'open' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'resolved' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getPriorityBadgeClassAttribute()
    {
        return match($this->priority) {
            'low' => 'bg-green-100 text-green-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-yellow-100 text-yellow-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getCategoryLabelAttribute()
    {
        return match($this->category) {
            'plumbing' => 'Plumbing',
            'electrical' => 'Electrical',
            'hvac' => 'HVAC',
            'appliance' => 'Appliance',
            'general' => 'General',
            'other' => 'Other',
            default => ucfirst($this->category)
        };
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
