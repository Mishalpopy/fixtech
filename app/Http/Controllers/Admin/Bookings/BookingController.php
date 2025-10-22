<?php

namespace App\Http\Controllers\Admin\Bookings;

use App\Http\Controllers\Controller;
use App\Models\Bookings\Booking;
use App\Models\Country;
use App\Models\Flight\FlightType;
use App\Models\Payment\PaymentStatuses;
use App\Repositories\Agent\AgentRepository;
use App\Repositories\Booking\BookingRepository;
use App\Repositories\Hotel\HotelRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class BookingController extends Controller
{
    use Toast;
    protected $agent_repo;
    protected $booking_repo;
    protected $hotel_repo;

    public function __construct(BookingRepository $booking_repo)
    {
        $this->booking_repo = $booking_repo;
    }

    public function index()
    {
        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $this->booking_repo->getAllBookings()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Bookings/Create', [
            'flight_types' => FlightType::get(),
            'payment_status' => PaymentStatuses::get(),
            'booking_status' => booking_status(),
            'countries' => Country::get()
        ]);
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
            $booking = $this->booking_repo->store($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            dd($th);
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Booking Successfully Created");
        return back();
    }

    public function edit(Booking $booking)
    {
        return Inertia::render('Admin/Bookings/Edit', [
            'booking' => $booking->load('flightType', 'paymentStatus', 'agent', 'passengers', 'additionalServices', 'hotel'),
            'flight_types' => FlightType::get(),
            'payment_status' => PaymentStatuses::get(),
            'booking_status' => booking_status(),
            'countries' => Country::get()
        ]);
    }

    public function update(Request $request,Booking $booking){

        DB::beginTransaction();

        try {
            $booking = $this->booking_repo->update($request->all(),$booking);
        } catch (Throwable $th) {
            DB::rollBack();
            dd($th);
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Booking Successfully Created");
        return back();
    }

    public function show(Booking $booking){

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $booking->load('flightType', 'paymentStatus', 'agent', 'passengers', 'additionalServices', 'hotel'),
            'flight_types' => FlightType::get(),
            'payment_status' => PaymentStatuses::get(),
            'booking_status' => booking_status(),
            'countries' => Country::get()
        ]);

    }

    public function downloadInvoice(Booking $booking)
{
    $data = [
        'booking' => $booking->load('hotel'), // eager load related hotel if needed
    ];

    $pdf = Pdf::loadView('admin.bookings.invoice', $data);
    return $pdf->download("Invoice-{$booking->booking_number}.pdf");
}
}
