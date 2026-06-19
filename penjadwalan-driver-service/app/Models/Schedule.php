<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_name',
        'vehicle_id',
        'plate_number',
        'schedule_date',
        'shift',
        'status',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'vehicle_id' => 'integer',
    ];
}
