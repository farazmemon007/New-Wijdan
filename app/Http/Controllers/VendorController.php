<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorLedger;
use App\Models\VendorPayment;
use App\Models\VendorBilty;
use App\Models\Purchase;
class VendorController extends Controller
{
    // VendorController.php aur WarehouseController.php same hoga
public function index() {
    $vendors = Vendor::all(); // ya $warehouses = Warehouse::all();
    return view('admin_panel.vendors.index', compact('vendors')); // ya warehouses.index
}

public function store(Request $request)
{
    if ($request->id) {
        Vendor::findOrFail($request->id)->update($request->except('opening_balance')); // prevent balance update
    } else {
        $vendor = Vendor::create($request->all());

        // Ledger entry
        VendorLedger::create([
            'vendor_id' => $vendor->id,
            'admin_or_user_id' => Auth::id(),
            'opening_balance' => $request->opening_balance ?? 0,
            'closing_balance' => $request->opening_balance ?? 0,
        ]);
    }

    return back()->with('success', 'Saved Successfully');
}


public function delete($id) {
    Vendor::findOrFail($id)->delete();
    return back()->with('success', 'Deleted Successfully');
}
public function vendors_ledger()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $VendorLedgers = VendorLedger::where('admin_or_user_id',$userId)->with('vendor')->get();
            return view('admin_panel.vendors.vendors_ledger', compact('VendorLedgers'));
        } else {
            return redirect()->back();
        }
    }
// vendor payment store and view start ................

// Show vendor payments
public function vendor_payments()
{
    $userId = Auth::id();
    $payments = VendorPayment::with('vendor')
        ->where('admin_or_user_id', $userId)
        ->orderByDesc('payment_date')
        ->get();

    $vendors = Vendor::all();
    return view('admin_panel.vendors.vendor_payments', compact('payments', 'vendors'));
}

// Store vendor payment
public function store_vendor_payment(Request $request)
{
    $request->validate([
        'vendor_id' => 'required|exists:vendors,id',
        'payment_date' => 'required|date',
        'amount' => 'required|numeric|min:0',
        'payment_method' => 'nullable|string',
        'note' => 'nullable|string',
        'adjustment_type' => 'required|in:plus,minus',
    ]);

    // Save the payment
    VendorPayment::create([
        'vendor_id' => $request->vendor_id,
        'admin_or_user_id' => Auth::id(),
        'payment_date' => $request->payment_date,
        'amount' => $request->amount,
        'payment_method' => $request->payment_method,
        'note' => $request->note,
    ]);

    // Update ledger
    $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->first();

    if ($ledger) {
        if ($request->adjustment_type == 'minus') {
            $ledger->closing_balance -= $request->amount;
        } else {
            $ledger->closing_balance += $request->amount;
        }
        $ledger->save();
    }

    return redirect()->back()->with('success', 'Vendor payment recorded.');
}

// end payments vendor

// vendor bilty statr  

// Show all bilties
public function vendor_bilties()
{
    $bilties = VendorBilty::with(['vendor', 'purchase'])->orderByDesc('id')->get();
    $vendors = Vendor::all();
    $purchases = Purchase::all();
    return view('admin_panel.vendors.vendor_bilties', compact('bilties', 'vendors', 'purchases'));
}

// Store bilty
public function store_vendor_bilty(Request $request)
{
    $request->validate([
        'vendor_id' => 'required|exists:vendors,id',
        'purchase_id' => 'nullable|exists:purchases,id',
        'bilty_no' => 'nullable|string',
        'vehicle_no' => 'nullable|string',
        'transporter_name' => 'nullable|string',
        'delivery_date' => 'nullable|date',
        'note' => 'nullable|string',
    ]);

    VendorBilty::create($request->all());

    return back()->with('success', 'Vendor bilty saved successfully.');
}
public function getVendorBalance($id)
{
    $ledger = VendorLedger::where('vendor_id', $id)->first();

    return response()->json([
        'closing_balance' => $ledger ? $ledger->closing_balance : 0
    ]);
}

}
