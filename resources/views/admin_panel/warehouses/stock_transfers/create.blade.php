@extends('admin_panel.layout.app')
@section('content')

<div class="card shadow-sm border-0">
    <div class="card-header">
        <h5>➕ New Stock Transfer</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('stock_transfers.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>From Warehouse</label>
                <select name="from_warehouse_id" class="form-control" required>
                    <option value="">Select Warehouse</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>To</label>
                <div class="d-flex gap-2">
                    <select name="to_warehouse_id" class="form-control">
                        <option value="">Select Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                        @endforeach
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="to_shop" value="1" id="toShop">
                        <label class="form-check-label" for="toShop">Transfer to Shop</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label>Product</label>
                <select name="product_id" class="form-control" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->item_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Transfer Stock</button>
        </form>
    </div>
</div>

@endsection
