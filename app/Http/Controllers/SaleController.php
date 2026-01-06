<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Product;
use App\Models\Productbooking;
use App\Models\ProductBookingItem;
use App\Models\ReceiptsVoucher;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\Stock;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;


class SaleController extends Controller
{

    
   
public function ajaxPost(Request $request)
{
    return DB::transaction(function () use ($request) {

        if (!$request->booking_id) {
            abort(422, 'Booking ID is required');
        }

        /* ================= FETCH BOOKING ================= */
        $booking = Productbooking::with('items')
            ->lockForUpdate()
            ->findOrFail($request->booking_id);

        if ($booking->is_posted) {
            abort(422, 'This invoice is already posted');
        }

        /* ================= CREATE SALE ================= */
        $sale = Sale::create([
            'invoice_no'       => $booking->invoice_no,
            'manual_invoice'   => $booking->manual_invoice,
            'customer_id'      => $booking->customer_id,
            'party_type'       => $booking->party_type,
            'address'          => $booking->address,
            'tel'              => $booking->tel,
            'remarks'          => $booking->remarks,
            'sub_total1'       => $booking->sub_total1,
            'sub_total2'       => $booking->sub_total2,
            'discount_percent' => $booking->discount_percent,
            'discount_amount'  => $booking->discount_amount,
            'previous_balance' => $booking->previous_balance,
            'total_balance'    => $booking->total_balance,
            'total_net'        => $booking->sub_total2 ?? 0,
        ]);

        /* ================= ITEMS + STOCK HANDLING ================= */
        foreach ($booking->items as $it) {

            // 1️⃣ Sale Item
            SaleItem::create([
                'sale_id'      => $sale->id,
                'warehouse_id' => $it->warehouse_id,
                'product_id'   => $it->product_id,
                'sales_qty'    => $it->sales_qty,
                'retail_price' => $it->retail_price,
                'amount'       => $it->amount,
            ]);

            // 2️⃣ LOCK STOCK ROW
            $stock = Stock::lockForUpdate()
                ->where('warehouse_id', $it->warehouse_id)
                ->where('product_id', $it->product_id)
                ->first();

            if (!$stock) {
                abort(422, 'Stock not found for product ID ' . $it->product_id);
            }

            if ($stock->qty < $it->sales_qty) {
                abort(422, 'Insufficient stock for product ID ' . $it->product_id);
            }

            // 3️⃣ MINUS FROM STOCK
            $stock->qty -= $it->sales_qty;
            $stock->save();

            // 4️⃣ STOCK MOVEMENT (OUT)
            StockMovement::create([
                'product_id'   => $it->product_id,
                'type'         => 'out',
                'qty'          => $it->sales_qty,
                'ref_type'     => 'SALE',
                'ref_id'       => $sale->id,
                'ref_uuid'     => $booking->invoice_no,
                'is_auto_pluck'=> 1,
                'note'         => 'Sale Invoice ' . $booking->invoice_no,
            ]);
        }

        /* ================= RECEIPTS ================= */
        $receipts = ReceiptsVoucher::where(
            'reference_no',
            $booking->invoice_no
        )->get();

        foreach ($receipts as $rv) {
            $account = Account::lockForUpdate()->find($rv->row_account_id);
            if ($account) {
                $account->opening_balance += $rv->amount;
                $account->save();
            }
        }

        /* ================= CUSTOMER LEDGER ================= */
        $ledger = CustomerLedger::lockForUpdate()
            ->where('customer_id', $booking->customer_id)
            ->first();

        if ($ledger) {
            $ledger->previous_balance = $ledger->closing_balance;
            $ledger->closing_balance -= $receipts->sum('amount');
            if ($ledger->closing_balance < 0) {
                $ledger->closing_balance = 0;
            }
            $ledger->save();
        }

        /* ================= MARK POSTED ================= */
        $booking->update([
            'is_posted' => 1,
            'posted_at'=> now(),
        ]);

        return response()->json([
            'ok'          => true,
            'sale_id'     => $sale->id,
            'invoice_url' => route('sale.invoice', $sale->id),
        ]);
    });
}



public function ajaxSave(Request $request)
{
    return DB::transaction(function () use ($request) {

        /* ================= UPDATE / CREATE BOOKING ================= */
        if ($request->filled('booking_id')) {

            $booking = Productbooking::findOrFail($request->booking_id);

            ProductBookingItem::where('booking_id', $booking->id)->delete();
            ReceiptsVoucher::where('reference_no', $booking->invoice_no)->delete();

        } else {

            $booking = new Productbooking();
            $booking->invoice_no = 'INVSLE-' . str_pad(
                (Productbooking::max('id') ?? 0) + 1,
                4,
                '0',
                STR_PAD_LEFT
            );
        }

        /* ================= SAVE HEADER ================= */
        $booking->manual_invoice   = $request->Invoice_main;
        $booking->party_type       = $request->partyType;
        $booking->customer_id      = $request->customer_id;
        $booking->address          = $request->address;
        $booking->tel              = $request->tel;
        $booking->remarks          = $request->remarks;
        $booking->sub_total1       = $request->subTotal1 ?? 0;
        $booking->sub_total2       = $request->subTotal2 ?? 0;
        $booking->discount_percent = $request->discountPercent ?? 0;
        $booking->discount_amount  = $request->discountAmount ?? 0;
        $booking->previous_balance = $request->previousBalance ?? 0;
        $booking->total_balance    = $request->totalBalance ?? 0;

        $booking->quantity = 0;
        $booking->save();

        /* ================= SAVE ITEMS ================= */
        $totalQty = 0;

        foreach ($request->product_id ?? [] as $i => $productId) {

            $qty = (float) ($request->sales_qty[$i] ?? 0);
            if (!$productId || $qty <= 0) continue;

            $totalQty += $qty;

            ProductBookingItem::create([
                'booking_id' => $booking->id,
                'warehouse_id' => $request->warehouse_id[$i],
                'product_id' => $productId,
                'sales_qty' => $qty,
                'retail_price' => $request->retail_price[$i] ?? 0,
                'discount_amount' => $request->discount_amount[$i] ?? 0,
                'amount' => $request->sales_amount[$i] ?? 0,
            ]);
        }

        $booking->quantity = $totalQty;
        $booking->save();

        /* ================= SAVE RECEIPTS ================= */
        foreach ($request->receipt_account_id ?? [] as $i => $accId) {

            $amt = (float) ($request->receipt_amount[$i] ?? 0);
            if (!$accId || $amt <= 0) continue;

            ReceiptsVoucher::create([
                'rvid' => ReceiptsVoucher::generateRVID(),
                'receipt_date' => Carbon::today(),
                'entry_date' => Carbon::now(),
                'type' => 'SALE_RECEIPT',
                'party_id' => $booking->customer_id,
                'tel' => $booking->tel,
                'remarks' => $booking->remarks,
                'reference_no' => $booking->invoice_no,
                'row_account_head' => 'Cash/Bank',
                'row_account_id' => $accId,
                'amount' => $amt,
                'total_amount' => $amt,
            ]);
        }

        return response()->json([
            'ok' => true,
            'booking_id' => $booking->id
        ]);
    });
}




