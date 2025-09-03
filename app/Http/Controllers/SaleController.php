<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SalesReturn;
use Illuminate\Http\Request;

use App\Models\CustomerLedger;
use App\Models\ProductBooking;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
 public function index()
{
    $sales = Sale::with(['customer_relation', 'product_relation'])->get();
    return view('admin_panel.sale.index', compact('sales'));
}

    public function addsale()
    {
        
    $products = Product::get();
    $Customer = Customer::get();
        return view('admin_panel.sale.add_sale',compact('products','Customer'));
    }

public function searchpname(Request $request)
{
    $q = $request->get('q');

    $products = Product::with(['brand']) // only products with active discount
        ->where(function ($query) use ($q) {
            $query->where('item_name', 'like', "%{$q}%")
                  ->orWhere('item_code', 'like', "%{$q}%")
                  ->orWhere('barcode_path', 'like', "%{$q}%");
        })
        ->get();

    return response()->json($products);
}

// public function searchpname(Request $request)
// {
//     $q = $request->get('q');

//     $products = Product::with(['brand', 'activeDiscount'])
//         ->whereHas('activeDiscount') // only products with active discount
//         ->where(function ($query) use ($q) {
//             $query->where('item_name', 'like', "%{$q}%")
//                   ->orWhere('item_code', 'like', "%{$q}%")
//                   ->orWhere('barcode_path', 'like', "%{$q}%");
//         })
//         ->get();

//     return response()->json($products);
// }







// public function store(Request $request)
// {
//     dd($request->all());
//     DB::beginTransaction();

//     try {
//         $product_ids    = $request->product_id;
//         $product_names  = $request->product_id;
//         $product_codes  = $request->item_code;
//         $brands         = $request->uom;
//         $units          = $request->unit;
//         $prices         = $request->price;
//         $discounts      = $request->item_disc;
//         $quantities     = $request->qty;
//         $totals         = $request->total;

//         $combined_products = [];
//         $combined_codes    = [];
//         $combined_brands   = [];
//         $combined_units    = [];
//         $combined_prices   = [];
//         $combined_discounts= [];
//         $combined_qtys     = [];
//         $combined_totals   = [];
//         $combined_colors = [];

//         $total_items = 0;

//        foreach ($product_ids as $index => $product_id) {
//     $qty   = $quantities[$index] ?? 0;
//     $price = $prices[$index] ?? 0;

//     // skip if invalid row
//     if (!$product_id || !$qty || !$price) {
//         continue;
//     }

//     $combined_products[]   = $product_names[$index] ?? '';
//     $combined_codes[]      = $product_codes[$index] ?? '';
//     $combined_brands[]     = $brands[$index] ?? '';
//     $combined_units[]      = $units[$index] ?? '';
//     $combined_prices[]     = $prices[$index] ?? 0;
//     $combined_discounts[]  = $discounts[$index] ?? 0;
//     $combined_qtys[]       = $quantities[$index] ?? 0;
//     $combined_totals[]     = $totals[$index] ?? 0;

//     // ⭐ Get colors for this product and convert to JSON
//     $color = $request->color[$index] ?? [];
//     $combined_colors[] = json_encode($color);

//     // update stock
//     $stock = Stock::where('product_id', $product_id)->first();
//     if ($stock) {
//         if ($stock->qty < $qty) {
//             throw new \Exception("Not enough stock for product: " . $product_names[$index]);
//         }
//         $stock->qty -= $qty;
//         $stock->save();
//     } else {
//         throw new \Exception("Stock record not found for product ID: " . $product_id);
//     }

//     $total_items += $qty;
// }



//         // Save Sale
//         $sale = new Sale();
//         $sale->customer         = $request->customer;
//         $sale->reference        = $request->reference;
//         $sale->product          = implode(',', $combined_products);
//         $sale->product_code     = implode(',', $combined_codes);
//         $sale->brand            = implode(',', $combined_brands);
//         $sale->unit             = implode(',', $combined_units);
//         $sale->per_price        = implode(',', $combined_prices);
//         $sale->per_discount     = implode(',', $combined_discounts);
//         $sale->qty              = implode(',', $combined_qtys);
//         $sale->per_total        = implode(',', $combined_totals);

