@extends('admin_panel.layout.app')

@section('content')

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">

            <div class="page-header row">
                <div class="page-title col-lg-6">
                    <h4>Vendor List</h4>
                    <h6>Manage Vendors</h6>
                </div>
                <div class="page-btn d-flex justify-content-end col-lg-6">
                    <button class="btn btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#vendorModal" onclick="clearVendor()">Add Vendor</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success"><strong>Success!</strong> {{ session('success') }}</div>
                    @endif

                    <table class="table datanew">
                        <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Address</th><th>Action</th></tr></thead>
                        <tbody>
                            @foreach($vendors as $key => $v)
                            <tr>
                                <td>{{ $key+1 }}</td><td>{{ $v->name }}</td><td>{{ $v->phone }}</td><td>{{ $v->address }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#vendorModal" onclick="editVendor('{{ $v->id }}','{{ $v->name }}','{{ $v->phone }}','{{ $v->address }}')">Edit</button>
                                    <a href="{{ url('vendor/delete/'.$v->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
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

<div class="modal fade" id="vendorModal">
    <div class="modal-dialog">
        <form action="{{ url('vendor/store') }}" method="POST">@csrf
            <input type="hidden" name="id" id="vendor_id">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add/Edit Vendor</h5></div>
                <div class="modal-body">
                    <div class="mb-2"><input class="form-control" name="name" id="vname" placeholder="Name" required></div>
                    <div class="mb-2"><input class="form-control" name="phone" id="vphone" placeholder="Phone"></div>
                    <div class="mb-2"><textarea class="form-control" name="address" id="vaddress" placeholder="Address"></textarea></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function clearVendor(){ $('#vendor_id').val(''); $('#vname').val(''); $('#vphone').val(''); $('#vaddress').val(''); }
function editVendor(id,name,phone,address){ $('#vendor_id').val(id); $('#vname').val(name); $('#vphone').val(phone); $('#vaddress').val(address); }
$('.datanew').DataTable();
</script>
@endpush