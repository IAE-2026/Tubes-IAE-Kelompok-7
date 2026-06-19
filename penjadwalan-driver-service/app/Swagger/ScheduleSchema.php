<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Schedule",
    type: "object",
    title: "Schedule",
    description: "Driver Schedule model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "driver_name", type: "string", example: "Budi Santoso"),
        new OA\Property(property: "vehicle_id", type: "integer", example: 1),
        new OA\Property(property: "plate_number", type: "string", example: "B 1234 ABC"),
        new OA\Property(property: "schedule_date", type: "string", format: "date", example: "2026-05-15"),
        new OA\Property(property: "shift", type: "string", enum: ["pagi", "siang", "malam"], example: "pagi"),
        new OA\Property(property: "status", type: "string", enum: ["active", "completed", "cancelled"], example: "active"),
        new OA\Property(property: "notes", type: "string", nullable: true, example: "Rute Jakarta - Bandung"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-05-15T10:00:00.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-05-15T10:00:00.000000Z"),
    ]
)]
class ScheduleSchema
{
    // This class exists solely for Swagger schema documentation
}