//         $sale->total_amount_Words = $request->total_amount_Words;
//         $sale->total_bill_amount  = $request->total_subtotal;
//         $sale->total_extradiscount= $request->total_extra_cost;
//         $sale->total_net           = $request->total_net;

//         $sale->cash = $request->cash;
//         $sale->card = $request->card;
//         $sale->change = $request->change;

//         $sale->total_items = $total_items;
//         $sale->save();

//         // ---- Maintain Customer Ledger ----
//         $customer_id = $request->customer;
//         $ledger_last = CustomerLedger::where('customer_id', $customer_id)
//                         ->latest('id')->first();

//         $previous_balance = $ledger_last->closing_balance ?? 0;
//         $closing_balance  = $previous_balance + $request->total_net; // sale increases customer debit

//         CustomerLedger::create([
//             'customer_id'     => $customer_id,
//             'admin_or_user_id'=> auth()->id(), // logged-in admin/user
//             'previous_balance'=> $previous_balance,
//             'closing_balance' => $closing_balance,
//             'date'            => now(),
//             'description'     => 'Sale #' . $sale->id,
//             'debit'           => $request->total_net,
//             'credit'          => 0,
//         ]);

//         DB::commit();
//         return back()->with('success', 'Sale recorded, stock updated, and ledger maintained.');
//     } catch (\Exception $e) {
//         DB::rollback();
//         return back()->with('error', 'Error: ' . $e->getMessage());
//     }
// }

// public function store(Request $request)
// {
//     DB::beginTransaction();

//     try {
//         $product_ids     = $request->product_id;
//         $product_names   = $request->product_id;
//         $product_codes   = $request->item_code;
//         $brands          = $request->uom;
//         $units           = $request->unit;
//         $prices          = $request->price;
//         $discounts       = $request->item_disc;
//         $quantities      = $request->qty;
//         $totals          = $request->total;
//         $colors          = $request->color; // ✅ Now an array of arrays

//         $combined_products   = [];
//         $combined_codes      = [];
//         $combined_brands     = [];
//         $combined_units      = [];
//         $combined_prices     = [];
//         $combined_discounts  = [];
//         $combined_qtys       = [];
//         $combined_totals     = [];
//         $combined_colors     = [];

//         $total_items = 0;

//         foreach ($product_ids as $index => $product_id) {
//             $qty   = $quantities[$index] ?? 0;
//             $price = $prices[$index] ?? 0;

//             // skip invalid row
//             if (!$product_id || !$qty || !$price) {
//                 continue;
//             }

//             $combined_products[]   = $product_names[$index] ?? '';
//             $combined_codes[]      = $product_codes[$index] ?? '';
//             $combined_brands[]     = $brands[$index] ?? '';
//             $combined_units[]      = $units[$index] ?? '';
//             $combined_prices[]     = $prices[$index] ?? 0;
//             $combined_discounts[]  = $discounts[$index] ?? 0;
//             $combined_qtys[]       = $quantities[$index] ?? 0;
//             $combined_totals[]     = $totals[$index] ?? 0;

//             // ✅ Store colors per row as JSON string
//             $rowColors = $colors[$index] ?? [];
//             $combined_colors[] = json_encode($rowColors);

//             // stock update
//             $stock = Stock::where('product_id', $product_id)->first();
//             if ($stock) {
//                 if ($stock->qty < $qty) {
//                     throw new \Exception("Not enough stock for product: " . $product_names[$index]);
//                 }
//                 $stock->qty -= $qty;
//                 $stock->save();
//             } else {
//                 throw new \Exception("Stock record not found for product ID: " . $product_id);
//             }

//             $total_items += $qty;
//         }

//         // ✅ Save Sale
//         $sale = new Sale();
//         $sale->customer            = $request->customer;
//         $sale->reference           = $request->reference;
//         $sale->product             = implode(',', $combined_products);
//         $sale->product_code        = implode(',', $combined_codes);
//         $sale->brand               = implode(',', $combined_brands);
//         $sale->unit                = implode(',', $combined_units);
//         $sale->per_price           = implode(',', $combined_prices);
//         $sale->per_discount        = implode(',', $combined_discounts);
//         $sale->qty                 = implode(',', $combined_qtys);
//         $sale->per_total           = implode(',', $combined_totals);
//         $sale->color             = json_encode($combined_colors); // ✅ Save all color rows

