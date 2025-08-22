@extends('admin_panel.layout.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Discount for Selected Products</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('discount.store') }}" method="POST">
            @csrf
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Original Price</th>
                        <th>Discount %</th>
                        <th>Discount Amount</th>
                        <th>Final Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $product)
                    <tr>
                        <input type="hidden" name="product_id[]" value="{{ $product->id }}">
                        <input type="hidden" name="actual_price[]" value="{{ $product->price }}">
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $product->item_code }}</td>
                        <td>{{ $product->item_name }}</td>
                        <td>{{ $product->price }}</td>
                        <td><input type="number" name="discount_percentage[]" class="form-control discountPercentage" value="0"></td>
                        <td><input type="number" name="discount_amount[]" class="form-control discountAmount" value="0"></td>
                        <td><input type="number" name="final_price[]" class="form-control finalPrice" readonly></td>
                        <td>
                            <select name="status[]" class="form-control">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary mt-3">Save Discounts</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    function updateFinalPrice(row){
        let original = parseFloat(row.find('td:nth-child(4)').text());
        let perc = parseFloat(row.find('.discountPercentage').val()) || 0;
        let amt = parseFloat(row.find('.discountAmount').val()) || 0;
        let finalPrice = original;

        if(perc > 0){
            finalPrice = original - (original * perc / 100);
        } else if(amt > 0){
            finalPrice = original - amt;
        }

        row.find('.finalPrice').val(finalPrice.toFixed(2));
    }

    $('.discountPercentage, .discountAmount').on('input', function(){
        let row = $(this).closest('tr');
        updateFinalPrice(row);
    });

    $('#discountCreateTable tbody tr').each(function(){
        updateFinalPrice($(this));
    });
});
</script>
@endsection
