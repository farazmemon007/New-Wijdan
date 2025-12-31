@extends('admin_panel.layout.app')
@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <h3>Add New Customer</h3>
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf



                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <label><strong>Customer ID:</strong></label>
                            <input type="text" class="form-control" name="customer_id" readonly value="{{ $latestId }}">
                        </div>
                        <div class="col-md-3">
                            <label><strong>Customer Type :</strong></label>
                            <select class="form-control" name="customer_type">
                                <option>Main Customer</option>
                                <option>Walking Customer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label><strong>Customer:</strong></label>
                            <input type="text" class="form-control" name="customer_name"
                                value="{{ old('customer_name') }}">
                        </div>
                        <div class="col-md-3">
                            <label>NTN / CNIC no:</label>
                            <input type="text" class="form-control" name="cnic" value="{{ old('cnic') }}">

                        </div>


                        <div class="row mb-3">

                            <div class="col-md-3 ">
                                <label>Filer Type:</label>
                                <select class="form-control" name="filer_type">
                                    <option value="filer">Filer</option>
                                    <option value="non filer">Non Filer</option>
                                    <option value="exempt">Exempt</option>
                                </select>
                            </div>

                            <div class="col-md-3 ">
                                <label>Mobile:</label>
                                <input type="text" class="form-control" name="mobile_2" value="{{ old('mobile_2') }}">
                            </div>

                             <div class="col-md-3">
                                <label>Opening balance (Dr):</label>
                                <input type="number" class="form-control" name="opening_balance"
                                    value="{{ old('opening_balance') }}">
                            </div>


                             <div class="col-md-3 mb-4">
                            <label>Address:</label>
                            <textarea rows="1" class="form-control" name="address">{{ old('address') }}</textarea>
                        </div>
                            
              
                        </div>

             

                        

                        <div class="row mb-4">
                           
                            {{--  <div class="col-md-6">
                <label>Credit (Cr):</label>
                <input type="number" class="form-control" name="credit" value="{{ old('credit') }}">
            </div>  --}}
                        </div>

                       

                        <div class="text-center">
                            <button class="btn btn-success" type="submit">Save Customer</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </div>
@endsection