//         $sale->total_amount_Words = $request->total_amount_Words;
//         $sale->total_bill_amount  = $request->total_subtotal;
//         $sale->total_extradiscount= $request->total_extra_cost;
//         $sale->total_net          = $request->total_net;

//         $sale->cash   = $request->cash;
//         $sale->card   = $request->card;
//         $sale->change = $request->change;

//         $sale->total_items = $total_items;
//         $sale->save();

//         // ✅ Ledger
//         $customer_id = $request->customer;
//         $ledger_last = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
//         $previous_balance = $ledger_last->closing_balance ?? 0;
//         $closing_balance  = $previous_balance + $request->total_net;

//         CustomerLedger::create([
//             'customer_id'      => $customer_id,
//             'admin_or_user_id' => auth()->id(),
//             'previous_balance' => $previous_balance,
//             'closing_balance'  => $closing_balance,
//             'date'             => now(),
//             'description'      => 'Sale #' . $sale->id,
//             'debit'            => $request->total_net,
//             'credit'           => 0,
//         ]);

//         DB::commit();
//         return back()->with('success', 'Sale and colors saved successfully.');
//     } catch (\Exception $e) {
//         DB::rollback();
//         return back()->with('error', 'Error: ' . $e->getMessage());
//     }
// }
// public function store(Request $request)
// {
//     $action = $request->input('action'); // 'booking' or 'sale'
//     DB::beginTransaction();

//     try {
//         // All request extraction and arrays setup
//         $product_ids     = $request->product_id;
//         $product_names   = $request->product_id;
//         $product_codes   = $request->item_code;
//         $brands          = $request->uom;
//         $units           = $request->unit;
//         $prices          = $request->price;
//         $discounts       = $request->item_disc;
//         $quantities      = $request->qty;
//         $totals          = $request->total;
//         $colors          = $request->color;

//         $combined_products   = [];
//         $combined_codes      = [];
//         $combined_brands     = [];
//         $combined_units      = [];
//         $combined_prices     = [];
//         $combined_discounts  = [];
//         $combined_qtys       = [];
//         $combined_totals     = [];
//         $combined_colors     = [];

//         $total_items = 0;

//         foreach ($product_ids as $index => $product_id) {
//             $qty   = $quantities[$index] ?? 0;
//             $price = $prices[$index] ?? 0;

//             if (!$product_id || !$qty || !$price) continue;

//             $combined_products[]   = $product_names[$index] ?? '';
//             $combined_codes[]      = $product_codes[$index] ?? '';
//             $combined_brands[]     = $brands[$index] ?? '';
//             $combined_units[]      = $units[$index] ?? '';
//             $combined_prices[]     = $prices[$index] ?? 0;
//             $combined_discounts[]  = $discounts[$index] ?? 0;
//             $combined_qtys[]       = $quantities[$index] ?? 0;
//             $combined_totals[]     = $totals[$index] ?? 0;
//             $combined_colors[]     = json_encode($colors[$index] ?? []);

//             // ✅ Stock Minus only for SALE
//             if ($action === 'sale') {
//                 $stock = Stock::where('product_id', $product_id)->first();
//                 if ($stock) {
//                     if ($stock->qty < $qty) {
//                         throw new \Exception("Not enough stock for product: " . $product_names[$index]);
//                     }
//                     $stock->qty -= $qty;
//                     $stock->save();
//                 } else {
//                     throw new \Exception("Stock record not found for product ID: " . $product_id);
//                 }
//             }

//             $total_items += $qty;
//         }

//         // ✅ Choose model based on action
//         if ($action === 'booking') {
//             $model = new \App\Models\ProductBooking();
//         } else {
//             $model = new \App\Models\Sale();
//         }

