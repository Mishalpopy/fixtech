<?php

namespace App\Repositories\Booking;

use App\Models\Bookings\Booking;
use Carbon\Carbon;

class BookingRepository
{

    public function store($data)
    {

        // dd($data);

        $latestBookingNumber = Booking::max('booking_number') ?? 999;

        $nextBookingNumber = $latestBookingNumber + 1;

        $booking = Booking::create([
            'agent_id' => $data['agent'],
            'flight_type_id' => $data['flight_type'],
            'hotel_id' => $data['hotel'],
            'payment_status_id' => $data['payment_status'],
            'passenger_name' => $data['passenger_name'],
            'email' => $data['email'],
            'passenger_number' => $data['passenger_number'],
            'agent_number' => $data['agent_number'],
            'pickup_address' => $data['pickup_address'],
            'adults' => $data['adults'],
            'childrens' => $data['childrens'],
            'adult_price' => $data['adult_price'],
            'child_price' => $data['child_price'],
            'flight_date' => Carbon::parse($data['flight_date'])->format('Y-m-d'),
            'pickup_time' => Carbon::parse($data['pickup_time'])->format('H:i:s'),
            'total_amount' => $data['total_amount'],
            'status' => $data['status'],
            'deposit' => $data['deposit'],
            'voucher_code' => $data['voucher_code'],
            'redemption_code' => $data['redemption_code'],
            'comments' => $data['comments'],
            'booking_number' => $nextBookingNumber
        ]);

        $this->storeBookingPassengers($data, $booking);
        $this->storeAdditionalServices($data, $booking);
    }


    public function update($data,$booking){

        $booking->update([
            'agent_id' => $data['agent'],
            'flight_type_id' => $data['flight_type'],
            'hotel_id' => $data['hotel'],
            'payment_status_id' => $data['payment_status'],
            'passenger_name' => $data['passenger_name'],
            'email' => $data['email'],
            'passenger_number' => $data['passenger_number'],
            'agent_number' => $data['agent_number'],
            'pickup_address' => $data['pickup_address'],
            'adults' => $data['adults'],
            'childrens' => $data['childrens'],
            'adult_price' => $data['adult_price'],
            'child_price' => $data['child_price'],
            'flight_date' => Carbon::parse($data['flight_date'])->format('Y-m-d'),
            'pickup_time' => Carbon::parse($data['pickup_time'])->format('H:i:s'),
            'total_amount' => $data['total_amount'],
            'status' => $data['status'],
            'deposit' => $data['deposit'],
            'voucher_code' => $data['voucher_code'],
            'redemption_code' => $data['redemption_code'],
            'comments' => $data['comments'],
        ]);
    
        // First delete old passengers and services (optional, depending on logic)
        $booking->passengers()->delete();
        $booking->additionalServices()->delete();
    
        // Then re-store updated passengers and services
        $this->storeBookingPassengers($data, $booking);
        $this->storeAdditionalServices($data, $booking);
    


    }

    public function storeBookingPassengers($data, $booking)
    {


        foreach ($data['passengers'] as $passenger) {
            // 
            // dd($passenger);

            $booking->passengers()->create([
                'name' => $passenger['name'],
                'country_id' => $passenger['country_id'],
                'year_of_birth' => Carbon::parse($passenger['year_of_birth'])->format('Y-m-d'),
                'weight' => $passenger['weight'],
                'id_status' => $passenger['id_status'],
            ]);
        }
    }

    public function storeAdditionalServices($data, $booking)
    {
        foreach ($data['additional_services'] as $key => $additional_service) {
            $booking->additionalServices()->create([
                'name' => $additional_service['name'],
                'description' => $additional_service['description'],
                'price' => $additional_service['price'],
            ]);
        }
    }

    public function getAllBookings()
    {
        return Booking::with('hotel')->get();
    }

    public function validateData($data)
    {
        validator($data, [
            'title' => 'bail|required|string',
            'description' => 'bail|required|string',
            'task_type' => 'bail|required',
            'priority' => 'bail|required',
            'due_date' => 'bail|required',


        ])->validate();
    }
}