      public function getCustomerData($id, Request $request)
    {
        $type = strtolower($request->query('type', 'customer'));

        if ($type === 'vendor') {
            // Fetch Vendor data
            $v = Vendor::find($id);
            if (!$v) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            return response()->json([
                'address' => $v->address,
                'mobile' => $v->phone, // assuming 'phone' field for vendors
                'remarks' => '', // No remarks for vendors
                'previous_balance' => 0, // Vendors may not have balance logic
            ]);
        }

        // Default: Fetch Customer data (including walking)
        $c = Customer::find($id);
        if (!$c) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Retrieve the latest ledger entry for the customer
        $latestLedger = CustomerLedger::where('customer_id', $id)->latest()->first();

        // If a ledger entry exists, use its closing_balance; otherwise, set it to 0
        $previous_balance = $latestLedger ? $latestLedger->closing_balance : 0;

        return response()->json([
            'filer_type' => $c->filer_type,
            'customer_type' => $c->customer_type,
            'address' => $c->address,
            'mobile' => $c->mobile,
            'remarks' => $c->remarks ?? '',
            'previous_balance' => $previous_balance, // Use the latest closing_balance
        ]);
    }

    /**
     * Display a listing of the resource.
     */
//////////////
    // public function index  (Request $request)
    // {
    //     $type = $request->type ?? 'customer';

