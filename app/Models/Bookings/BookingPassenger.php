<?php

namespace App\Models\Bookings;

use App\Models\Country;
use Illuminate\Database\Eloquent\Model;

class BookingPassenger extends Model
{
    protected $fillable = [
        'booking_id',
        'country_id',
        'name',
        'year_of_birth',
        'weight',
        'id_status',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