//         // Common values
//         $model->customer             = $request->customer;
//         $model->reference            = $request->reference;
//         $model->product              = implode(',', $combined_products);
//         $model->product_code         = implode(',', $combined_codes);
//         $model->brand                = implode(',', $combined_brands);
//         $model->unit                 = implode(',', $combined_units);
//         $model->per_price            = implode(',', $combined_prices);
//         $model->per_discount         = implode(',', $combined_discounts);
//         $model->qty                  = implode(',', $combined_qtys);
//         $model->per_total            = implode(',', $combined_totals);
//         $model->color                = json_encode($combined_colors);
//         $model->total_amount_Words  = $request->total_amount_Words;
//         $model->total_bill_amount   = $request->total_subtotal;
//         $model->total_extradiscount = $request->total_extra_cost;
//         $model->total_net           = $request->total_net;
//         $model->cash                = $request->cash;
//         $model->card                = $request->card;
//         $model->change              = $request->change;
//         $model->total_items         = $total_items;
//         $model->save();

//         // ✅ Only Sale will update Ledger
//         if ($action === 'sale') {
//             $customer_id = $request->customer;
//             $ledger_last = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
//             $previous_balance = $ledger_last->closing_balance ?? 0;
//             $closing_balance  = $previous_balance + $request->total_net;

//             CustomerLedger::create([
//                 'customer_id'      => $customer_id,
//                 'admin_or_user_id' => auth()->id(),
//                 'previous_balance' => $previous_balance,
//                 'closing_balance'  => $closing_balance,
//                 'date'             => now(),
//                 'description'      => 'Sale #' . $model->id,
//                 'debit'            => $request->total_net,
//                 'credit'           => 0,
//             ]);
//         }

