<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
    use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use Illuminate\Support\Facades\Auth;
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

    $customer = Customer::create($data);

    // Ledger entry: calculate opening balance
    $debit = $request->debit ?? 0;
    $credit = $request->credit ?? 0;

    $closingBalance = $debit - $credit; // Actual net balance
    $userId = Auth::id();

    // Only create if opening entry exists
    if ($debit > 0 || $credit > 0) {
        CustomerLedger::create([
            'customer_id' => $customer->id,
            'admin_or_user_id' => $userId,
            'date' => now(),
            'description' => 'Opening Balance',
            'debit' => $debit,
            'credit' => $credit,
            'previous_balance' => 0,
            'closing_balance' => $closingBalance,
        ]);
    }

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


    // customer ledger start

// Customer Ledger View
public function customer_ledger()
{
    if (Auth::check()) {
        $userId = Auth::id();
        $CustomerLedgers = CustomerLedger::with('customer')
            ->where('admin_or_user_id', $userId)
            ->get();

        return view('admin_panel.customers.customer_ledger', compact('CustomerLedgers'));
    } else {
        return redirect()->back();
    }
}
// customer payment start


// View all customer payments
public function customer_payments()
{
    $payments = CustomerPayment::with('customer')->orderByDesc('id')->get();
    $customers = Customer::all();
    return view('admin_panel.customers.customer_payments', compact('payments', 'customers'));
}

// Store a customer payment
public function store_customer_payment(Request $request)
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => 'nullable|string',
        'payment_date' => 'required|date',
        'note' => 'nullable|string',
    ]);

    $userId = Auth::id();

    // Save in payments table
    $payment = CustomerPayment::create([
        'customer_id' => $request->customer_id,
        'admin_or_user_id' => $userId,
        'amount' => $request->amount,
        'payment_method' => $request->payment_method,
        'payment_date' => $request->payment_date,
        'note' => $request->note,
    ]);

    // Ledger update
    $previous = CustomerLedger::where('customer_id', $request->customer_id)->latest()->first();
    $prevBalance = $previous->closing_balance ?? 0;

    // Prevent over-payment (optional)
    if ($request->amount > $prevBalance) {
        return back()->with('error', 'Amount exceeds available balance.');
    }

    $newClosing = $prevBalance - $request->amount;

    CustomerLedger::create([
        'customer_id' => $request->customer_id,
        'admin_or_user_id' => $userId,
        'date' => $request->payment_date,
        'description' => 'Payment to Customer',
        'debit' => 0,
        'credit' => $request->amount,
        'previous_balance' => $prevBalance,
        'closing_balance' => $newClosing,
    ]);

    return back()->with('success', 'Payment to customer recorded and ledger updated.');
}


}

