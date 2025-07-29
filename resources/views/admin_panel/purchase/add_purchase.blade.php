
    {{-- Item Row Autocomplete + Add/Remove --}}
    <!-- Make sure jQuery and Bootstrap Typeahead are included -->
    @extends('admin_panel.layout.app')

    @section('content')
            <div class="main-content">
                <div class="main-content-inner">
                    <div class="container">
                        <div class="row">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .table-scroll tbody {
            display: block;
            max-height: calc(60px * 5);
            /* Assuming each row is ~40px tall */
            overflow-y: auto;
        }

        .table-scroll thead,
        .table-scroll tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        /* Optional: Hide scrollbar width impact */
        .table-scroll thead {
            width: calc(100% - 1em);
        }

        .table-scroll .icon-col {
            width: 51px;
            /* Ya jitni chhoti chahiye */
            min-width: 51px;
            max-width: 40px;
        }

        .table-scroll {
            max-height: none !important;
            overflow-y: visible !important;
        }


        .disabled-row input {
            background-color: #f8f9fa;
            pointer-events: none;
        }
    </style>

    <body>
        <!-- page-wrapper start -->
    
            <div class="body-wrapper">
                <div class="bodywrapper__inner">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-nowrap overflow-auto">
                        <!-- Title on the left -->
                        <div class="flex-grow-1">
                            <h6 class="page-title m-0">INWARDS GATE PASSES</h6>
                        </div>

                        <!-- Buttons on the right -->
        <div class="d-flex gap-4 justify-content-end flex-wrap">
        <button type="button" class="btn btn-outline-primary " style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#supplierModal">
            <i class="la la-truck-loading"></i> Add New Vendor
        </button>

        <button type="button" class="btn btn-outline-success " style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#transportModal">
            <i class="la la-truck"></i> Add New Transport
        </button>

        <button type="button" class="btn btn-outline-warning text-dark " style="margin-right: 5px" data-bs-toggle="modal" data-bs-target="#warehouseModal">
            <i class="la la-warehouse"></i> Add New Warehouse
        </button>

        <a href="#" class="btn btn-outline-info " style="margin-right: 5px">
            <i class="la la-plus"></i> Add Product
        </a>

        {{-- <button type="button" class="btn btn-outline-danger " id="cancelBtn">
            Cancel
        </button> --}}
        <a href="{{ route('Purchase.home') }}" class="btn btn-danger" >Back </a>
    </div>



                    </div>



                    <div class="row gy-3">
                        <div class="col-lg-12 col-md-12 mb-30">
                            <div class="card">
                                <div class="card-body">
                                    {{-- <form action="{{ route('store-Purchase') }}" method="POST"> --}}
                                            @if ($errors->any())
                                                <div class="alert alert-danger">
                                                    <ul>
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('store.Purchase') }}" method="POST">
                                        @csrf
                                    <div class="row mb-3 g-3 mt-4">
        <div class="col-xl-12">
            <div class="row g-3">
                <!-- Serial, Date, PO, GP -->
                <div class="col-xl-3 col-sm-6 mt-3">
                    <label><i class="bi bi-hash text-primary me-1"></i> Serial No</label>
                    <input name="serial_no" type="text" class="form-control" >
                </div>
                <div class="col-xl-3 col-sm-6 mt-3">
                    <label><i class="bi bi-calendar-date text-primary me-1"></i> Current Date</label>
                    <input name="purchase_date" value="{{ date('Y-m-d') }}" type="date" class="form-control" >
                </div>
                <div class="col-xl-3 col-sm-6 mt-3">
                    <label><i class="bi bi-file-earmark-text text-primary me-1"></i> Purchase Order No</label>
                    <input name="purchase_order_no" type="text" class="form-control">
                </div>
                <div class="col-xl-3 col-sm-6 mt-3">
                    <label><i class="bi bi-receipt text-primary me-1"></i> Vendor</label>
                    {{-- <input name="challan_no" type="text" class="form-control"> --}}
                    <select name="challan_no" class="form-control"  >
                        <option disabled selected>Select One</option>

                        @foreach ($Vendor as $item)
                        <option >{{ $item->name  }}</option>
                            
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 mt-3">
                    <label><i class="bi bi-person-lines-fill text-primary me-1"></i> Supplier</label>
                    <select name="supplier" id="supplierSelect" class="form-control"  >
                        <option disabled selected>Select One</option>
                    </select>
                </div>
                <div class="col-xl-3 col-sm-6 mt-3">
                    <label><i class="bi bi-building text-primary me-1"></i> Warehouse</label>
                    <select name="warehouse_id" class="form-control"  >
                        <option disabled selected>Select One</option>

                        @foreach ($Warehouse as $item)
                        <option >{{ $item->warehouse_name  }}</option>
                            
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-sm-6 mt-3">
                    <label><i class="bi bi-truck text-primary me-1"></i> Deliver Date</label>
                    <input name="order_date" type="date" class="form-control"  >
                </div>
                <div class="col-xl-3 col-sm-6 mt-3">
                    <label><i class="bi bi-box-arrow-in-down text-primary me-1"></i> Received Date</label>
                    <input name="received_date" type="date" class="form-control"  >
                </div>
                <div class="col-xl-12 col-sm-6 mt-3">
                    <label><i class="bi bi-card-text text-primary me-1"></i> Job No & Description</label>
                    <input name="job_description" type="text" class="form-control">
                </div>
            </div>

            <!-- Supplier Info Row -->
            <div class="row mt-4">
            </div>
        </div>
    </div>



                                        <!-- Item Code Table -->
        
        <div style="max-height: 300px; overflow-y: scroll; ">
        <table class="table mt-3 table-bordered">
            <thead>
            <tr class="text-center">
                <th>product</th>
                <th>Item Code</th>
            
                <th>UOM</th>
                <th>Measurement</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Qty</th>

                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody id="purchaseItems" style="max-height: 300px; overflow-y: auto;">
            <tr>
                <td>
                        <input type="text" name="item_name[]" class="form-control productSearch" placeholder="Enter product name...">
                        <ul class="searchResults list-group mt-1"></ul>
                </td>
                <td class="item_code border"><input type="text" name="item_code[]" class="form-control" readonly></td>
                <td class="uom border"><input type="text" name="uom[]" class="form-control" readonly></td>
                <td class="measurement border"><input type="text" name="measurement[]" class="form-control" readonly></td>
                <td class="unit border"><input type="text" name="unit[]" class="form-control" readonly></td>
                <td><input type="text" name="price[]" class="form-control price" value="" readonly></td>
                <td class="qty"><input type="number" name="quantity[]" class="form-control quantity" value="1"></td>
                <td class="total border"><input type="text" name="total[]" class="form-control total" readonly></td>
                <td><button class="btn btn-sm btn-danger remove-row">X</button></td>
            </tr>
        </tbody>

        </table>
        </div>
                                        <div class="container">
                                            <h3>Transport & Vehicle Details</h3>
                                            <hr>

                                            <!-- Row 1 -->
                                                <div class="row mb-3 mt-3">
                                                    <div class="col-md-4">
                                                        <label><i class="fas fa-truck-moving me-1"></i>&nbsp;Transport</label>
                                                        <select name="transport" id="supplierSelect" class="form-control"
                                                            >
                                                            <option disabled selected>Select Transport Company</option>
                                                            {{-- @foreach ($Transports as $Supplier)
                                                                <option value="{{ $Supplier->company_name }}">
                                                                    {{ $Supplier->company_name }}
                                                                </option>
                                                            @endforeach --}}
                                                        </select>
                                                    </div>


                                                <div class="col-md-4">
                                                    <label><i class="fas fa-file-alt me-1"></i>&nbsp;Bilti Number</label>
                                                    <input type="text" name="bilti_number" class="form-control"
                                                        placeholder="Enter Bilti Number">
                                                </div>

                                                <div class="col-md-4">
                                                    <label><i class="fas fa-user me-1"></i>&nbsp;Driver Name</label>
                                                    <input type="text" name="driver_name" class="form-control"
                                                        placeholder="Enter Driver's Name">
                                                </div>
                                            </div>

                                            <!-- Row 2 -->
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label><i class="fas fa-id-badge me-1"></i>&nbsp;Truck Number</label>
                                                    <input type="text" name="truck_no" class="form-control"
                                                        placeholder="e.g. TRK-1234">
                                                </div>

                                                <div class="col-md-4">
                                                    <label><i class="fas fa-phone me-1"></i>&nbsp;Driver Phone</label>
                                                    <input type="text" name="driver_phone" class="form-control"
                                                        placeholder="03XX-XXXXXXX">
                                                </div>

                                                <div class="col-md-4">
                                                    <label><i class="fas fa-info-circle me-1"></i>&nbsp;Vehicle
                                                        Description</label>
                                                    <textarea name="vehicle_description" class="form-control" placeholder="Type vehicle details here..."></textarea>
                                                </div>
                                            </div>
                                        </div>


                                

                                        <button type="submit" class="btn btn-primary w-100 mt-4">Submit Purchase</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- bodywrapper__inner end -->
            </div><!-- body-wrapper end -->
        </div>

        <!-- Warehouse Modal -->
        <div class="modal fade" id="warehouseModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Warehouse</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>

                    <form action="" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Branch</label>
                                <select name="branch_id" class="form-control select2"  >
                                    <option disabled selected>Select Branch</option>
                                    <option value="0">Main Super Admin</option>
                                    {{-- @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control"  >
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" class="form-control" name="address">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary w-100 h-45">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Transport Modal -->
        <div class="modal fade" id="transportModal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Transport</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>

                    <form action="" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Company Name</label>
                                        <input type="text" name="company_name" class="form-control"
                                            autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" autocomplete="off"
                                            >
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Mobile</label>
                                        <input type="number" name="mobile" class="form-control"  >
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" name="address" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary w-100 h-45">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Veondor Modal -->
        <div class="modal fade" id="supplierModal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Supplier</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>

                    <form action="" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" autocomplete="off"
                                            >
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">E-Mail</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Mobile
                                            <i class="fa fa-info-circle text--primary"
                                                title="Type the mobile number including the country code. Otherwise, SMS won't send to that number."></i>
                                        </label>
                                        <input type="number" name="mobile" class="form-control"  >
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Company</label>
                                        <input type="text" name="company_name" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" name="address" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary w-100 h-45">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
        </div>

        @endsection
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        {{-- Success & Error Messages --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                confirmButtonColor: '#3085d6',
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: {!! json_encode(implode('<br>', $errors->all())) !!},
                confirmButtonColor: '#d33',
            });
        </script>
    @endif

    {{-- Cancel Button Confirmation --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cancelBtn = document.getElementById('cancelBtn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function () {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will cancel your changes!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, go back!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '';
                        }
                    });
                });
            }
        });
    </script>

    {{-- Item Row Autocomplete + Add/Remove --}}
    <!-- Make sure jQuery and Bootstrap Typeahead are included -->

    <script>
        $(document).ready(function () {

        // 🔍 Product Search per row
        $(document).on('keyup', '.productSearch', function () {
            const input = $(this);
            const query = input.val();
            const row = input.closest('tr');
            const resultBox = row.find('.searchResults');

            if (query.length > 0) {
                $.ajax({
                    url: "{{ route('search-products') }}",
                    type: 'GET',
                    data: { q: query },
                    success: function (data) {
                        let html = '';
                        data.forEach(product => {
                            html += `
    <li class="list-group-item search-result-item"
        data-product-name="${product.name}"
        data-product-uom="${product.uom}"
        data-product-measurement="${product.measurement}"
        data-product-unit="${product.unit}"
        data-product-code="${product.code}"
        data-price="${product.price}">
        ${product.name} - ${product.code} - Rs. ${product.price}
    </li>`;
                        });
                        resultBox.html(html);
                        input.css('border', '2px solid green');
                    },
                    error: function (xhr) {
                        if (xhr.status === 404) {
                            input.css('border', '2px solid red');
                        }
                        resultBox.html('');
                    }
                });
            } else {
                resultBox.html('');
            }
        });

        // 🧲 Select Product
        $(document).on('click', '.search-result-item', function () {
            const li = $(this);
            const row = li.closest('tr');
    row.find('.item_code input').val(li.data('product-code'));
    row.find('.uom input').val(li.data('product-uom'));
    row.find('.measurement input').val(li.data('product-measurement'));
    row.find('.unit input').val(li.data('product-unit'));
    row.find('.price').val(li.data('price'));
    row.find('.quantity').val(1);
    row.find('.total input').val(li.data('price'));
    row.find('.productSearch').val(li.data('product-name'));


            row.find('.searchResults').html('');

            // ➕ Add new blank row
    const newRow = `
    <tr>
        <td>
            <div class="form-group">
                <input type="text" name="item_name[]" class="form-control productSearch" placeholder="Enter product name...">
                <ul class="list-group mt-1 searchResults"></ul>
            </div>
        </td>
        <td class="item_code border"><input type="text" name="item_code[]" class="form-control" readonly></td>
        <td class="uom border"><input type="text" name="uom[]" class="form-control" readonly></td>
        <td class="measurement border"><input type="text" name="measurement[]" class="form-control" readonly></td>
        <td class="unit border"><input type="text" name="unit[]" class="form-control" readonly></td>
        <td><input type="text" name="price[]" class="form-control price" value="" readonly></td>
        <td class="qty"><input type="number" name="quantity[]" class="form-control quantity" value="1"></td>
        <td class="total border"><input type="text" name="total[]" class="form-control total" readonly></td>
        <td><button class="btn btn-sm btn-danger remove-row">X</button></td>
    </tr>`;

            $('#purchaseItems').append(newRow);
        });

        // ✏️ Total Calculation
        $('#purchaseItems').on('input', '.quantity, .price', function () {
            const row = $(this).closest('tr');
            const qty = parseFloat(row.find('.quantity').val()) || 0;
            const price = parseFloat(row.find('.price').val()) || 0;
            const total = (qty * price).toFixed(2);
            row.find('.total').text(total);
        });

        // ❌ Remove Row
        $('#purchaseItems').on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
        });

    });

    </script>