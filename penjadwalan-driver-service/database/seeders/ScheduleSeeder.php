<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            [
                'driver_name' => 'Budi Santoso',
                'vehicle_id' => 1,
                'plate_number' => 'B 1234 ABC',
                'schedule_date' => '2026-05-15',
                'shift' => 'pagi',
                'status' => 'active',
                'notes' => 'Rute Jakarta - Bandung',
            ],
            [
                'driver_name' => 'Andi Wijaya',
                'vehicle_id' => 2,
                'plate_number' => 'B 5678 DEF',
                'schedule_date' => '2026-05-15',
                'shift' => 'siang',
                'status' => 'active',
                'notes' => 'Rute Jakarta - Semarang',
            ],
            [
                'driver_name' => 'Citra Dewi',
                'vehicle_id' => 3,
                'plate_number' => 'B 9012 GHI',
                'schedule_date' => '2026-05-15',
                'shift' => 'malam',
                'status' => 'active',
                'notes' => 'Rute Jakarta - Surabaya',
            ],
            [
                'driver_name' => 'Dedi Prasetyo',
                'vehicle_id' => 1,
                'plate_number' => 'B 1234 ABC',
                'schedule_date' => '2026-05-14',
                'shift' => 'pagi',
                'status' => 'completed',
                'notes' => 'Rute Jakarta - Cirebon',
            ],
            [
                'driver_name' => 'Eka Putri',
                'vehicle_id' => 4,
                'plate_number' => 'B 3456 JKL',
                'schedule_date' => '2026-05-16',
                'shift' => 'pagi',
                'status' => 'active',
                'notes' => null,
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
