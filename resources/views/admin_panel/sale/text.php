@extends('admin_panel.layout.app')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .searchResults {
        position: absolute;
        z-index: 9999;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background: #fff;
        text-align: start;
    }
    .search-result-item.active {
        background: #007bff;
        color: white;
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">SALES</h5>
            <span class="fw-bold text-dark">USER NAME: <span>Administrator</span></span>
        </div>
<form action="{{ route('sales.store') }}" method="POST">
    @csrf
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

        <div class="card-body">
            {{-- Top Info --}}
           <div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">Customer:</label>
        <input type="text" name="customer" class="form-control form-control-sm" value="Counter Sale">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Reference #</label>
        <input type="text" name="reference" class="form-control form-control-sm">
    </div>
</div>

            {{-- Item Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle text-center">
                    <thead>
                        <tr class="text-center">
                            <th>Product</th><th>Code</th><th>Brand</th><th>Unit</th><th>Price</th><th>Discount</th><th>Qty</th><th>Total</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemTable">
                  <tr>
    <td>
        <input type="hidden" name="product_id[]" class="product_id">
        <input type="text" name="product[]" class="form-control productSearch" placeholder="Enter product name..." autocomplete="off">
        <ul class="searchResults list-group mt-1"></ul>
    </td>
    <td><input type="text" name="product_code[]" class="form-control item_code" readonly></td>
    <td><input type="text" name="brand[]" class="form-control brand" readonly></td>
    <td><input type="text" name="unit[]" class="form-control unit" readonly></td>
    <td><input type="number" name="per_price[]" class="form-control price" step="0.01" value="0"></td>
    <td><input type="number" name="item_disc[]" class="form-control item_disc" step="0.01" value="0"></td>
    <td><input type="number" name="qty[]" class="form-control qty" min="1" value="1"></td>
    <td><input type="text" name="row_total[]" class="form-control row_total" readonly></td>
    <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
</tr>

{{-- Summary Table --}}
               </tbody>
                </table>
            </div>

            {{-- Summary --}}
            <table class="table table-bordered table-sm mt-4 text-center">
                <tr>
                    <th>Amount In Words</th><th>Subtotal</th><th>Discount</th><th>Extra Cost</th><th>Net Total</th>
                </tr>
             <tr>
    <td><input type="text" name="amount_in_words" class="form-control amount-in-words" readonly></td>
    <td><input type="text" name="subtotal" id="subtotal" class="form-control text-center" readonly></td>
    <td><input type="number" name="overall_discount" id="overallDiscount" class="form-control text-center" value="0"></td>
    <td><input type="number" name="extra_cost" id="extraCost" class="form-control text-center" value="0"></td>
    <td><input type="text" name="net_total" id="netAmount" class="form-control text-center" readonly></td>
</tr>
     
            </table>

            {{-- Footer --}}
            <div class="d-flex justify-content-between mt-4">
                <div><strong>Total Items:</strong> <span id="totalQty">0</span></div>
                <div>
                    <button class="btn btn-primary">Save</button>
                    <button class="btn btn-secondary">Close</button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>


<script>
$(document).ready(function() {

    function numberToWords(num) {
        const a = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
                   "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen",
                   "Eighteen", "Nineteen"];
        const b = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
        if ((num = num.toString()).length > 9) return "Overflow";
        const n = ("000000000" + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{3})$/);
        if (!n) return; let str = "";
        str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + " " + a[n[1][1]]) + " Crore " : "";
        str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + " " + a[n[2][1]]) + " Lakh " : "";
        str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + " " + a[n[3][1]]) + " Thousand " : "";
        str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + " " + a[n[4][1]]) + " " : "";
        return str.trim() + " Rupees Only";
    }

    function calcRow($row) {
        const qty = parseFloat($row.find('.qty').val()) || 0;
        const price = parseFloat($row.find('.price').val()) || 0;
        const disc = parseFloat($row.find('.item_disc').val()) || 0;
        const total = Math.max(0, (qty * price) - disc);
        $row.find('.row_total').val(total.toFixed(2));
    }

    function updateSummary() {
        let subtotal = 0;
        let totalQty = 0;

        $('#itemTable tr').each(function () {
            const rowTotal = parseFloat($(this).find('.row_total').val()) || 0;
            const qty = parseFloat($(this).find('.qty').val()) || 0;
            subtotal += rowTotal;
            totalQty += qty;
        });

        $('#subtotal').val(subtotal.toFixed(2));
        const discount = parseFloat($('#overallDiscount').val()) || 0;
        const extra = parseFloat($('#extraCost').val()) || 0;
        const net = subtotal - discount + extra;
        $('#netAmount').val(net.toFixed(2));
        $('.amount-in-words').val(numberToWords(Math.round(net)));
        $('#totalQty').text(totalQty);
    }

    function addNewRow() {
      const newRow = `
    <tr>
        <td>
            <input type="hidden" name="product_id[]" class="product_id">
            <input type="text" name="product[]" class="form-control productSearch" placeholder="Enter product name..." autocomplete="off">
            <ul class="searchResults list-group mt-1"></ul>
        </td>
        <td><input type="text" name="product_code[]" class="form-control item_code" readonly></td>
        <td><input type="text" name="brand[]" class="form-control brand" readonly></td>
        <td><input type="text" name="unit[]" class="form-control unit" readonly></td>
        <td><input type="number" name="per_price[]" class="form-control price" step="0.01" value="0"></td>
        <td><input type="number" name="item_disc[]" class="form-control item_disc" step="0.01" value="0"></td>
        <td><input type="number" name="qty[]" class="form-control qty" min="1" value="1"></td>
        <td><input type="text" name="row_total[]" class="form-control row_total" readonly></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
    </tr>`;

        $('#itemTable').append(newRow);
    }

    // Fetch products from server
    $(document).on('keyup', '.productSearch', function (e) {
        const $input = $(this);
        const $row = $input.closest('tr');
        const $resultBox = $row.find('.searchResults');
        const q = $input.val();

        if (q.length === 0) {
            $resultBox.empty();
            return;
        }

        $.get("{{ route('search-product-name') }}", { q }, function (data) {
            let list = '';
            data.forEach(p => {
                list += `<li class="list-group-item search-result-item" 
                            data-id="${p.id}"
                            data-name="${p.item_name}"
                            data-code="${p.item_code}"
                            data-brand="${p.brand?.name || ''}"
                            data-unit="${p.unit_id}"
                            data-price="${p.wholesale_price}">
                            ${p.item_name} - ${p.item_code} - Rs. ${p.wholesale_price}
                         </li>`;
            });
            $resultBox.html(list);
        });
    });

    $(document).on('click', '.search-result-item', function () {
        const $li = $(this);
        const $row = $li.closest('tr');

        const selectedId = $li.data('id');
        let alreadyAdded = false;
        $('.product_id').each(function () {
            if ($(this).val() == selectedId) {
                alreadyAdded = true;
            }
        });
        if (alreadyAdded) {
            alert('Product already added!');
            return;
        }

        $row.find('.product_id').val($li.data('id'));
        $row.find('.productSearch').val($li.data('name'));
        $row.find('.item_code').val($li.data('code'));
        $row.find('.brand').val($li.data('brand'));
        $row.find('.unit').val($li.data('unit'));
        $row.find('.price').val($li.data('price'));
        $row.find('.qty').val(1);
        $row.find('.item_disc').val(0);

        calcRow($row);
        updateSummary();
        addNewRow();

        $row.find('.searchResults').empty();
    });

    $(document).on('input', '.qty, .price, .item_disc', function () {
        const $row = $(this).closest('tr');
        calcRow($row);
        updateSummary();
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        updateSummary();
    });

    $('#overallDiscount, #extraCost').on('input', updateSummary);
});
</script>
@endsection
