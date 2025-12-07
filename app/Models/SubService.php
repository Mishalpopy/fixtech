<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'name',
        'description',
        'image',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = [
        'formatted_created_at'
    ];

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d-m-Y') : null;
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }

    public function processes(): HasMany
    {
        return $this->hasMany(ServiceProcess::class)->orderBy('order');
    }

    public function priceCharts(): HasMany
    {
        return $this->hasMany(SubServicePriceChart::class)->orderBy('order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(SubServiceFaq::class)->orderBy('order');
    }
}
