<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Warehouse;

class PurchaseController extends Controller
{  public function index()
    {
        // $userId = Auth::id();
      $Purchase = Purchase::get();
      return  view("admin_panel.purchase.index",compact('Purchase'));
    }
      public function add_purchase()
    {
        // $userId = Auth::id();
      $Purchase = Purchase::get();
      $Vendor = Vendor::get();
      $Warehouse = Warehouse::get();
         return view('admin_panel.purchase.add_purchase',compact('Vendor',"Warehouse",'Purchase'));
    }
public function store(Request $request)
{
    $validated = $request->validate([
        'invoice_no' => 'nullable',
        'supplier' => 'nullable',
        'purchase_date' => 'nullable',
        'warehouse_id' => 'nullable',
        'item_category' => 'nullable',

        'item_name' => 'nullable|array',
        'quantity' => 'nullable|array',
        'price' => 'nullable|array',
        'unit' => 'nullable|array',

        'total' => 'nullable',
        'note' => 'nullable',
        'total_price' => 'nullable',
        'discount' => 'nullable',
        'Payable_amount' => 'nullable',
        'paid_amount' => 'nullable',
        'due_amount' => 'nullable',
        'status' => 'nullable',
        'is_return' => 'nullable',
    ]);

    Purchase::create([
        'invoice_no' => $validated['invoice_no'] ?? null,
        'supplier' => $validated['supplier'] ?? null,
        'purchase_date' => $validated['purchase_date'] ?? null,
        'warehouse_id' => $validated['warehouse_id'] ?? null,
        'item_category' => $validated['item_category'] ?? null,

        'item_name' => json_encode($validated['item_name'] ?? []),
        'quantity' => json_encode($validated['quantity'] ?? []),
        'price' => json_encode($validated['price'] ?? []),
        'unit' => json_encode($validated['unit'] ?? []),
'total' => json_encode($validated['total'] ?? []),

        'note' => $validated['note'] ?? null,
        'total_price' => $validated['total_price'] ?? null,
        'discount' => $validated['discount'] ?? null,
        'Payable_amount' => $validated['Payable_amount'] ?? null,
        'paid_amount' => $validated['paid_amount'] ?? null,
        'due_amount' => $validated['due_amount'] ?? null,
        'status' => $validated['status'] ?? null,
        'is_return' => $validated['is_return'] ?? null,
    ]);

    return redirect()->back()->with('success', 'Purchase record saved successfully!');
}


public function edit($id) {
  $purchase   = Purchase::findOrFail($id);
  $Vendor     = Vendor::all();
  $Warehouse  = Warehouse::all();
//   $Transport  = Transport::all();
  return view('admin_panel.purchase.edit', compact('purchase','Vendor','Warehouse'));
}

public function update(Request $request, $id)
{
    $validated = $request->validate([
        'invoice_no' => 'nullable',
        'supplier' => 'nullable',
        'purchase_date' => 'nullable',
        'warehouse_id' => 'nullable',
        'item_category' => 'nullable',
        'item_name' => 'nullable|array',
        'quantity' => 'nullable|array',
        'price' => 'nullable|array',
        'unit' => 'nullable|array',
        'total' => 'nullable|array',
        'note' => 'nullable',
        'total_price' => 'nullable',
        'discount' => 'nullable',
        'Payable_amount' => 'nullable',
        'paid_amount' => 'nullable',
        'due_amount' => 'nullable',
        'status' => 'nullable',
        'is_return' => 'nullable',
    ]);

    $purchase = Purchase::findOrFail($id);

    $purchase->update([
        'invoice_no' => $validated['invoice_no'] ?? null,
        'supplier' => $validated['supplier'] ?? null,
        'purchase_date' => $validated['purchase_date'] ?? null,
        'warehouse_id' => $validated['warehouse_id'] ?? null,
        'item_category' => $validated['item_category'] ?? null,

        'item_name' => json_encode($validated['item_name'] ?? []),
        'quantity' => json_encode($validated['quantity'] ?? []),
        'price' => json_encode($validated['price'] ?? []),
        'unit' => json_encode($validated['unit'] ?? []),
        'total' => json_encode($validated['total'] ?? []),

        'note' => $validated['note'] ?? null,
        'total_price' => $validated['total_price'] ?? null,
        'discount' => $validated['discount'] ?? null,
        'Payable_amount' => $validated['Payable_amount'] ?? null,
        'paid_amount' => $validated['paid_amount'] ?? null,
        'due_amount' => $validated['due_amount'] ?? null,
        'status' => $validated['status'] ?? null,
        'is_return' => $validated['is_return'] ?? null,
    ]);

    return redirect()->route('Purchase.home')->with('success', 'Purchase updated successfully!');
}

public function destroy($id)
{
    $purchase = Purchase::findOrFail($id);
    $purchase->delete();

    return redirect()->back()->with('success', 'Purchase deleted successfully.');
}

}
