<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body { font-family: sans-serif; padding: 30px; }
        .header, .footer { text-align: center; }
        .title { font-size: 24px; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Booking Invoice</h2>
        <p><strong>Booking No:</strong> {{ $booking->booking_number }}</p>
        <p><strong>Date:</strong> {{ $booking->created_at->format('d-m-Y') }}</p>
    </div>

    <div class="section">
        <h4>Hotel Information</h4>
        <p><strong>Hotel:</strong> {{ $booking->hotel->name ?? 'N/A' }}</p>
    </div>

    <div class="section">
        <h4>Passenger Details</h4>
        <p><strong>Name:</strong> {{ $booking->passenger_name }}</p>
        <p><strong>No. of Passengers:</strong> {{ $booking->passengers_count }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Passengers</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Booking Charges</td>
                <td>{{ $booking->passengers_count }}</td>
                <td>{{ number_format($booking->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section total">
        <p><strong>Total Amount:</strong> ₹{{ number_format($booking->total_amount, 2) }}</p>
    </div>

    <div class="footer">
        <p>Thank you for your booking!</p>
    </div>
</body>
</html>
