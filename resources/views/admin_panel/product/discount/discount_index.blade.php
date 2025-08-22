@extends('admin_panel.layout.app')
@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">💰 Product Discounts</h5>
            <small class="text-muted">Manage all product discounts here</small>
        </div>
        @if(auth()->user()->can('Create Discount') || auth()->user()->email === 'admin@admin.com')
            <a href="{{ route('discount.create') }}" class="btn btn-success btn-sm">
                ➕ Add Discount
            </a>
        @endif
    </div>

    <div class="card-body">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="discountTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Code</th>
                        <th>Image</th>
                        <th>Category / Sub-Category</th>
                        <th>Item Name</th>
                        <th>Unit</th>
                        <th>Brand</th>
                        <th>Original Price</th>
                        <th>Discount %</th>
                        <th>Discount Amount</th>
                        <th>Final Price</th>
                        <th>Status</th>
                        <th>Barcode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($discounts as $key => $discount)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $discount->product->item_code }}</td>
                        <td>
                            @if($discount->product->image)
                                <img src="{{ asset('uploads/products/'.$discount->product->image) }}" width="50" height="50">
                            @else
                                <span class="badge bg-secondary">No Img</span>
                            @endif
                        </td>
                        <td>{{ $discount->product->category_relation->name ?? '-' }} / {{ $discount->product->sub_category_relation->name ?? '-' }}</td>
                        <td>{{ $discount->product->item_name }}</td>
                        <td>{{ $discount->product->unit->name ?? '-' }}</td>
                        <td>{{ $discount->product->brand->name ?? '-' }}</td>
                        <td>{{ number_format($discount->actual_price,2) }}</td>
                        <td>{{ $discount->discount_percentage }}%</td>
                        <td>{{ number_format($discount->discount_amount,2) }}</td>
                        <td>{{ number_format($discount->final_price,2) }}</td>
                        <td>
                            <form action="{{ route('discount.toggleStatus', $discount->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm {{ $discount->status ? 'btn-success' : 'btn-danger' }}">
                                    {{ $discount->status ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('discount.barcode', $discount->id) }}" class="btn btn-sm btn-outline-success">🏷 Barcode</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- DataTables JS --}}
<script>
$(document).ready(function() {
    $('#discountTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        order: [[0, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search discounts..."
        }
    });
});
</script>
@endsection
