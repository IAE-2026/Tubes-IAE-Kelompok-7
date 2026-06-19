<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'license_plate',
        'type',
        'brand',
        'status',
        'receipt_number',
    ];
}