    //     $customers = Customer::where('type', $type)
    //         ->orderBy('name')
    //         ->get(['id', 'name', 'mobile']);
    //         dd($customers);

    //     return response()->json($customers);
    // }

    // // 🔹 Single customer detail
    // public function show($id, Request $request)
    // {
    //     $type = $request->type ?? 'customer';

    //     $customer = Customer::where('id', $id)
    //         ->where('type', $type)
    //         ->firstOrFail();

    //     return response()->json([
    //         'address' => $customer->address,
    //         'mobile' => $customer->mobile,
    //         'remarks' => $customer->remarks,
    //         'previous_balance' => $customer->previous_balance,
    //     ]);
    // }



    ////////////
    public function index()
    {
        $sales = Sale::with(['customer', 'product'])->get();

        return view('admin_panel.sale.index', compact('sales'));
    }

    public function addsale()
    {
        $products = Product::get();
        $customer = Customer::all();
        $warehouse = Warehouse::all();
        // dd($Customer);$warehouses = Warehouse::all();
        // $customers = Customer::all();
        $accounts = Account::all();
        // Get next invoice from Sale model generator (ensures INVSLE-003 -> INVSLE-004)
        $nextInvoiceNumber = Sale::generateInvoiceNo();


          return view('admin_panel.sale.add_sale222', compact('warehouse', 'customer','accounts', 'nextInvoiceNumber'));
    }

