<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubServicePriceChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_service_id',
        'time_duration',
        'current_price',
        'original_price',
        'is_urgent',
        'order',
        'status'
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_urgent' => 'boolean',
        'status' => 'boolean',
        'order' => 'integer',
    ];

    protected $appends = [
        'formatted_created_at'
    ];

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d-m-Y') : null;
    }

    public function subService(): BelongsTo
    {
        return $this->belongsTo(SubService::class);
    }
}
