<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    protected $fillable = [
        'vendor_id',
        'admin_or_user_id',
        'amount',
        'payment_method',
        'payment_date',
        'note',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
