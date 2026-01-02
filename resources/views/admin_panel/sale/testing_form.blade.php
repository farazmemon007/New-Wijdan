<form action="{{ route('sales.store') }}" method="POST">
    @csrf
    <input type="hidden" name="action" value="sale">
    <input type="text" name="customer" value="1">
    <input type="text" name="reference" value="INV-001">

    <!-- Products -->
    <input type="text" name="product_id[]" value="101">
    <input type="text" name="product[]" value="Product A">
    <input type="text" name="item_code[]" value="A101">
    <input type="text" name="qty[]" value="2">
    <input type="text" name="price[]" value="500">
    <input type="text" name="item_disc[]" value="50">
    <input type="text" name="total[]" value="950">
    <input type="text" name="color[]" value='["Red"]'>

    <input type="text" name="branch_id" value="1">
    <input type="text" name="warehouse_id" value="1">

    <input type="text" name="total_subtotal" value="1000">
    <input type="text" name="total_extra_cost" value="0">
    <input type="text" name="total_net" value="950">
    <input type="text" name="cash" value="1000">
    <input type="text" name="card" value="0">
    <input type="text" name="change" value="50">
    <input type="text" name="total_amount_Words" value="Nine Hundred Fifty">

    <button type="submit">Post Sale</button>
</form>
