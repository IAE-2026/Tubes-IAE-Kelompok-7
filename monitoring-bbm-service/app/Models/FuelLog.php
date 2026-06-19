<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{
    protected $fillable = ['vehicle_id','driver_name','liters','total_cost','fuel_station','filled_at','soap_receipt_number'];
}
