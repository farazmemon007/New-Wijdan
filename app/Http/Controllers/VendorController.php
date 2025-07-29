<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;

class VendorController extends Controller
{
    // VendorController.php aur WarehouseController.php same hoga
public function index() {
    $vendors = Vendor::all(); // ya $warehouses = Warehouse::all();
    return view('admin_panel.vendors.index', compact('vendors')); // ya warehouses.index
}

public function store(Request $request) {
    if ($request->id) {
        Vendor::findOrFail($request->id)->update($request->all());
    } else {
        Vendor::create($request->all());
    }
    return back()->with('success', 'Saved Successfully');
}

public function delete($id) {
    Vendor::findOrFail($id)->delete();
    return back()->with('success', 'Deleted Successfully');
}

}
