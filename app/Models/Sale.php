<?php

// app/Models/Sale.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer', 'product', 'reference', 'product_code', 'brand', 'unit', 'per_price', 
        'per_discount', 'qty', 'per_total', 'total_amount_Words', 'total_bill_amount',
        'total_extradiscount', 'total_net', 'cash', 'card', 'change', 'total_discount',
        'total_subtotal', 'total_items','color'
    ];

    public function customer_relation()
    {
        return $this->belongsTo(Customer::class, 'customer', 'id');
    }

    public function product_relation()
    {
        return $this->belongsTo(Product::class, 'product', 'id');
    }
}
