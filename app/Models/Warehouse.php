<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{protected $fillable = ['warehouse_name', 'location', 'remarks']; 
    use HasFactory;
}
