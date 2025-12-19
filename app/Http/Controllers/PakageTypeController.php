<?php

namespace App\Http\Controllers;

use App\Models\PackageType;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class PackageTypeController extends Controller
{
    /**
     * 🔹 List all package types
     */
    public function index()
    {
        $packageTypes = PackageType::orderBy('id', 'desc')->get();
        return view('package_type.index', compact('packageTypes'));
    }

    /**
     * 🔹 Store new package type
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:package_types,name',
        ], [
            'name.required' => 'Package type is required',
            'name.unique'   => 'Package type already exists',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->with('swal_error', $validator->errors()->first());
        }

        PackageType::create([
            'name' => $request->name,
        ]);

        return redirect()->back()
            ->with('success', 'Package Type Created Successfully');
    }

    /**
     * 🔹 Update package type
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'edit_id' => 'required|exists:package_types,id',
            'name'    => 'required|unique:package_types,name,' . $request->edit_id,
        ], [
            'name.required' => 'Package type is required',
            'name.unique'   => 'Package type already exists',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->with('swal_error', $validator->errors()->first());
        }

        $packageType = PackageType::findOrFail($request->edit_id);
        $packageType->update([
            'name' => $request->name,
        ]);

        return redirect()->back()
            ->with('success', 'Package Type Updated Successfully');
    }

    /**
     * 🔹 Delete package type
     */
    public function destroy($id)
    {
        $packageType = PackageType::findOrFail($id);

        $packageType->delete();

        return redirect()->back()
            ->with('success', 'Package Type Deleted Successfully');
    }
}
