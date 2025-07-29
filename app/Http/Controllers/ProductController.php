<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
// use App\Models\Size;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function product()
    {
        // dd(Auth::user()->id);
        // if (Auth::id()) {
        //     $userId = Auth::id();
        $products = Product::with('category_relation','sub_category_relation')
        ->when(Auth::user()->email !== "admin@admin.com", function($query){
                return $query->where('creater_id', Auth::user()->id);
        })
        ->get();


            $categories = Category::get();
            // $sizes = Size::all(); // Size table se sab sizes le rahe hain
            return view('admin_panel.product.index',compact('products','categories'));
        // } else {
        //     return redirect()->back();
        // }
    }


public function fetchSubCategories(Request $request)
{
    $categoryId = $request->category_id;
    $subCategories = Subcategory::where('category_id',$categoryId)->get();
    return response()->json($subCategories);
}



public function store_product(Request $request)
{
    if (Auth::id()) {
        $userId = Auth::id();

        // ✅ Last Product Code Check
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $nextCode = 'ITEM-0001';
        if ($lastProduct) {
            $lastId = $lastProduct->id + 1;
            $nextCode = 'ITEM-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
        }

        Product::create([
            'creater_id' => $userId,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'item_code' => $nextCode, // ✅ Auto Generated Code
            'item_name' => $request->item_name,
            'size' => $request->size,
            'carton_quantity' => $request->carton_quantity,
            'pcs_in_carton' => $request->pcs_in_carton,
            'initial_stock' => $request->initial_stock,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
            'alert_quantity' => $request->alert_quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Product created successfully');
    } else {
        return redirect()->back();
    }
}

    public function update(Request $request, $id)
    {
        $product_id = $id;
        Product::where('id', $product_id)->update([
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'item_name' => $request->item_name,
            'pcs_in_carton' => $request->pcs_in_carton,
            'initial_stock' => $request->initial_stock,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
            'initial_stock' => $request->initial_stock,
            'alert_quantity' => $request->alert_quantity,
        ]);
        return redirect()->back()->with('success', 'Product updated successfully');
    }

    public function edit($id)
    {
      $product = Product::with('category_relation','sub_category_relation')
    ->where('id', $id)
    ->firstOrFail();


        return view('admin_panel.product.edit', compact('product'));
    }
    // Add function in ProductController.php
public function barcode($id)
{
    $product = Product::findOrFail($id);
    return view('admin_panel.product.barcode', compact('product'));
}

public function searchProducts(Request $request)
{
    $query = $request->get('q');

    \Log::info("Search query: " . $query); // Debug log

    $products = Product::where('item_name', 'like', '%' . $query . '%')
        ->get(['id', 'item_name', 'item_code', 'retail_price', 'uom','measurement','unit']);

    if ($products->isEmpty()) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    $products = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->item_name,
            'code' => $product->item_code,
            'price' => $product->retail_price,
            'uom' => $product->uom,
            'measurement' => $product->measurement,
            'unit' => $product->unit,
        ];
    });

    return response()->json($products);
}


}