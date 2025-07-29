<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
{
    $customers = Customer::latest()->get(); // no status filter
    return view('admin_panel.customers.index', compact('customers'));
}

public function toggleStatus($id)
{
    $customer = Customer::findOrFail($id);
    $customer->status = $customer->status === 'active' ? 'inactive' : 'active';
    $customer->save();

    return redirect()->back()->with('success', 'Customer status updated.');
}


    public function markInactive($id)
{
    $customer = Customer::findOrFail($id);
    $customer->status = 'inactive';
    $customer->save();

    return redirect()->route('customers.index')->with('success', 'Customer marked as inactive.');
}

    public function inactiveCustomers()
{
    $customers = Customer::where('status', 'inactive')->latest()->get();
    return view('admin_panel.customers.inactive', compact('customers'));
}

    public function create()
    {
        $latestId = 'CUST-' . str_pad(Customer::max('id') + 1, 4, '0', STR_PAD_LEFT);
        return view('admin_panel.customers.create', compact('latestId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|unique:customers',
            'customer_name' => 'nullable',
            'customer_name_ur' => 'nullable',
            'cnic' => 'nullable',
            'filer_type' => 'nullable',
            'zone' => 'nullable',
            'contact_person' => 'nullable',
            'mobile' => 'nullable',
            'email_address' => 'nullable|email',
            'contact_person_2' => 'nullable',
            'mobile_2' => 'nullable',
            'email_address_2' => 'nullable|email',
            'debit' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'address' => 'nullable',
        ]);

        Customer::create($data);
        
        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin_panel.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $data = $request->except('_token');

        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}

