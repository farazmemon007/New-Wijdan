<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usertype = Auth::user()->usertype;
        $userId = Auth::id();

        if ($usertype == 'user') {
            return view('user_panel.dashboard', compact('userId'));
        } 
        elseif ($usertype == 'admin') {
            $stock = Stock::orderByDesc('id')->get(); // get latest first
            return view('admin_panel.dashboard', compact('userId', 'stock'));
        } 
        else {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
    }
}
