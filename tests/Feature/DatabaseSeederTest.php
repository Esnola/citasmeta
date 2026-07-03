<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\AppointmentSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_initial_admin_as_first_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 11);
        $this->assertDatabaseCount('clients', 30);
        $this->assertDatabaseCount('appointments', 1914);

        $admin = User::query()->firstOrFail();

        $this->assertSame(1, $admin->id);
        $this->assertSame('test@example.com', $admin->email);
    }

    public function test_client_seeder_populates_clients_without_duplicates(): void
    {
        $this->seed(ClientSeeder::class);
        $this->seed(ClientSeeder::class);

        $this->assertDatabaseCount('clients', 11);

        $client = Client::query()->where('telefono', '600123123')->firstOrFail();

        $this->assertSame('Ana', $client->nombre);
        $this->assertSame('Pérez López', $client->apellidos);
    }

    public function test_appointment_seeder_populates_client_appointments_without_duplicates(): void
    {
        $this->seed(AppointmentSeeder::class);
        $this->seed(AppointmentSeeder::class);

        $this->assertDatabaseCount('clients', 30);
        $this->assertDatabaseCount('appointments', 1914);

        $client = Client::query()->where('telefono', '618287914')->firstOrFail();

        $this->assertGreaterThanOrEqual(63, $client->appointments()->count());
    }
}
