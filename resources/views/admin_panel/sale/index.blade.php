{{-- @php --}}
    
{{-- // echo "<pre>";
//         print_r($sales); 
//     echo "</pre>"; --}}
{{-- @endphp --}}
@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Button CSS for dropdown */
    .action-dropdown {
        border-radius: 12px;
        padding: 6px;
        min-width: 210px;
    }

    .action-dropdown .dropdown-item {
        padding: 9px 14px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.25s ease;
    }

    .action-dropdown .dropdown-item:hover {
        background: linear-gradient(90deg, #f8f9fa, #eef1f5);
        transform: translateX(4px);
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Records</h5>
            <div>
                <a href="{{ route('sale.add') }}" class="btn btn-primary me-2">Add Sale</a>
                <a href="{{ url('bookings') }}" class="btn btn-primary">All Bookings</a>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Invoice No</th>
                        <th>Customer Type</th>
                        <th>Customer name</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Discount %</th>
                        <th>Discount Amount</th>
                        <th>Total Balance</th>
                        <th>Receipt</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>@foreach($sales as $sale)
<tr>
    <td>{{ $sale->id }}</td>
    <td>{{ $sale->invoice_no }}</td>
    <td>{{ $sale->party_type }}</td>
    <!-- Customer Name from relation -->
    <td>{{ $sale->customer->customer_name ?? 'N/A' }}</td>
    <td>{{ $sale->quantity ?? 0 }}</td>
    <td>{{ number_format($sale->sub_total1, 2) }}</td>
    <td>{{ number_format($sale->discount_percent, 2) }}</td>
    <td>{{ number_format($sale->discount_amount, 2) }}</td>
    <td>{{ number_format($sale->total_balance, 2) }}</td>
    <td>{{ number_format($sale->receipt1 + $sale->receipt2, 2) }}</td>
    <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d-m-Y') }}</td>
    <td class="text-center">
        <!-- PRIMARY ACTION -->
        <a href="{{ route('booking.dc', $sale->id) }}" class="btn btn-sm btn-info text-white me-1" title="View Invoice">
            <i class="fas fa-file-invoice"></i> Invoice
        </a>

        <!-- MORE OPTIONS DROPDOWN -->
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i> More
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg action-dropdown">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('sales.return.create', $sale->id) }}">
                        <i class="fas fa-undo text-warning"></i> Return Sale
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('sales.edit', $sale->id) }}">
                        <i class="fas fa-edit text-primary"></i> Edit Sale
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('sales.recepit', $sale->id) }}">
                        <i class="fas fa-receipt text-danger"></i> Receipt
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('booking.dc', $sale->id) }}">
                        <i class="fas fa-truck text-success"></i> Delivery Challan
                    </a>
                </li>
            </ul>
        </div>
    </td>
</tr>
@endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
