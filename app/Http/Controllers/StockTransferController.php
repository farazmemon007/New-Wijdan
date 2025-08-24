<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransfer;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Product;

class StockTransferController extends Controller
{
    public function index() {
        $transfers = StockTransfer::with('fromWarehouse', 'toWarehouse', 'product')->get();
        return view('admin_panel.warehouses.stock_transfers.index', compact('transfers'));
    }

    public function create() {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('admin_panel.warehouses.stock_transfers.create', compact('warehouses', 'products'));
    }

    public function store(Request $request) {
        // dd($request->a   ll());
        $request->validate([
            'from_warehouse_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        // Source stock check
        $sourceStock = WarehouseStock::where('warehouse_id', $request->from_warehouse_id)
            ->where('product_id',$request->product_id)
            ->first();

        if (!$sourceStock || $sourceStock->quantity < $request->quantity) {
            return back()->with('error', 'Insufficient stock in source warehouse.');
        }

        // Reduce source stock
        $sourceStock->quantity -= $request->quantity;
        $sourceStock->save();

        // Add to destination warehouse if not shop
        if (!$request->to_shop && $request->to_warehouse_id) {
            $destStock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $request->to_warehouse_id,
                    'product_id' => $request->product_id
                ],
                [
                    'quantity' => 0,
                    'price' => $sourceStock->price
                ]
            );
            $destStock->quantity += $request->quantity;
            $destStock->save();
        }

        // Create transfer record
        StockTransfer::create($request->all());

        return redirect()->route('stock_transfers.index')->with('success', 'Stock transferred successfully.');
    }

    public function destroy(StockTransfer $stockTransfer) {
        // Optional: reverse the transfer if needed
        return back()->with('error', 'Deleting transfers not allowed.');
    }
    public function getStockQuantity(Request $request)
{
    $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
        ->where('product_id', $request->product_id)
        ->first();

    return response()->json([
        'quantity' => $stock ? $stock->quantity : 0
    ]);
}

}
