<?php

namespace App\Models\Bookings;

use Illuminate\Database\Eloquent\Model;

class BookingAdditionalService extends Model
{
    protected $fillable = [
        'booking_id',
        'name',
        'description',
        'price'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
