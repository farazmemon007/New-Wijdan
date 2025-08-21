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
             'date',
            'description',
            'debit' ,
            'credit' ,
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
