@php
    //     echo "<pre>";
    //     print_r($bookings);
    //     echo "<pre>";
    // dd();
@endphp

@extends('admin_panel.layout.app')
@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0">BOOKINGS</h5>
                <span class="fw-bold text-dark">
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary">Add Booking</a>
                </span>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Invoice No</th>
                            <th>Party Type</th>
                            <th>Quantity</th>
                            <th>Sub Total</th>
                            <th>Discount %</th>
                            <th>Discount Amount</th>
                            <th>Total Balance</th>
                            <th>Booking Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <!-- Customer Name from relation -->
                                <td>{{ $booking->customer->customer_name ?? 'N/A' }}</td>
                                <td>{{ $booking->invoice_no }}</td>
                                <td>{{ $booking->party_type }}</td>
                                <td>{{ $booking->quantity ?? 0 }}</td>
                                <td>{{ number_format($booking->sub_total1, 2) }}</td>
                                <td>{{ number_format($booking->discount_percent, 2) }}</td>
                                <td>{{ number_format($booking->discount_amount, 2) }}</td>
                                <td>{{ number_format($booking->total_balance, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('d-m-Y') }}</td>
                                <td>
                                    <span
                                        class="badge 
        {{ ($booking->status ?? 'pending') == 'pending'
            ? 'bg-warning'
            : (($booking->status ?? '') == 'approved'
                ? 'bg-success'
                : 'bg-secondary') }}">
                                        {{ ucfirst($booking->status ?? 'pending') }}
                                    </span>
                                </td>


                                <td>
                                    <a href="{{ route('sale.invoice', $booking->id) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary">Receipt</a>
                                    <a href="{{ route('sales.from.booking', $booking->id) }}"
                                        class="btn btn-sm btn-success">Confirm</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
