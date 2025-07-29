@extends('admin_panel.layout.app')

@section('content')
     <div class="main-content">
            <div class="main-content-inner">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 ">
                                  <div class="page-header">
                <div class="page-title">
                    <h4>Edit Product</h4>
                    <h6>Manage Product Details</h6>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif
                    <form action="{{ route('product.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                       <input type="hidden" name="category_id" value="{{ $product->category_id }}">
<input type="hidden" name="sub_category_id" value="{{ $product->sub_category_id }}">

<div class="mb-3 col-md-6">
    <label class="form-label">Category</label>
    <input type="text" class="form-control" value="{{ $product->category_relation->name ?? 'No Category' }}" readonly>
</div>

<div class="mb-3 col-md-6">
    <label class="form-label">Sub Category</label>
    <input type="text" class="form-control" value="{{ $product->sub_category_relation->name ?? 'No Sub Category' }}" readonly>
</div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Item Name</label>
                                    <input type="text" class="form-control" name="item_name" value="{{ $product->item_name }}" required>
                                </div>
                            </div>

                            {{-- <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Size</label>
                                    <input type="text" class="form-control" name="size" value="{{ $product->size }}" readonly>
                                </div>
                            </div> --}}

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pcs in Carton</label>
                                    <input type="number" class="form-control" name="pcs_in_carton" value="{{ $product->pcs_in_carton }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Wholesale Price</label>
                                    <input type="number" class="form-control" name="wholesale_price" value="{{ $product->wholesale_price }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Retail Price</label>
                                    <input type="number" class="form-control" name="retail_price" value="{{ $product->retail_price }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Initial Stock</label>
                                    <input type="number" class="form-control" name="initial_stock" value="{{ $product->initial_stock }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Alert Quantity</label>
                                    <input type="number" class="form-control" name="alert_quantity" value="{{ $product->alert_quantity }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
                        </div>
                     
                    </div>
                    </div>
                    </div>
                    </div>
@endsection