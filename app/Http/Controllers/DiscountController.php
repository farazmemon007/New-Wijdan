<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDiscount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // Discount List Page
    public function index()
    {
        $discounts = ProductDiscount::with('product.category_relation', 'product.sub_category_relation', 'product.unit', 'product.brand')
            ->orderByDesc('id')->get();

        return view('admin_panel.product.discount.discount_index', compact('discounts'));
    }

    // Show Create Discount Page
    public function create(Request $request)
    {
        $productIds = $request->products ? explode(',', $request->products) : [];
        $products = Product::with(['category_relation', 'sub_category_relation', 'unit', 'brand', 'stock'])
            ->whereIn('id', $productIds)->get();

        return view('admin_panel.product.discount.discount_create', compact('products'));
    }

    // Store Discount
    public function store(Request $request)
    {
        foreach ($request->product_id as $key => $productId) {
            $product = Product::find($productId);

            $discountPercentage = $request->discount_percentage[$key] ?? 0;
            $discountAmount = $request->discount_amount[$key] ?? 0;
            $status = $request->status[$key] ?? 1;

            $finalPrice = $product->price; // original price
            if ($discountPercentage > 0) {
                $finalPrice = $product->price - ($product->price * $discountPercentage / 100);
            } elseif ($discountAmount > 0) {
                $finalPrice = $product->price - $discountAmount;
            }

            ProductDiscount::updateOrCreate(
                ['product_id' => $productId],
                [
                    'actual_price' => $product->price,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                    'final_price' => $finalPrice,
                    'status' => $status
                ]
            );
        }

        return redirect()->route('discount.index')->with('success', 'Discounts saved successfully.');
    }

    // Toggle Status Active/Inactive
    public function toggleStatus($id)
    {
        $discount = ProductDiscount::findOrFail($id);
        $discount->status = !$discount->status;
        $discount->save();

        return redirect()->back()->with('success', 'Discount status updated.');
    }

    // Discount Barcode Page
    public function barcode($id)
    {
        $discount = ProductDiscount::with('product')->findOrFail($id);
        return view('admin_panel.product.discount.discount_barcode', compact('discount'));
    }
}
