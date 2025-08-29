@extends('admin_panel.layout.app')

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Inward Gatepasses</h3>
                            <a class="btn btn-primary" href="{{ route('add_inwardgatepass') }}">Add Inward Gatepass</a>
                        </div>

                        <div class="border mt-1 shadow rounded" style="background-color: white;">
                            <div class="col-lg-12 m-auto">
                                <div class="table-responsive mt-5 mb-5">
                                    <table id="gatepass-table" class="table">
                                        <thead class="text-center" style="background:#add8e6">
                                            <tr>
                                                <th>ID</th>
                                                <th>Branch</th>
                                                <th>Warehouse</th>
                                                <th>Vendor</th>
                                                <th>Date</th>
                                                <th>Note</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            @foreach ($gatepasses as $gp)
                                                <tr>
                                                    <td>{{ $gp->id }}</td>
                                                    <td>{{ $gp->branch->name ?? 'N/A' }}</td>
                                                    <td>{{ $gp->warehouse->warehouse_name ?? 'N/A' }}</td>
                                                    <td>{{ $gp->vendor->name ?? 'N/A' }}</td>
                                                    <td>{{ $gp->gatepass_date }}</td>
                                                    <td>{{ $gp->note }}</td>
                                                    <td>
                                                        <a href="{{ route('InwardGatepass.show', $gp->id) }}"
                                                            class="btn btn-sm btn-info">
                                                            View
                                                        </a>
                                                        <a href="{{ route('InwardGatepass.edit', $gp->id) }}"
                                                            class="btn btn-sm" style="background:#add8e6">
                                                            Edit
                                                        </a>

                                                        <form action="{{ route('InwardGatepass.destroy', $gp->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-danger btn-sm delete-btn">
                                                                Delete
                                                            </button>
                                                        </form>

                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- datatable --}}
                        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
                        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
                        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

                        <script>
                            $(document).ready(function() {
                                $('#gatepass-table').DataTable({
                                    "pageLength": 10,
                                    "lengthMenu": [5, 10, 25, 50, 100],
                                    "order": [
                                        [0, 'desc']
                                    ],
                                    "language": {
                                        "search": "Search Gatepass:",
                                        "lengthMenu": "Show _MENU_ entries"
                                    }
                                });
                            });
                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- SweetAlert --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // delete confirm
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to delete this gatepass!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // success alert after delete
    @if(session('success'))
        Swal.fire({
            title: 'Deleted!',
            text: "{{ session('success') }}",
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    @endif
</script>
