<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Unit;
// use App\Models\Size;
use Carbon\Carbon;
use Milon\Barcode\DNS1D;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    public function searchProducts(Request $request)
{
    $q = $request->get('q');

    $products = Product::with('brand')->where(function ($query) use ($q) {
            $query->where('item_name', 'like', "%{$q}%")
                  ->orWhere('item_code', 'like', "%{$q}%")
                  ->orWhere('barcode_path', 'like', "%{$q}%");
        })->get();

    return response()->json($products);
}

    
    public function product()
{
    $products = Product::with('category_relation', 'sub_category_relation', 'unit', 'brand') // brand relation add kiya
        ->when(Auth::user()->email !== "admin@admin.com", function ($query) {
            return $query->where('creater_id', Auth::user()->id);
        })
        ->get();

    $categories = Category::get();

    return view('admin_panel.product.index', compact('products', 'categories'));
}

    public function view_store()
    {
        $categories = Category::select('id', 'name')->get();
        $units = Unit::select('id', 'name')->get();
        $brands = Brand::select('id', 'name')->get();
        return view('admin_panel.product.create', compact('categories', 'units', 'brands'));
    }

    public function getSubcategories($category_id)
    {
        $subcategories = SubCategory::where('category_id', $category_id)->get();
        return response()->json($subcategories);
    }
   public function generateBarcode(Request $request)
{
    $barcodeNumber = $request->has('code') && $request->code != ''
        ? $request->code
        : rand(100000000000, 999999999999);

    $barcodePNG = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39', 3, 50);
    $barcodeImage = "data:image/png;base64," . $barcodePNG;

    return response()->json([
        'barcode_number' => $barcodeNumber,
        'barcode_image'  => $barcodeImage
    ]);
}





   public function store_product(Request $request)
{
    if (!Auth::id()) {
        return redirect()->back();
    }

    $userId = Auth::id();

    // ✅ Last Product Code Auto Generate
    $lastProduct = Product::orderBy('id', 'desc')->first();
    $nextCode = 'ITEM-0001';
    if ($lastProduct) {
        $lastId = $lastProduct->id + 1;
        $nextCode = 'ITEM-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
    }

    // ✅ Image Upload Handle
    $imagePath = null;
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/products'), $filename);
        $imagePath = $filename; // save file name in variable
    }

    // ✅ Product Create
    Product::create([
        'creater_id'     => $userId,
        'category_id'    => $request->category_id,
        'sub_category_id'=> $request->sub_category_id,
        'item_code'      => $nextCode,
        'item_name'      => $request->product_name,
        'barcode_path'   => $request->barcode_path ?? rand(100000000000, 999999999999),
        'unit_id'        => $request->unit,
        'initial_stock'  => $request->Stock,
        'brand_id'        => $request->brand_id, 
        'wholesale_price'=> $request->wholesale_price,
        'price'          => $request->retail_price,
        'alert_quantity' => $request->alert_quantity,
        'image'          => $imagePath,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    return redirect()->back()->with('success', 'Product created successfully');
}



   public function update(Request $request, $id)
{
    $product_id = $id;
    $userId = auth()->id(); // current logged in user ka ID
    $imagePath = null;


    // Agar nayi image upload hui ho
    if ($request->hasFile('image')) {
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('uploads/products'), $imageName);
        $imagePath = 'uploads/products/' . $imageName;
    } else {
        // Purani image hi rakho
        $imagePath = Product::where('id', $product_id)->value('image');
    }

    Product::where('id', $product_id)->update([
        'creater_id'     => $userId,
        'category_id'    => $request->category_id,
        'sub_category_id'=> $request->sub_category_id,
        'item_code'      => $request->item_code, // yahan code update ka option
        'item_name'      => $request->product_name,
        'barcode_path'   => $request->barcode_path ?? rand(100000000000, 999999999999),
        'unit_id'        => $request->unit,
        'initial_stock'  => $request->Stock,
        'brand_id'       => $request->brand_id,
        'wholesale_price'=> $request->wholesale_price,
        'price'          => $request->retail_price,
        'alert_quantity' => $request->alert_quantity,
        'image'          => $imagePath,
        'updated_at'     => now(),
    ]);

    return redirect()->back()->with('success', 'Product updated successfully');
}
   public function edit($id)
{

    $product = Product::with('category_relation', 'sub_category_relation', 'unit', 'brand')->findOrFail($id);
    // dd($product->toArray());
    $categories = Category::all();
    

    $subcategories = SubCategory::all();
    $brands = Brand::all();
    return view('admin_panel.product.edit', compact('product', 'categories', 'subcategories', 'brands'));
}

    // Add function in ProductController.php
    public function barcode($id)
    {
        $product = Product::findOrFail($id);
        return view('admin_panel.product.barcode', compact('product'));
    }

    // public function searchProducts(Request $request)
    // {
    //     $query = $request->get('q');

    //     \Log::info("Search query: " . $query); // Debug log

    //     $products = Product::where('item_name', 'like', '%' . $query . '%')
    //         ->get(['id', 'item_name', 'item_code', 'retail_price', 'uom', 'measurement', 'unit']);

    //     if ($products->isEmpty()) {
    //         return response()->json(['message' => 'Product not found'], 404);
    //     }

    //     $products = $products->map(function ($product) {
    //         return [
    //             'id' => $product->id,
    //             'name' => $product->item_name,
    //             'code' => $product->item_code,
    //             'price' => $product->retail_price,
    //             'uom' => $product->uom,
    //             'measurement' => $product->measurement,
    //             'unit' => $product->unit,
    //         ];
    //     });

    //     return response()->json($products);
    // }
}