    public function searchpname(Request $request)
    {
        $q = $request->get('q');

        $products = Product::with(['brand'])
            // only products with active discount
            ->where(function ($query) use ($q) {
                $query->where('item_name', 'like', "%{$q}%")
                    ->orWhere('item_code', 'like', "%{$q}%")
                    ->orWhere('barcode_path', 'like', "%{$q}%");
            })
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $isBooking = $request->has('booking');
        if ($isBooking) {
            $booking = Productbooking::create([
                'invoice_no' => $request->Invoice_no,
                'manual_invoice' => $request->Invoice_main,
                'customer_id' => $request->customer,
                'party_type' => $request->input('partyType') ?? null,
                'sub_customer' => $request->customerType,
                'filer_type' => $request->filerType,
                'address' => $request->address,
                'tel' => $request->tel,
                'remarks' => $request->remarks,
                'sub_total1' => $request->subTotal1 ?? 0,
                'sub_total2' => $request->subTotal2 ?? 0,
                'discount_percent' => $request->discountPercent ?? 0,
                'discount_amount' => $request->discountAmount ?? 0,
                'previous_balance' => $request->previousBalance ?? 0,
                'total_balance' => $request->totalBalance ?? 0,
                'receipt1' => $request->receipt1 ?? 0,
                'receipt2' => $request->receipt2 ?? 0,
                'final_balance1' => $request->finalBalance1 ?? 0,
                'final_balance2' => $request->finalBalance2 ?? 0,
                'weight' => $request->weight ?? null,
            ]);

            $totalQty = 0;
            foreach ($request->warehouse_name ?? [] as $i => $warehouse_id) {
                $productId = $request->input("product_name.$i");
                if (empty($warehouse_id) || empty($productId)) {
                    continue;
                }

                $qty = (float) $request->input("sales-qty.$i", 0);
                $totalQty += $qty;

                ProductBookingItem::create([
                    'booking_id' => $booking->id,
                    'warehouse_id' => $warehouse_id,
                    'product_id' => $productId,
                    'stock' => (float) $request->input("stock.$i", 0),
                    'price_level' => (float) $request->input("price.$i", 0),
                    'sales_price' => (float) $request->input("sales-price.$i", 0),
                    'sales_qty' => $qty,
                    'retail_price' => (float) $request->input("retail-price.$i", 0),
                    'discount_percent' => (float) $request->input("discount-percent.$i", 0),
                    'discount_amount' => (float) $request->input("discount-amount.$i", 0),
                    'amount' => (float) $request->input("sales-amount.$i", 0),
                ]);
            }
            $booking->quantity = $totalQty;
            $booking->save();

            return back()->with('success', 'Booking saved successfully!');
        }

        // Direct Sale (stock minus)
        return DB::transaction(function () use ($request) {
            $invoiceNo = Sale::generateInvoiceNo();
            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'manual_invoice' => $request->Invoice_main ?? null,
                'partyType' => $request->input('partyType') ?? null,
                'customer_id' => $request->customer ?? null,
                'sub_customer' => $request->customerType ?? null,
                'filer_type' => $request->filerType ?? null,
                'address' => $request->address ?? null,
                'tel' => $request->tel ?? null,
                'remarks' => $request->remarks ?? null,
                'sub_total1' => $request->subTotal1 ?? 0,
                'sub_total2' => $request->subTotal2 ?? 0,
                'discount_percent' => $request->discountPercent ?? 0,
                'discount_amount' => $request->discountAmount ?? 0,
                'previous_balance' => $request->previousBalance ?? 0,
                'total_balance' => $request->totalBalance ?? 0,
                'receipt1' => $request->receipt1 ?? 0,
                'receipt2' => $request->receipt2 ?? 0,
                'final_balance1' => $request->finalBalance1 ?? 0,
                'final_balance2' => $request->finalBalance2 ?? 0,
                'weight' => $request->weight ?? null,
            ]);

            foreach ($request->warehouse_name ?? [] as $i => $warehouse_id) {
                $productId = $request->input("product_name.$i");
                if (empty($warehouse_id) || empty($productId)) {
                    continue;
                }

                $saleQty = (float) $request->input("sales-qty.$i", 0);

                // Per-warehouse stock
                if ($ws = WarehouseStock::where('warehouse_id', $warehouse_id)->where('product_id', $productId)->first()) {
                    $ws->quantity = max(0, $ws->quantity - $saleQty);
                    $ws->save();
                }

                // Global stock
                if ($p = Product::find($productId)) {
                    $p->stock = max(0, ($p->stock ?? 0) - $saleQty);
                    $p->save();
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'warehouse_id' => $warehouse_id,
                    'product_id' => $productId,
                    'stock' => (float) $request->input("stock.$i", 0),
                    'price_level' => (float) $request->input("price.$i", 0),
                    'sales_price' => (float) $request->input("sales-price.$i", 0),
                    'sales_qty' => $saleQty,
                    'retail_price' => (float) $request->input("retail-price.$i", 0),
                    'discount_percent' => (float) $request->input("discount-percent.$i", 0),
                    'discount_amount' => (float) $request->input("discount-amount.$i", 0),
                    'amount' => (float) $request->input("sales-amount.$i", 0),
                ]);
            }

