<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Vehicle::create([
            'license_plate' => 'B 1234 ABC',
            'type' => 'Car',
            'brand' => 'Toyota',
            'status' => 'active',
        ]);
        
        \App\Models\Vehicle::create([
            'license_plate' => 'D 5678 DEF',
            'type' => 'Truck',
            'brand' => 'Hino',
            'status' => 'active',
        ]);
    }
}
