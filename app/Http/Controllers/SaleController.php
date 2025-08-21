<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\CustomerLedger;

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

    $products = Product::with('brand')->where(function ($query) use ($q) {
            $query->where('item_name', 'like', "%{$q}%")
                  ->orWhere('item_code', 'like', "%{$q}%")
                  ->orWhere('barcode_path', 'like', "%{$q}%");
        })->get();

    return response()->json($products);
}




public function store(Request $request)
{
    // dd($request->all());
    DB::beginTransaction();

    try {
        $product_ids    = $request->product_id;
        $product_names  = $request->product_id;
        $product_codes  = $request->item_code;
        $brands         = $request->uom;
        $units          = $request->unit;
        $prices         = $request->price;
        $discounts      = $request->item_disc;
        $quantities     = $request->qty;
        $totals         = $request->total;

        $combined_products = [];
        $combined_codes    = [];
        $combined_brands   = [];
        $combined_units    = [];
        $combined_prices   = [];
        $combined_discounts= [];
        $combined_qtys     = [];
        $combined_totals   = [];

        $total_items = 0;

       foreach ($product_ids as $index => $product_id) {
    $qty   = $quantities[$index] ?? 0;
    $price = $prices[$index] ?? 0;

    // skip khali row: product id, quantity aur price check
    if (!$product_id || !$qty || !$price) {
        continue;
    }

    $combined_products[]   = $product_names[$index] ?? '';
    $combined_codes[]      = $product_codes[$index] ?? '';
    $combined_brands[]     = $brands[$index] ?? '';
    $combined_units[]      = $units[$index] ?? '';
    $combined_prices[]     = $prices[$index] ?? 0;
    $combined_discounts[]  = $discounts[$index] ?? 0;
    $combined_qtys[]       = $quantities[$index] ?? 0;
    $combined_totals[]     = $totals[$index] ?? 0;

    // stock update
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

    $total_items += $qty;
}


        // Save Sale
        $sale = new Sale();
        $sale->customer         = $request->customer;
        $sale->reference        = $request->reference;
        $sale->product          = implode(',', $combined_products);
        $sale->product_code     = implode(',', $combined_codes);
        $sale->brand            = implode(',', $combined_brands);
        $sale->unit             = implode(',', $combined_units);
        $sale->per_price        = implode(',', $combined_prices);
        $sale->per_discount     = implode(',', $combined_discounts);
        $sale->qty              = implode(',', $combined_qtys);
        $sale->per_total        = implode(',', $combined_totals);

        $sale->total_amount_Words = $request->total_amount_Words;
        $sale->total_bill_amount  = $request->total_subtotal;
        $sale->total_extradiscount= $request->total_extra_cost;
        $sale->total_net           = $request->total_net;

        $sale->cash = $request->cash;
        $sale->card = $request->card;
        $sale->change = $request->change;

        $sale->total_items = $total_items;
        $sale->save();

        // ---- Maintain Customer Ledger ----
        $customer_id = $request->customer;
        $ledger_last = CustomerLedger::where('customer_id', $customer_id)
                        ->latest('id')->first();

        $previous_balance = $ledger_last->closing_balance ?? 0;
        $closing_balance  = $previous_balance + $request->total_net; // sale increases customer debit

        CustomerLedger::create([
            'customer_id'     => $customer_id,
            'admin_or_user_id'=> auth()->id(), // logged-in admin/user
            'previous_balance'=> $previous_balance,
            'closing_balance' => $closing_balance,
            'date'            => now(),
            'description'     => 'Sale #' . $sale->id,
            'debit'           => $request->total_net,
            'credit'          => 0,
        ]);

        DB::commit();
        return back()->with('success', 'Sale recorded, stock updated, and ledger maintained.');
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }
}