//         DB::commit();
//         return back()->with('success', $action === 'sale' ? 'Sale completed.' : 'Booking created successfully.');
//     } catch (\Exception $e) {
//         DB::rollback();
//         return back()->with('error', 'Error: ' . $e->getMessage());
//     }
// }
public function store(Request $request)
{
    $action = $request->input('action'); // 'booking' or 'sale'
    $booking_id = $request->booking_id; // <-- existing booking ID if editing

    DB::beginTransaction();

    try {
        // --- Your existing arrays setup ---
        $product_ids     = $request->product_id;
        $product_names   = $request->product_id; // seems like typo, probably $request->product_name?
        $product_codes   = $request->item_code;
        $brands          = $request->uom;
        $units           = $request->unit;
        $prices          = $request->price;
        $discounts       = $request->item_disc;
        $quantities      = $request->qty;
        $totals          = $request->total;
        $colors          = $request->color;

        $combined_products   = [];
        $combined_codes      = [];
        $combined_brands     = [];
        $combined_units      = [];
        $combined_prices     = [];
        $combined_discounts  = [];
        $combined_qtys       = [];
        $combined_totals     = [];
        $combined_colors     = [];

        $total_items = 0;

        foreach ($product_ids as $index => $product_id) {
            $qty   = $quantities[$index] ?? 0;
            $price = $prices[$index] ?? 0;

            if (!$product_id || !$qty || !$price) continue;

            $combined_products[]   = $product_names[$index] ?? '';
            $combined_codes[]      = $product_codes[$index] ?? '';
            $combined_brands[]     = $brands[$index] ?? '';
            $combined_units[]      = $units[$index] ?? '';
            $combined_prices[]     = $prices[$index] ?? 0;
            $combined_discounts[]  = $discounts[$index] ?? 0;
            $combined_qtys[]       = $quantities[$index] ?? 0;
            $combined_totals[]     = $totals[$index] ?? 0;
            $combined_colors[]     = json_encode($colors[$index] ?? []);

            // Only Sale updates stock
            if ($action === 'sale') {
                $stock = Stock::where('product_id', $product_id)->first();
                if ($stock) {
                    if ($stock->qty < $qty) {
                        throw new \Exception("Not enough stock for product: " . $product_names[$index]);
                    }
                    $stock->qty -= $qty;
                    $stock->save();
                } else {
                    throw new \Exception("Stock record not found for product ID: " . $product_id);
                }
            }

            $total_items += $qty;
        }

        // --- Choose model ---
        if ($action === 'booking') {
            $model = $booking_id ? \App\Models\ProductBooking::findOrFail($booking_id) : new \App\Models\ProductBooking();
        } else {
            $model = new \App\Models\Sale(); // usually you don’t edit sales
        }

        // --- Fill common fields ---
        $model->customer             = $request->customer;
        $model->reference            = $request->reference;
        $model->product              = implode(',', $combined_products);
        $model->product_code         = implode(',', $combined_codes);
        $model->brand                = implode(',', $combined_brands);
        $model->unit                 = implode(',', $combined_units);
        $model->per_price            = implode(',', $combined_prices);
        $model->per_discount         = implode(',', $combined_discounts);
        $model->qty                  = implode(',', $combined_qtys);
        $model->per_total            = implode(',', $combined_totals);
        $model->color                = json_encode($combined_colors);
        $model->total_amount_Words   = $request->total_amount_Words;
        $model->total_bill_amount    = $request->total_subtotal;
        $model->total_extradiscount  = $request->total_extra_cost;
        $model->total_net            = $request->total_net;
        $model->cash                 = $request->cash;
        $model->card                 = $request->card;
        $model->change               = $request->change;
        $model->total_items          = $total_items;
        $model->save();
 if ($action === 'sale') {
            $customer_id    = $request->customer;
            $ledger_last    = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
            $previous_balance = $ledger_last->closing_balance ?? 0;
            $closing_balance  = $previous_balance + $request->total_net;

            CustomerLedger::create([
                'customer_id'      => $customer_id,
                'admin_or_user_id' => auth()->id(),
                'previous_balance' => $previous_balance,
                'closing_balance'  => $closing_balance,
                'date'             => now(),
                'description'      => 'Sale #' . $model->id,
                'debit'            => $request->total_net,
                'credit'           => 0,
            ]);
        }
        DB::commit();
        return back()->with('success', $action === 'sale' ? 'Sale completed.' : 'Booking updated successfully!');
    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

public function convertFromBooking($id)
{
    $booking = ProductBooking::findOrFail($id);
    $customers = Customer::all();

    // Decode fields
    $products     = explode(',', $booking->product);
    $codes        = explode(',', $booking->product_code);
    $brands       = explode(',', $booking->brand);
    $units        = explode(',', $booking->unit);
    $prices       = explode(',', $booking->per_price);
    $discounts    = explode(',', $booking->per_discount);
    $qtys         = explode(',', $booking->qty);
    $totals       = explode(',', $booking->per_total);

    $colors_json  = json_decode($booking->color, true);

    $items = [];

    foreach ($products as $index => $p) {
        // Find product name using item_code or product_name
        $product = Product::where('item_name', trim($p))->orWhere('item_code', trim($codes[$index] ?? ''))->first();

        $items[] = [
            'product_id' => $product->id ?? '',
            'item_name'  => $product->item_name ?? $p, // 👈 This will appear in input box
            'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
            'uom'        => $product->brand->name ?? ($brands[$index] ?? ''),
            'unit'       => $product->unit_id ?? ($units[$index] ?? ''),
            'price'      => floatval($prices[$index] ?? 0),
            'discount'   => floatval($discounts[$index] ?? 0),
            'qty'        => intval($qtys[$index] ?? 1),
            'total'      => floatval($totals[$index] ?? 0),
            'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
        ];
    }

    return view('admin_panel.sale.booking_edit', [
        'Customer' => $customers,
        'booking' => $booking,
        'bookingItems' => $items,
    ]);
}

// sale return start
public function saleretun($id)
{
    $sale = Sale::findOrFail($id);
    $customers = Customer::all();

    // Decode sale pivot or comma fields
    $products = explode(',', $sale->product);
    $codes    = explode(',', $sale->product_code);
    $brands   = explode(',', $sale->brand);
    $units    = explode(',', $sale->unit);
    $prices   = explode(',', $sale->per_price);
    $discounts= explode(',', $sale->per_discount);
    $qtys     = explode(',', $sale->qty);
    $totals   = explode(',', $sale->per_total);
    $colors_json = json_decode($sale->color, true);

    $items = [];

    foreach ($products as $index => $p) {
    $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

    $items[] = [
        'product_id' => $product->id ?? '',
        'item_name'  => $product->item_name ?? $p,
        'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
        'brand'      => $product->brand->name ?? ($brands[$index] ?? ''), // <-- change here
        'unit'       => $product->unit ?? ($units[$index] ?? ''),
        'price'      => floatval($prices[$index] ?? 0),
        'discount'   => floatval($discounts[$index] ?? 0),
        'qty'        => intval($qtys[$index] ?? 1),
        'total'      => floatval($totals[$index] ?? 0),
        'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
    ];
}

    return view('admin_panel.sale.return.create', [
        'sale' => $sale,
        'Customer' => $customers,
        'saleItems' => $items,
    ]);
}

// public function storeSaleReturn(Request $request)
// {
//     // dd($request->all());
//     DB::beginTransaction();

//     try {
//         $product_ids     = $request->product_id;
//         $product_names   = $request->product;
//         $product_codes   = $request->item_code;
//         $brands          = $request->uom;
//         $units           = $request->unit;
//         $prices          = $request->price;
//         $discounts       = $request->item_disc;
//         $quantities      = $request->qty;
//         $totals          = $request->total;
//         $colors          = $request->color;

//         $combined_products   = [];
//         $combined_codes      = [];
//         $combined_brands     = [];
//         $combined_units      = [];
//         $combined_prices     = [];
//         $combined_discounts  = [];
//         $combined_qtys       = [];
//         $combined_totals     = [];
//         $combined_colors     = [];

//         $total_items = 0;

//         foreach ($product_ids as $index => $product_id) {
//             $qty   = $quantities[$index] ?? 0;
//             $price = $prices[$index] ?? 0;

//             if (!$product_id || !$qty || !$price) continue;

//             $combined_products[]   = $product_names[$index] ?? '';
//             $combined_codes[]      = $product_codes[$index] ?? '';
//             $combined_brands[]     = $brands[$index] ?? '';
//             $combined_units[]      = $units[$index] ?? '';
//             $combined_prices[]     = $price;
//             $combined_discounts[]  = $discounts[$index] ?? 0;
//             $combined_qtys[]       = $qty;
//             $combined_totals[]     = $totals[$index] ?? 0;

//             // Convert color to valid JSON array
//             $decodedColor = $colors[$index] ?? [];
//             if (is_array($decodedColor)) {
//                 $combined_colors[] = json_encode($decodedColor);
//             } else {
//                 $decoded = json_decode($decodedColor, true);
//                 $combined_colors[] = json_encode(is_array($decoded) ? $decoded : []);
//             }

//             // ➕ Restore stock
//             $stock = \App\Models\Stock::where('product_id', $product_id)->first();
//             if ($stock) {
//                 $stock->qty += $qty;
//                 $stock->save();
//             }

//             $total_items += $qty;
//         }

//         // ➕ Create Sale Return
//         $saleReturn = new \App\Models\SalesReturn();
//         $saleReturn->sale_id              = $request->sale_id;
//         $saleReturn->customer             = $request->customer;
//         $saleReturn->reference            = $request->reference;

//         $saleReturn->product              = implode(',', $combined_products);
//         $saleReturn->product_code         = implode(',', $combined_codes);
//         $saleReturn->brand                = implode(',', $combined_brands);
//         $saleReturn->unit                 = implode(',', $combined_units);
//         $saleReturn->per_price            = implode(',', $combined_prices);
//         $saleReturn->per_discount         = implode(',', $combined_discounts);
//         $saleReturn->qty                  = implode(',', $combined_qtys);
//         $saleReturn->per_total            = implode(',', $combined_totals);
//         $saleReturn->color                = json_encode($combined_colors);

//         $saleReturn->total_amount_Words   = $request->total_amount_Words;
//         $saleReturn->total_bill_amount    = $request->total_subtotal;
//         $saleReturn->total_extradiscount  = $request->total_extra_cost;
//         $saleReturn->total_net            = $request->total_net;

//         $saleReturn->cash                 = $request->cash;
//         $saleReturn->card                 = $request->card;
//         $saleReturn->change               = $request->change;

//         $saleReturn->total_items          = $total_items;
//         $saleReturn->return_note          = $request->return_note;

//         $saleReturn->save();

//         DB::commit();

//         return redirect()->route('sale.index')->with('success', 'Sale return saved successfully.');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         return back()->with('error', 'Sale return failed: ' . $e->getMessage());
//     }
// }
public function storeSaleReturn(Request $request)
{
    DB::beginTransaction();

    try {
        $product_ids     = $request->product_id;
        $product_names   = $request->product;
        $product_codes   = $request->item_code;
        $brands          = $request->uom;
        $units           = $request->unit;
        $prices          = $request->price;
        $discounts       = $request->item_disc;
        $quantities      = $request->qty;
        $totals          = $request->total;
        $colors          = $request->color;

        $combined_products   = [];
        $combined_codes      = [];
        $combined_brands     = [];
        $combined_units      = [];
        $combined_prices     = [];
        $combined_discounts  = [];
        $combined_qtys       = [];
        $combined_totals     = [];
        $combined_colors     = [];

        $total_items = 0;

        foreach ($product_ids as $index => $product_id) {
            $qty   = $quantities[$index] ?? 0;
            $price = $prices[$index] ?? 0;

            if (!$product_id || !$qty || !$price) continue;

            $combined_products[]   = $product_names[$index] ?? '';
            $combined_codes[]      = $product_codes[$index] ?? '';
            $combined_brands[]     = $brands[$index] ?? '';
            $combined_units[]      = $units[$index] ?? '';
            $combined_prices[]     = $price;
            $combined_discounts[]  = $discounts[$index] ?? 0;
            $combined_qtys[]       = $qty;
            $combined_totals[]     = $totals[$index] ?? 0;

            // Convert color to valid JSON array
            $decodedColor = $colors[$index] ?? [];
            if (is_array($decodedColor)) {
                $combined_colors[] = json_encode($decodedColor);
            } else {
                $decoded = json_decode($decodedColor, true);
                $combined_colors[] = json_encode(is_array($decoded) ? $decoded : []);
            }

            // ➕ Restore stock
            $stock = \App\Models\Stock::where('product_id', $product_id)->first();
            if ($stock) {
                $stock->qty += $qty;
                $stock->save();
            }

            $total_items += $qty;
        }

        // ➕ Create Sale Return
        $saleReturn = new \App\Models\SalesReturn();
        $saleReturn->sale_id              = $request->sale_id;
        $saleReturn->customer             = $request->customer;
        $saleReturn->reference            = $request->reference;
        $saleReturn->product              = implode(',', $combined_products);
        $saleReturn->product_code         = implode(',', $combined_codes);
        $saleReturn->brand                = implode(',', $combined_brands);
        $saleReturn->unit                 = implode(',', $combined_units);
        $saleReturn->per_price            = implode(',', $combined_prices);
        $saleReturn->per_discount         = implode(',', $combined_discounts);
        $saleReturn->qty                  = implode(',', $combined_qtys);
        $saleReturn->per_total            = implode(',', $combined_totals);
        $saleReturn->color                = json_encode($combined_colors);
        $saleReturn->total_amount_Words   = $request->total_amount_Words;
        $saleReturn->total_bill_amount    = $request->total_subtotal;
        $saleReturn->total_extradiscount  = $request->total_extra_cost;
        $saleReturn->total_net            = $request->total_net;
        $saleReturn->cash                 = $request->cash;
        $saleReturn->card                 = $request->card;
        $saleReturn->change               = $request->change;
        $saleReturn->total_items          = $total_items;
        $saleReturn->return_note          = $request->return_note;
        $saleReturn->save();

        // ➕ Mark original sale as returned
        $sale = \App\Models\Sale::find($request->sale_id);
        if ($sale) {
            $sale->sale_status = 1; // 1 = Return
            $sale->save();
        }

        // ➕ Update Customer Ledger for Sale Return
        $customer_id    = $request->customer;
        $ledger_last    = \App\Models\CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
        $previous_balance = $ledger_last->closing_balance ?? 0;
        $closing_balance  = $previous_balance - $request->total_net; // reduce balance for return

        \App\Models\CustomerLedger::create([
            'customer_id'      => $customer_id,
            'admin_or_user_id' => auth()->id(),
            'previous_balance' => $previous_balance,
            'closing_balance'  => $closing_balance,
            'date'             => now(),
            'description'      => 'Sale Return #' . $saleReturn->id,
            'debit'            => 0,
            'credit'           => $request->total_net, // credit the customer
        ]);

        DB::commit();

        return redirect()->route('sale.index')->with('success', 'Sale return saved successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Sale return failed: ' . $e->getMessage());
    }
}


public function salereturnview()
{
    // Fetch all sale returns with the original sale and customer info
    $salesReturns = SalesReturn::with('sale.customer_relation')->orderBy('created_at', 'desc')->get();

    return view('admin_panel.sale.return.index', [
        'salesReturns' => $salesReturns,
    ]);
}


}
