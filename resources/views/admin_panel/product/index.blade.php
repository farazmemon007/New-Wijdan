
@extends('admin_panel.layout.app')
@section('content')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
    width: 75px !important
}
    </style>
 <div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">📦 Product List</h5>
            <small class="text-muted">Manage all products here</small>
        </div>
        <div class="d-flex justify-content-between align-items-end gap-1" >
              @if(auth()->user()->can(' Discount.index') || auth()->user()->email === 'admin@admin.com')
            <a href="{{ route('discount.index') }}" class="btn btn-success btn-sm">
                View Discount
            </a>    
        @endif
          <a href="create_prodcut" class="btn btn-primary"> Add product</a>

                <button id="createDiscountBtn" class="btn btn-success btn-sm">
        ➡ Create Discount
    </button>
        </div>
      
    </div>

    <div class="card-body">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table id="productTable" class="table table-striped table-bordered align-middle nowrap" style="width:100%">
                <thead class="table-light">
                    <tr>
                          <th><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Item Code</th>
                        <th>Image</th>
                        <th>Category<br>Sub-Category</th>
                        <th>Item Name</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Alert Qty</th>
                        <th class="text-center">Brand Name</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $product)
                    {{-- @dd($product->stock->qty) --}}
                    <tr>
                           <td><input type="checkbox" class="selectProduct" value="{{ $product->id }}"></td>
                        <td>{{ $key + 1 }}</td>
                        <td class="fw-bold">{{ $product->item_code }}</td>
                        <td>
                            @if($product->image)
                                <img src="{{ asset('uploads/products/' . $product->image) }}" 
                                     alt="Product" width="50" height="50" 
                                     class="rounded border">
                            @else
                                <span class="badge bg-secondary">No Img</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $product->category_relation->name ?? '-' }}</strong><br>
                            <small class="text-muted">{{ $product->sub_category_relation->name ?? '-' }}</small>
                        </td>
                        <td>{{ $product->item_name }}</td>
                        <td>{{ $product->unit_id ?? '-' }}</td>
                        <td>PKR {{ number_format($product->price) }}</td>
                        <td>{{ $product->stock->qty ?? '- ' }}</td>
                        <td>{{ $product->alert_quantity }}</td>
                        <td>{{ $product->brand->name ?? '-' }}</td>
                        <td class="text-center">
                            @if(auth()->user()->can('Edit Product') || auth()->user()->email === 'admin@admin.com')
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                    ✏ Edit
                                </a>
                            @endif
                           
                            <a href="{{ route('product.barcode', $product->id) }}" class="btn btn-sm btn-outline-success">
                                🏷 Barcode
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- add product modal --}}

<div class="modal fade bd-example-modal-lg" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('store-product') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category_id" id="categorySelect" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sub-Category</label>
                            <select class="form-control" name="sub_category_id" id="subCategorySelect" >
                                <option value="">Select Sub-Category</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" required>
                        </div>
                    </div>

                    {{-- <div class="row"> --}}
                        {{-- <div class="col-md-6 mb-3">
                            <label class="form-label">Size</label> --}}
                            {{-- <select class="form-control" name="size" id="sizeSelect" required>
                                <option value="">Select Size</option>
                           
                            </select> --}}
                        {{-- </div> --}}
                        {{-- <div class="col-md-6 mb-3">
                            <label class="form-label">Carton Quantity</label>
                            <input type="number" class="form-control" name="carton_quantity" id="carton_quantity" required>
                        </div> --}}
                    {{-- </div> --}}
                    {{-- <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pieces per Carton</label>
                            <input type="number" class="form-control" name="pcs_in_carton" id="pieces_per_carton" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" name="initial_stock" id="initial_stock">
                        </div>
                    </div> --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alert Quantity</label>
                            <input type="number" class="form-control" name="alert_quantity" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" name="wholesale_price" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Price</label>
                            <input type="number" step="0.01" class="form-control" name="retail_price" required>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // Select/Deselect all checkboxes
    $('#selectAll').click(function() {
        $('.selectProduct').prop('checked', this.checked);
    });

    // On "Create Discount" click
    $('#createDiscountBtn').click(function() {
        var selected = [];
        $('.selectProduct:checked').each(function() {
            selected.push($(this).val());
        });

        if(selected.length === 0){
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Please select at least one product!",
               
            });
            return;
        }

        // Redirect with product IDs as query param
        window.location.href = "{{ route('discount.create') }}" + "?products=" + selected.join(',');
    });
});
</script>

<script>
$(document).ready(function() {
    $('#productTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        order: [[0, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search products..."
        }
    });
});
</script>

<!-- DataTables CSS -->

@endsection
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let cartonQuantityInput = document.getElementById("carton_quantity");
        let piecesPerCartonInput = document.getElementById("pieces_per_carton");
        let initialStockInput = document.getElementById("initial_stock");

        function updateInitialStock() {
            let cartonQuantity = parseInt(cartonQuantityInput.value) || 0;
            let piecesPerCarton = parseInt(piecesPerCartonInput.value) || 0;
            initialStockInput.value = cartonQuantity * piecesPerCarton;
        }

        cartonQuantityInput.addEventListener("input", updateInitialStock);
        piecesPerCartonInput.addEventListener("input", updateInitialStock);
    });

 $(document).ready(function() {
        // Add Product Modal: Fetch Subcategories on Category Change
        $('#categorySelect').change(function() {
            var categoryId = $(this).val();
        
            $('#subCategorySelect').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                   url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#subCategorySelect').append('<option value="' + subCategory.id + '">' + subCategory.name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
            }
        });

        // Edit Product Modal: Fetch Subcategories when Category is Changed
        $('#edit_category').change(function() {
            var categoryId = $(this).val();
            $('#edit_sub_category').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                   url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#edit_sub_category').append('<option value="' + subCategory.sub_category_name + '">' + subCategory.sub_category_name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
            }
        });
    });
</script>