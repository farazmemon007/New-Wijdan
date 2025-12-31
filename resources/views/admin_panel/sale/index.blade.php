@extends('admin_panel.layout.app')
@section('content')
<style>
    /* button css dropdown */
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
            <h5 class="mb-0">SALES</h5>
            <div>
                <span class="fw-bold text-dark"><a href="{{ route('sale.add') }}" class="btn btn-primary">Add sale</a></span>
                <span class="fw-bold text-dark"><a href="{{ url('bookings') }}" class="btn btn-primary">All Booking</a></span>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>discount</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Status Sale</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->id }}</td>
                        <td>{{ $sale->customer_relation->customer_name ?? 'N/A' }}</td>
                        <td>{{ $sale->reference }}</td>
                        <td>{{ $sale->product_relation->item_name ?? 'N/A' }}</td>
                        <td>{{ $sale->qty }}</td>
                        <td>{{ $sale->per_price }}</td>
                        <td>{{ $sale->per_discount}}</td>
                        <td>{{ $sale->per_total }}</td>
                        <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                        <td>
                            @if($sale->sale_status === null)
                            <span class="badge bg-success">Sale</span>
                            @elseif($sale->sale_status == 1)
                            <span class="badge bg-danger">Return</span>
                            @else
                            <span class="badge bg-secondary">Unknown</span>
                            @endif
                        </td>
                        <td class="text-center">

    <!-- PRIMARY ACTION -->
    <a href="{{ route('sales.invoice', $sale->id) }}"
       class="btn btn-sm btn-info text-white me-1"
       title="View Invoice">
        <i class="fas fa-file-invoice"></i>
        Recipt
    </a>

    <!-- MORE OPTIONS DROPDOWN -->
    <div class="btn-group">
        <button type="button"
                class="btn btn-sm btn-outline-dark dropdown-toggle dropdown-toggle-split"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="fas fa-ellipsis-v"></i>
            more
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-lg action-dropdown">

            <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="{{ route('sales.return.create', $sale->id) }}">
                    <i class="fas fa-undo text-warning"></i>
                    Return Sale
                </a>
            </li>

            <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="{{ route('sales.edit', $sale->id) }}">
                    <i class="fas fa-edit text-primary"></i>
                    Edit Sale
                </a>
            </li>

            <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="{{ route('sales.recepit', $sale->id) }}">
                    <i class="fas fa-receipt text-danger"></i>
                    Receipt
                </a>
            </li>

            <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="{{ route('sales.dc', $sale->id) }}">
                    <i class="fas fa-truck text-success"></i>
                    Delivery Challan
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