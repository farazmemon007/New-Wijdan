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
                <select name="from_warehouse_id" id="from_warehouse_id" class="form-control" required>
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
                <select name="product_id" id="product_id" class="form-control" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->item_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Available Stock</label>
                <input type="number" id="available_stock" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" id="transfer_quantity" class="form-control" required>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function fetchStock() {
        var warehouseId = $('#from_warehouse_id').val();
        var productId = $('#product_id').val();

        if (warehouseId && productId) {
            $.ajax({
                url: '/warehouse-stock-quantity',
                method: 'GET',
                data: {
                    warehouse_id: warehouseId,
                    product_id: productId
                },
                success: function(response) {
                    $('#available_stock').val(response.quantity);
                    $('#transfer_quantity').attr('max', response.quantity);
                }
            });
        }
    }

    $('#from_warehouse_id, #product_id').change(fetchStock);

    $('#transfer_quantity').on('input', function() {
        var entered = parseInt($(this).val());
        var max = parseInt($(this).attr('max'));

        if (entered > max) {
            alert('Cannot transfer more than available stock!');
            $(this).val(max);
        }
    });
});
</script>
