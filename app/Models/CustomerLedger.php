<?php
// app/Models/CustomerLedger.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLedger extends Model
{
    protected $fillable = [
        'customer_id',
        'admin_or_user_id',
        'previous_balance',
        'closing_balance',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
