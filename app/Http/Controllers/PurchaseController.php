<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\PurchaseItem;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use App\Models\VendorLedger;
use App\Models\Inwardgatepass;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{ 
        public function index()
    {
        $Purchase = Purchase::with(['branch', 'warehouse', 'vendor', 'items'])->get();
        return view("admin_panel.purchase.index", compact('Purchase'));
    }
public function addBill($gatepassId)
{
    // Fetch the gatepass along with its related items and products
    $gatepass = InwardGatepass::with('items.product')->findOrFail($gatepassId);

    // Pass the gatepass data to the view
    return view('admin_panel.inward.add_bill', compact('gatepass'));
}

      public function add_purchase()
    {
        // $userId = Auth::id();
      $Purchase = Purchase::get();
      $Vendor = Vendor::get();
      $Warehouse = Warehouse::get();
         return view('admin_panel.purchase.add_purchase',compact('Vendor',"Warehouse",'Purchase'));
    }
public function store(Request $request, $gatepassId = null)
{
    // Agar gatepass ID nahi hai, tou direct purchase karenge
    if ($gatepassId) {
        // Fetch the gatepass and check if a bill is already linked
        $gatepass = InwardGatepass::with('purchase')->findOrFail($gatepassId);

        // Agar gatepass pe already purchase hai, tou error return karenge
        if ($gatepass->purchase) {
            return back()->with('error', 'This gatepass already has an associated bill.');
        }
    } else {
        // Agar gatepass nahi hai, tou direct purchase karenge
        $gatepass = null; // No gatepass for direct purchase
    }

    // Validate the incoming request data
    $validated = $request->validate([
        'invoice_no'      => 'nullable|string',
        'vendor_id'        => 'nullable|exists:vendors,id',
        'purchase_date'    => 'nullable|date',
        'warehouse_id'     => 'nullable|exists:warehouses,id',
        'note'             => 'nullable|string',
        'discount'         => 'nullable|numeric|min:0',
        'extra_cost'       => 'nullable|numeric|min:0',
        'product_id'       => 'required|array',
        'product_id.*'     => 'required|exists:products,id',
        'qty'              => 'required|array',
        'qty.*'            => 'required|numeric|min:1',
        'price'            => 'required|array',
        'price.*'          => 'required|numeric|min:0',
        'unit'             => 'required|array',
        'unit.*'           => 'required|string',
        'item_discount'    => 'nullable|array', // Update here to nullable
        'item_discount.*'  => 'nullable|numeric|min:0', // Update here to nullable
    ]);

    DB::transaction(function () use ($validated, $request, $gatepass) {
        // Generate the next invoice number
        $lastInvoice = Purchase::latest()->value('invoice_no');
        $nextInvoice = $lastInvoice 
            ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
            : 'INV-00001';

        // Create a new purchase record
        $purchase = Purchase::create([
            'branch_id'       => auth()->user()->id,
            'warehouse_id'    => $validated['warehouse_id'],
            'vendor_id'       => $validated['vendor_id'],
            'purchase_date'   => $validated['purchase_date'] ?? now(),
            'invoice_no'      => $validated['invoice_no'] ?? $nextInvoice,
            'note'             => $validated['note'] ?? null,
            'subtotal'        => 0,
            'discount'        => 0,
            'extra_cost'      => 0,
            'net_amount'      => 0,
            'paid_amount'     => 0,
            'due_amount'      => 0,
        ]);

        $subtotal = 0;

        // Store the purchase items and update stock
        foreach ($validated['product_id'] as $index => $productId) {
            $qty = $validated['qty'][$index] ?? 0;  
            $price = $validated['price'][$index] ?? 0;  

            if (empty($productId) || empty($qty) || empty($price)) {
                continue;
            }

            $disc = $validated['item_discount'][$index] ?? 0;
            $unit = $validated['unit'][$index] ?? null;
            $lineTotal = ($price * $qty) - $disc;

            PurchaseItem::create([
                'purchase_id'     => $purchase->id,
                'product_id'      => $productId,
                'unit'            => $unit,
                'price'           => $price,
                'item_discount'   => $disc,
                'qty'             => $qty,
                'line_total'      => $lineTotal,
            ]);

            $subtotal += $lineTotal;

            // Update stock or create new stock entry
            $stock = Stock::where('branch_id', auth()->user()->id)
                ->where('warehouse_id', $validated['warehouse_id'])
                ->where('product_id', $productId)
                ->first();

            if ($stock) {
                $stock->qty += $qty;
                $stock->save();
            } else {
                Stock::create([
                    'branch_id'       => auth()->user()->id,
                    'warehouse_id'    => $validated['warehouse_id'],
                    'product_id'      => $productId,
                    'qty'             => $qty,
                ]);
            }
        }

        // Final calculations
        $discount = $request->discount ?? 0;
        $extraCost = $request->extra_cost ?? 0;
        $netAmount = ($subtotal - $discount) + $extraCost;

        // Update the purchase totals
        $purchase->update([
            'subtotal'    => $subtotal,
            'discount'    => $discount,
            'extra_cost'  => $extraCost,
            'net_amount'  => $netAmount,
            'due_amount'  => $netAmount,
        ]);

        // Vendor Ledger Update
        $previousLedger = VendorLedger::where('vendor_id', $validated['vendor_id'])->first();
        $openingBalance = $previousLedger ? $previousLedger->closing_balance : 0;
        $newClosingBalance = $openingBalance + $netAmount;

        VendorLedger::updateOrCreate(
            ['vendor_id' => $validated['vendor_id']],
            [
                'vendor_id'         => $validated['vendor_id'],
                'admin_or_user_id'  => auth()->id(),
                'previous_balance'  => $subtotal,
                'closing_balance'   => $newClosingBalance,
                'opening_balance'   => $openingBalance,
            ]
        );

        // Link the gatepass to the purchase if provided
        if ($gatepass) {
            $gatepass->purchase_id = $purchase->id;
            $gatepass->status = 'linked'; 
            $gatepass->save();
        }
    });

    // Redirect with success message
    return redirect()->route('InwardGatepass.home')->with('success', 'Bill has been successfully added and the gatepass is now linked.');
}






// public function store(Request $request)
// {
//     // ✅ Validation
//     $validated = $request->validate([
//         'invoice_no'     => 'nullable|string',
//         'vendor_id'      => 'nullable|exists:vendors,id',
//         'purchase_date'  => 'nullable|date',
//         'warehouse_id'   => 'nullable|exists:warehouses,id',
//         'note'           => 'nullable|string',
//         'discount'       => 'nullable|numeric|min:0',
//         'extra_cost'     => 'nullable|numeric|min:0',

//         // Purchase Items
//         'product_id'       => 'nullable|array',
//         'product_id.*'     => 'nullable|exists:products,id',
//         'qty'              => 'nullable|array',
//         'qty.*'            => 'nullable|numeric|min:1',
//         'price'            => 'nullable|array',
//         'price.*'          => 'nullable|numeric|min:0',
//         'unit'             => 'nullable|array',
//         'unit.*'           => 'nullable|string',
//         'item_discount'    => 'nullable|array',
//         'item_discount.*'  => 'nullable|numeric|min:0',
//     ]);

//     DB::transaction(function () use ($validated, $request) {

//         // 🧾 Generate Next Invoice No
//         $lastInvoice = Purchase::latest()->value('invoice_no');
//         $nextInvoice = $lastInvoice 
//             ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
//             : 'INV-00001';

//         // ✍️ Create Purchase with temporary values
//         $purchase = Purchase::create([
//             'branch_id'     => auth()->user()->id,
//             'warehouse_id'  => $validated['warehouse_id'],
//             'vendor_id'     => $validated['vendor_id'] ?? null,
//             'purchase_date' => $validated['purchase_date'] ?? now(),
//             'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
//             'note'          => $validated['note'] ?? null,
//             'subtotal'      => 0,
//             'discount'      => 0,
//             'extra_cost'    => 0,
//             'net_amount'    => 0,
//             'paid_amount'   => 0,
//             'due_amount'    => 0,
//         ]);

//         $subtotal = 0;

//         // 🧾 Purchase Items
//         $productIds = $validated['product_id'] ?? [];
//         foreach ($productIds as $index => $productId) {
//             $qty   = $validated['qty'][$index] ?? null;
//             $price = $validated['price'][$index] ?? null;

//             if (empty($productId) || empty($qty) || empty($price)) {
//                 continue;
//             }

//             $disc = $validated['item_discount'][$index] ?? 0; // ✅ Correct name
//             $unit = $validated['unit'][$index] ?? null;

//             $lineTotal = ($price * $qty) - $disc;

//             PurchaseItem::create([
//                 'purchase_id'   => $purchase->id,
//                 'product_id'    => $productId,
//                 'unit'          => $unit,
//                 'price'         => $price,
//                 'item_discount' => $disc,
//                 'qty'           => $qty,
//                 'line_total'    => $lineTotal,
//             ]);

//             $subtotal += $lineTotal;

//             // 📦 Update Stock
//             $stock = Stock::where('branch_id', auth()->user()->id)
//                 ->where('warehouse_id', $validated['warehouse_id'])
//                 ->where('product_id', $productId)
//                 ->first();

//             if ($stock) {
//                 $stock->qty += $qty;
//                 $stock->save();
//             } else {
//                 Stock::create([
//                     'branch_id'     => auth()->user()->id,
//                     'warehouse_id'  => $validated['warehouse_id'],
//                     'product_id'    => $productId,
//                     'qty'           => $qty,
//                 ]);
//             }
//         }

//         // 💵 Final Calculations (use values from request safely)
//         $discount   = $request->discount ?? 0;
//         $extraCost  = $request->extra_cost ?? 0;
//         $netAmount  = ($subtotal - $discount) + $extraCost;

//         $purchase->update([
//             'subtotal'    => $subtotal,
//             'discount'    => $discount,
//             'extra_cost'  => $extraCost,
//             'net_amount'  => $netAmount,
//             'due_amount'  => $netAmount,
//         ]);

//         // 📘 Vendor Ledger Update
//         $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
//             ->value('closing_balance') ?? 0;

//         $newClosingBalance = $previousBalance + $netAmount;

//         VendorLedger::updateOrCreate(
//             ['vendor_id' => $validated['vendor_id']],
//             [
//                 'vendor_id'         => $validated['vendor_id'],
//                 'admin_or_user_id'  => auth()->id(),
//                 'previous_balance'  => $subtotal,
//                 'closing_balance'   => $newClosingBalance,
//             ]
//         );
//     });

//     return back()->with('success', 'Purchase saved successfully!');
// }


    // public function store(Request $request)
    // {
        
//         $validated = $request->validate([
//             'invoice_no'     => 'nullable|string',
//             'vendor_id'      => 'nullable|exists:vendors,id',
//             // 'branch_id'      => 'required|exists:branches,id',
//             'purchase_date'  => 'nullable|date',
//             'warehouse_id'   => 'nullable|exists:warehouses,id',
//             'note'           => 'nullable|string',
//     'discount'       => 'nullable|numeric|min:0',
//     'extra_cost'     => 'nullable|numeric|min:0',

//             // Purchase Items
//             'product_id'     => 'nullable|array',
//             'product_id.*'   => 'nullable|exists:products,id',
//             'qty'            => 'nullable|array',
//             'qty.*'          => 'nullable|numeric|min:1',
//             'price'          => 'nullable|array',
//             'price.*'        => 'nullable|numeric|min:0',
//             'unit'           => 'nullable|array',
//             'unit.*'         => 'nullable|string',
//             'item_discount'  => 'nullable|array',
//             'item_discount.*'=> 'nullable|numeric|min:0',
//         ]);
// DB::transaction(function () use ($validated) {

//     $lastInvoice = Purchase::latest()->value('invoice_no');

//     $nextInvoice = $lastInvoice 
//         ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
//         : 'INV-00001';

//     // 1️⃣ Create purchase
//     $purchase = Purchase::create([
//         'branch_id'     => Auth()->user()->id,
//         'warehouse_id'  => $validated['warehouse_id'],
//         'vendor_id'     => $validated['vendor_id'] ?? null,
//         'purchase_date' => $validated['purchase_date'] ?? now(),
//         'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
//         'note'          => $validated['note'] ?? null,
//         'subtotal'      => $validated['subtotal'] ?? 0,
//         'discount'      => $validated['discount'] ?? 0,
//         'extra_cost'    => $validated['extra_cost'] ?? 0,
//         'net_amount'    => $validated['net_amount'] ?? 0,
//         'paid_amount'   => 0,
//         'due_amount'    => 0,
        
//     ]);

//     $subtotal = 0;

//     // 2️⃣ Loop & filter rows
//     $productIds = $validated['product_id'] ?? [];
//     foreach ($productIds as $index => $productId) {
//         $qty   = $validated['qty'][$index] ?? null;
//         $price = $validated['price'][$index] ?? null;

//         // Skip row if any essential field is empty
//         if (empty($productId) || empty($qty) || empty($price)) {
//             continue;
//         }

//         $disc = $validated['item_disc'][$index] ?? 0;
//         $unit = $validated['unit'][$index] ?? null;

//         $lineTotal = ($price * $qty) - $disc;

//         // Save item
//         PurchaseItem::create([
//             'purchase_id'   => $purchase->id,
//             'product_id'    => $productId,
//             'unit'          => $unit,
//             'price'         => $price,
//             'item_discount' => $disc,
//             'qty'           => $qty,
//             'line_total'    => $lineTotal,
//         ]);

//         $subtotal += $lineTotal;

//         // 3️⃣ Update stock
//         $stock = Stock::where('branch_id', Auth()->user()->id)
//             ->where('warehouse_id', $validated['warehouse_id'])
//             ->where('product_id', $productId)
//             ->first();

//         if ($stock) {
//             $stock->qty += $qty;
//             $stock->save();
//         } else {
//             Stock::create([
//                 'branch_id'     => Auth()->user()->id,
//                 'warehouse_id'  => $validated['warehouse_id'],
//                 'product_id'    => $productId,
//                 'qty'           => $qty,
//             ]);
//         }
//     }

//     // 4️⃣ Update totals
//     $purchase->update([
//         'subtotal'    => $subtotal,
//         'net_amount'  => $subtotal,
//         'due_amount'  => $subtotal,
//     ]);

//     // 5️⃣ Vendor ledger
//     $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
//         ->value('closing_balance') ?? 0;

//     $newClosingBalance = $previousBalance + $subtotal;

//     VendorLedger::updateOrCreate(
//         ['vendor_id' => $validated['vendor_id']],
//         [
//             'vendor_id' => $validated['vendor_id'],
//             'admin_or_user_id' => Auth::id(),
//             'previous_balance' => $subtotal,
//             'closing_balance' => $newClosingBalance,
//         ]
//     );

// });

        // // DB::transaction(function () use ($validated) {
            
        // // $lastInvoice = Purchase::latest()->value('invoice_no');

        // // // Agar last invoice mila to +1 karo, warna start karo INV-00001
        // // $nextInvoice = $lastInvoice 
        // //     ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
        // //     : 'INV-00001';
            
        // //     // 1️⃣ Save main Purchase
        // //     $purchase = Purchase::create([
                
        // //         'branch_id'     => Auth()->user()->id,
        // //         'warehouse_id'  => $validated['warehouse_id'],
        // //         'vendor_id'     => $validated['vendor_id'] ?? null,
        // //         'purchase_date' => $validated['purchase_date'] ?? now(),
        // //         'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
        // //         'note'          => $validated['note'] ?? null,
        // //         'subtotal'      => 0,
        // //         'discount'      => 0,
        // //         'extra_cost'    => 0,
        // //         'net_amount'    => 0,
        // //         'paid_amount'   => 0,
        // //         'due_amount'    => 0,
        // //     ]);

        // //     $subtotal = 0;

        // //     // 2️⃣ Loop purchase items
        // //     foreach ($validated['product_id'] as $index => $productId) {
        // //         $qty     = $validated['qty'][$index];
        // //         $price   = $validated['price'][$index];
        // //         $disc    = $validated['item_discount'][$index] ?? 0;
        // //         $lineTotal = ($price * $qty) - $disc;

        // //         // Save purchase item
        // //         PurchaseItem::create([
        // //             'purchase_id'   => $purchase->id,
        // //             'product_id'    => $productId,
        // //             'unit'          => $validated['unit'][$index] ?? null,
        // //             'price'         => $price,
        // //             'item_discount' => $disc,
        // //             'qty'           => $qty,
        // //             'line_total'    => $lineTotal,
        // //         ]);

        // //         $subtotal += $lineTotal;

        // //         // 3️⃣ Update stock
        // //         $stock = Stock::where('branch_id',  Auth()->user()->id,)
        // //             ->where('warehouse_id', $validated['warehouse_id'])
        // //             ->where('product_id', $productId)
        // //             ->first();

        // //         if ($stock) {
        // //             $stock->qty += $qty;
        // //             $stock->save();
        // //         } else {
        // //             Stock::create([
        // //                 'branch_id'     => Auth()->user()->id,
        // //                 'warehouse_id'  => $validated['warehouse_id'],
        // //                 'product_id'    => $productId,
        // //                 'qty'           => $qty,
        // //             ]);
        // //         }
        // //     }

        // //     // 4️⃣ Update totals in purchase
        // //     $purchase->update([
        // //         'subtotal'    => $subtotal,
        // //         'net_amount'  => $subtotal,
        // //         'due_amount'  => $subtotal,
        // //     ]);

        // //     $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
        // //         ->value('closing_balance') ?? 0; // If no previous balance, start from 0
        // //     // Calculate new balances

        // //     $newPreviousBalance = $subtotal;

        // //     $newClosingBalance = $previousBalance + $subtotal;
        // //     $userId = Auth::id();

        // //     // Update or create distributor ledger
        // //     VendorLedger::updateOrCreate(
        // //         ['vendor_id' => $validated['vendor_id']],
        // //         [
        // //             'vendor_id' => $validated['vendor_id'],
        // //             'admin_or_user_id' => $userId,
        // //             'previous_balance' => $newPreviousBalance,
        // //             'closing_balance' => $newClosingBalance,
        // //         ]
        // //     );

        // });

    //     return redirect()->back()->with('success', 'Purchase saved successfully!');
    // }


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
