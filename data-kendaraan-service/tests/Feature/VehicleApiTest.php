<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\Role;
use App\Models\User;

class VehicleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles for database
        Role::firstOrCreate(['name' => 'admin'], ['description' => 'Admin']);
        Role::firstOrCreate(['name' => 'staf'], ['description' => 'Staff']);
        Role::firstOrCreate(['name' => 'warga'], ['description' => 'Warga']);
    }

    /**
     * Test access is blocked when token is missing.
     */
    public function test_cannot_access_api_without_token(): void
    {
        $response = $this->getJson('/api/v1/vehicles');
        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Unauthorized: Missing or invalid Authorization Bearer token'
        ]);
    }

    /**
     * Test user with warga role can read vehicles but cannot create vehicles.
     */
    public function test_warga_role_can_read_but_cannot_create_vehicles(): void
    {
        // 1. Create a dummy vehicle
        Vehicle::create([
            'license_plate' => 'D 1111 TST',
            'type' => 'car',
            'brand' => 'Toyota',
            'status' => 'active',
            'receipt_number' => 'IAE-LOG-TEST'
        ]);

        // 2. Read vehicles with warga mock token
        $response = $this->getJson('/api/v1/vehicles', [
            'Authorization' => 'Bearer mock_token_warga'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonCount(1, 'data');

        // 3. Try to create a vehicle (should be blocked with 403)
        $createResponse = $this->postJson('/api/v1/vehicles', [
            'license_plate' => 'D 2222 TST',
            'type' => 'motorcycle',
            'brand' => 'Honda',
            'status' => 'active'
        ], [
            'Authorization' => 'Bearer mock_token_warga'
        ]);

        $createResponse->assertStatus(403);
        $createResponse->assertJsonPath('status', 'error');
        $this->assertDatabaseMissing('vehicles', ['license_plate' => 'D 2222 TST']);
    }

    /**
     * Test admin role can create vehicles and gets audited.
     */
    public function test_admin_role_can_create_vehicle_with_receipt_number(): void
    {
        $response = $this->postJson('/api/v1/vehicles', [
            'license_plate' => 'D 3333 TST',
            'type' => 'truck',
            'brand' => 'Mitsubishi',
            'status' => 'active'
        ], [
            'Authorization' => 'Bearer mock_token_admin'
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.license_plate', 'D 3333 TST');
        
        // Assert the mock receipt number is generated and saved in testing environment
        $response->assertJsonPath('data.receipt_number', 'IAE-LOG-TESTING-12345');
        
        $this->assertDatabaseHas('vehicles', [
            'license_plate' => 'D 3333 TST',
            'receipt_number' => 'IAE-LOG-TESTING-12345'
        ]);

        // Verify local user and role were mapped correctly
        $this->assertDatabaseHas('users', [
            'email' => 'admin@ktp.iae.id',
            'name' => 'Mock Admin'
        ]);
        
        $user = User::where('email', 'admin@ktp.iae.id')->first();
        $this->assertEquals('admin', $user->role->name);
    }
}