            return back()->with('success', 'Sale saved successfully!');
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Convert booking to sale form prefill.
     */
    public function convertFromBooking($id)
    {
        $booking = ProductBooking::findOrFail($id);
        $customers = Customer::all();

        // Decode fields
        $products = explode(',', $booking->product);
        $codes = explode(',', $booking->product_code);
        $brands = explode(',', $booking->brand);
        $units = explode(',', $booking->unit);
        $prices = explode(',', $booking->per_price);
        $discounts = explode(',', $booking->per_discount);
        $qtys = explode(',', $booking->qty);
        $totals = explode(',', $booking->per_total);
        $colors_json = json_decode($booking->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            // Find product name using item_code or product_name
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p, // This will appear in input box
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
            'Customer'      => $customers,
            'booking'       => $booking,
            'bookingItems'  => $items,
        ]);
    }

    // sale return start
    public function saleretun($id)
    {
        $sale = Sale::findOrFail($id);
        $customers = Customer::all();

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
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
            'sale'      => $sale,
            'Customer'  => $customers,
            'saleItems' => $items,
        ]);
    }

    public function storeSaleReturn(Request $request)
    {
        DB::beginTransaction();

        try {
            // keep same location as sale (hidden fields in blade)
            $branchId = (int) ($request->input('branch_id', 1));
            $warehouseId = (int) ($request->input('warehouse_id', 1));

            $srMovements = [];

            $product_ids = $request->product_id ?? [];
            $product_names = $request->product ?? [];
            $product_codes = $request->item_code ?? [];
            $brands = $request->uom ?? [];
            $units = $request->unit ?? [];
            $prices = $request->price ?? [];
            $discounts = $request->item_disc ?? [];
            $quantities = $request->qty ?? [];
            $totals = $request->total ?? [];
            $colors = $request->color ?? [];

            $combined_products = $combined_codes = $combined_brands = $combined_units = [];
            $combined_prices = $combined_discounts = $combined_qtys = $combined_totals = $combined_colors = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty = max(0.0, (float) ($quantities[$index] ?? 0));
                $price = max(0.0, (float) ($prices[$index] ?? 0));

                if (! $product_id || $qty <= 0 || $price <= 0) {
                    continue;
                }

                $combined_products[] = $product_names[$index] ?? '';
                $combined_codes[] = $product_codes[$index] ?? '';
                $combined_brands[] = $brands[$index] ?? '';
                $combined_units[] = $units[$index] ?? '';
                $combined_prices[] = $price;
                $combined_discounts[] = $discounts[$index] ?? 0;
                $combined_qtys[] = $qty;
                $combined_totals[] = $totals[$index] ?? 0;

                $decodedColor = $colors[$index] ?? [];
                $combined_colors[] = is_array($decodedColor)
                    ? json_encode($decodedColor)
                    : json_encode((array) json_decode($decodedColor, true));

                // restore stock at SAME location (lock row to avoid race)
                $stock = Stock::where('product_id', $product_id)
                    ->where('branch_id', $branchId)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->qty += $qty;
                    $stock->save();
                } else {
                    Stock::create([
                        'product_id'   => $product_id,
                        'branch_id'    => $branchId,
                        'warehouse_id' => $warehouseId,
                        'qty'          => $qty,
                        'reserved_qty' => 0,
                    ]);
                }

                // movement queue (IN) → ref_id after save
                $srMovements[] = [
                    'product_id' => $product_id,
                    'type'       => 'in',
                    'qty'        => (float) $qty,
                    'ref_type'   => 'SR',
                    'ref_id'     => null,
                    'note'       => 'Sale return',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $total_items += $qty;
            }

            // create Sale Return first
            $saleReturn = new SalesReturn;
            $saleReturn->sale_id = $request->sale_id;
            $saleReturn->customer = $request->customer;
            $saleReturn->reference = $request->reference;
            $saleReturn->product = implode(',', $combined_products);
            $saleReturn->product_code = implode(',', $combined_codes);
            $saleReturn->brand = implode(',', $combined_brands);
            $saleReturn->unit = implode(',', $combined_units);
            $saleReturn->per_price = implode(',', $combined_prices);
            $saleReturn->per_discount = implode(',', $combined_discounts);
            $saleReturn->qty = implode(',', $combined_qtys);
            $saleReturn->per_total = implode(',', $combined_totals);
            $saleReturn->color = json_encode($combined_colors);
            $saleReturn->total_amount_Words = $request->total_amount_Words;
            $saleReturn->total_bill_amount = $request->total_subtotal;
            $saleReturn->total_extradiscount = $request->total_extra_cost;
            $saleReturn->total_net = $request->total_net;
            $saleReturn->cash = $request->cash;
            $saleReturn->card = $request->card;
            $saleReturn->change = $request->change;
            $saleReturn->total_items = $total_items;
            $saleReturn->return_note = $request->return_note;
            $saleReturn->save();

            // insert movements with proper ref_id
            if (! empty($srMovements)) {
                foreach ($srMovements as &$m) {
                    $m['ref_id'] = $saleReturn->id;
                }
                unset($m);

                DB::table('stock_movements')->insert($srMovements);
            }

            // update original sale
            $sale = Sale::find($request->sale_id);
            if ($sale) {
                $sale_qtys = array_map('floatval', explode(',', $sale->qty));
                $sale_totals = array_map('floatval', explode(',', $sale->per_total));
                $sale_prices = array_map('floatval', explode(',', $sale->per_price));

                foreach ($product_ids as $index => $product_id) {
                    $return_qty = max(0.0, (float) ($quantities[$index] ?? 0));
                    if ($return_qty > 0 && isset($sale_qtys[$index])) {
                        $sale_qtys[$index] = max(0.0, $sale_qtys[$index] - $return_qty);
                        $price = $sale_prices[$index] ?? 0.0;
                        $sale_totals[$index] = $price * $sale_qtys[$index];
                    }
                }

                $sale->qty = implode(',', $sale_qtys);
                $sale->per_total = implode(',', $sale_totals);
                $sale->total_net = array_sum($sale_totals);
                $sale->total_bill_amount = $sale->total_net;
                $sale->total_items = array_sum($sale_qtys);
                $sale->save();
            }

            // ledger impact
            $customer_id = $request->customer;
            $ledger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();

            if ($ledger) {
                $ledger->previous_balance = $ledger->closing_balance;
                $ledger->closing_balance = $ledger->closing_balance - $request->total_net;
                $ledger->save();
            } else {
                CustomerLedger::create([
                    'customer_id'      => $customer_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => 0,
                    'closing_balance'  => 0 - $request->total_net,
                    'opening_balance'  => 0 - $request->total_net,
                ]);
            }

            DB::commit();

            return redirect()->route('sale.index')->with('success', 'Sale return saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Sale return failed: '.$e->getMessage());
        }
    }

    public function salereturnview()
    {
        // Fetch all sale returns with the original sale and customer info
        $salesReturns = SalesReturn::with('sale.customer')->orderBy('created_at', 'desc')->get();

        return view('admin_panel.sale.return.index', [
            'salesReturns' => $salesReturns,
        ]);
    }

    public function saleinvoice($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
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
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.saleinvoice', [
            'sale'      => $sale,
            'saleItems' => $items,
        ]);
    }

    public function saleedit($id)
    {
        $sale = Sale::findOrFail($id);
        $customers = Customer::all();

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
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

        return view('admin_panel.sale.saleedit', [
            'sale'      => $sale,
            'Customer'  => $customers,
            'saleItems' => $items,
        ]);
    }

    public function updatesale(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // --- Arrays from request ---
            $product_ids = $request->product_id;
            $product_names = $request->product ?? []; // ✅ ab match karega
            $product_codes = $request->item_code;
            $brands = $request->brand;  // ✅ request me brand aata hai
            $units = $request->unit;
            $prices = $request->price;
            $discounts = $request->item_disc;
            $quantities = $request->qty;
            $totals = $request->total;
            $colors = $request->color;

            $combined_products = [];
            $combined_codes = [];
            $combined_brands = [];
            $combined_units = [];
            $combined_prices = [];
            $combined_discounts = [];
            $combined_qtys = [];
            $combined_totals = [];
            $combined_colors = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty = $quantities[$index] ?? 0;
                $price = $prices[$index] ?? 0;

                if (! $product_id || ! $qty || ! $price) {
                    continue;
                }

                $combined_products[] = $product_names[$index] ?? '';
                $combined_codes[] = $product_codes[$index] ?? '';
                $combined_brands[] = $brands[$index] ?? '';
                $combined_units[] = $units[$index] ?? '';
                $combined_prices[] = $prices[$index] ?? 0;
                $combined_discounts[] = $discounts[$index] ?? 0;
                $combined_qtys[] = $quantities[$index] ?? 0;
                $combined_totals[] = $totals[$index] ?? 0;
                $combined_colors[] = json_encode($colors[$index] ?? []);

                $total_items += $qty;
            }

            // --- Find existing Sale ---
            $sale = Sale::findOrFail($id);

            // Save old total before update
            $old_total = $sale->total_net;

            // --- Fill fields ---
            $sale->customer = $request->customer;
            $sale->reference = $request->reference;
            $sale->product = implode(',', $combined_products);
            $sale->product_code = implode(',', $combined_codes);
            $sale->brand = implode(',', $combined_brands);
            $sale->unit = implode(',', $combined_units);
            $sale->per_price = implode(',', $combined_prices);
            $sale->per_discount = implode(',', $combined_discounts);
            $sale->qty = implode(',', $combined_qtys);
            $sale->per_total = implode(',', $combined_totals);
            $sale->color = json_encode($combined_colors);
            $sale->total_amount_Words = $request->total_amount_Words;
            $sale->total_bill_amount = $request->total_subtotal;
            $sale->total_extradiscount = $request->total_extra_cost;
            $sale->total_net = $request->total_net;
            $sale->cash = $request->cash;
            $sale->card = $request->card;
            $sale->change = $request->change;
            $sale->total_items = $total_items;
            $sale->save();

            // Ledger update
            $customer_id = $request->customer;
            $ledger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();

            // Difference nikal lo
            $difference = $request->total_net - $old_total;

            if ($ledger) {
                $ledger->previous_balance = $ledger->closing_balance;
                $ledger->closing_balance = $ledger->closing_balance + $difference;
                $ledger->save();
            } else {
                CustomerLedger::create([
                    'customer_id'      => $customer_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => 0,
                    'closing_balance'  => $request->total_net,
                    'opening_balance'  => $request->total_net,
                ]);
            }

            DB::commit();

            return redirect()->route('sale.index')->with('success', 'Sale updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function saledc($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
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
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.saledc', [
            'sale'      => $sale,
            'saleItems' => $items,
        ]);
    }

    public function salerecepit($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
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
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.salerecepit', [
            'sale'      => $sale,
            'saleItems' => $items,
        ]);
    }


 /* -------- Prints -------- */
    public function invoice(Sale $sale)
    { 
   $sale->load([
        'items.product',
        'items.warehouse',
        'customer'
    ]);
        return view('admin_panel.sale.invoice', compact('sale'));
    }
    public function print2(Sale $sale)
    {
        return view('admin_panel.sale.prints.print2', compact('sale'));
    }
    public function dc(Sale $sale)
    {
        return view('admin_panel.sale.prints.dc', compact('sale'));
    }

    public function bookingPrint(Productbooking $booking)
    {
        return view('admin_panel.sale.booking.prints.print', compact('booking'));
    }
    public function bookingPrint2(Productbooking $booking)
    {
        return view('admin_panel.sale.booking.prints.print2', compact('booking'));
    }
public function bookingDc(Productbooking $booking)
{
    /* ================= CUSTOMER ================= */
    $customer = Customer::find($booking->customer_id);

    /* ================= ITEMS + CORRECT WAREHOUSE ================= */
    $items = ProductBookingItem::query()
        ->where('product_booking_items.booking_id', $booking->id)
        ->leftJoin('products', 'products.id', '=', 'product_booking_items.product_id')
        ->leftJoin('warehouses', 'warehouses.id', '=', 'product_booking_items.warehouse_id')
        ->select([
            'product_booking_items.*',
            'products.item_name',
            'warehouses.warehouse_name',
            'warehouses.location',
        ])
        ->get();

    return view(
        'admin_panel.sale.booking.prints.dc',
        compact('booking', 'customer', 'items')
    );
}





}
