<?php

namespace App\Models\Bookings;

use App\Models\Admin\Agent\Agent;
use App\Models\Flight\FlightType;
use App\Models\Hotel\Hotel;
use App\Models\Payment\PaymentStatuses;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [

        'agent_id',
        'flight_type_id',
        'payment_status_id',
        'hotel_id',
        'passenger_name',
        'email',
        'passenger_number',
        'agent_number',
        'pickup_address',
        'adults',
        'childrens',
        'adult_price',
        'child_price',
        'flight_date',
        'pickup_time',
        'total_amount',
        'status',
        'deposit',
        'voucher_code',
        'redemption_code',
        'comments',
        'booking_number'

    ];

    protected $appends = [
        'passengers_count',
        'amount',
        'formatted_created_at'
    ];

    public function flightType()
    {
        return $this->belongsTo(FlightType::class);
    }

    public function paymentStatus()
    {
        return $this->belongsTo(PaymentStatuses::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function passengers()
    {
        return $this->hasMany(BookingPassenger::class);
    }

    public function additionalServices()
    {
        return $this->hasMany(BookingAdditionalService::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function getPassengersCountAttribute()
    {
        return $this->passengers()->count();
    }

    public function getAmountAttribute()
    {

        $childPrice = $this->child_price ?? 0;
        $adultPrice = $this->adult_price ?? 0;

        $childCount = $this->childrens ?? 0;
        $adultCount = $this->adults ?? 0;

        return ($childPrice * $childCount) + ($adultPrice * $adultCount);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }
}
