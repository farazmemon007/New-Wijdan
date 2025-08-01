
@extends('admin_panel.layout.app')
@section('content')
    
 <div class="main-content">
            <div class="main-content-inner">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 ">
            <div class="page-header row">
                <div class="page-title col-lg-6">
                    <h4>Product List</h4>
                    <h6>Manage Products</h6>
                </div>
           <div class="page-btn d-flex justify-content-end col-lg-6 ">
                    @if(auth()->user()->can('Create Product') || auth()->user()->email === 'admin@admin.com')
                        <button class="btn btn-outline-primary mb-2 " data-bs-toggle="modal" data-bs-target="#addProductModal">
                            Add Product
                        </button>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Unit</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    {{-- <th>pcs_in_carton</th> --}}
                                    {{-- <th>Purchase Price</th> --}}
                                    <th>Price</th>
                                    {{-- <th>Carton Qnty</th> --}}
                                    {{-- <th>Initial Stock</th> --}}
                                    <th>Alert Quantity</th>
                                    {{-- <th>Quantity</th> --}}
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $key => $product)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $product->category_relation->name ?? '-' }}</td>
                                    <td>{{ $product->sub_category_relation->name ?? '-' }}</td>
                                    <td>{{ $product->unit->name ?? '-' }}</td>
                                    <td>{{ $product->item_code }}</td>
                                    <td>{{ $product->item_name }}</td>
                                    {{-- <td>{{ $product->size }}</td> --}}
                                    {{-- <td>{{ $product->pcs_in_carton }}</td> --}}
                                    {{-- <td>{{ $product->wholesale_price }}</td> --}}
                                    {{-- <td>{{ $product->retail_price }}</td> --}}
                                    {{-- <td>{{ $product->carton_quantity }}</td> --}}
                                    {{-- <td>{{ $product->initial_stock }}</td> --}}
                                    <td>{{ $product->price }}</td>
                                    <td>{{ $product->alert_quantity }}</td>
                                    {{-- <td>{{ $product->quantity }}</td> --}}
                                    <td>
                                        @if(auth()->user()->can('Edit Product') || auth()->user()->email === 'admin@admin.com')
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary text-white">
                                                Edit
                                            </a>
                                        @endif
                                        @if(auth()->user()->can('Delete Product') || auth()->user()->email === 'admin@admin.com')
                                            <a href="" class="btn btn-sm btn-danger  text-white">
                                                Delete
                                            </a>
                                        @endif
                                        <a href="{{ route('product.barcode', $product->id) }}" class="btn btn-sm btn-success">Barcode</a>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    url: "{{ route('fetch-subcategories') }}",
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
                    url: "{{ route('fetch-subcategories') }}",
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