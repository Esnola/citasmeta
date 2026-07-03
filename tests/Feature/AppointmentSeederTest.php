<?php

namespace Tests\Feature;

use App\Models\Appointment;
use Carbon\Carbon;
use Database\Seeders\AppointmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_creates_six_appointments_per_date_and_time_slot(): void
    {
        Carbon::setTestNow('2026-06-29 10:00:00');

        $this->seed(AppointmentSeeder::class);
        $this->seed(AppointmentSeeder::class);

        $this->assertDatabaseCount('clients', 174);
        $this->assertDatabaseCount('appointments', 1914);

        $appointments = Appointment::query()
            ->with('client')
            ->get();

        $this->assertCount(1914, $appointments);

        $startDate = now()->toDateString();
        $endDate = now()->addDays(10)->toDateString();

        foreach ($appointments as $appointment) {
            $this->assertNotNull($appointment->client);
            $this->assertGreaterThanOrEqual($startDate, $appointment->fecha->toDateString());
            $this->assertLessThanOrEqual($endDate, $appointment->fecha->toDateString());
        }

        foreach ($appointments->groupBy(fn (Appointment $appointment): string => $appointment->fecha->toDateString().' '.$appointment->hora) as $slotAppointments) {
            $this->assertCount(6, $slotAppointments);
        }

        $this->assertFalse(
            Appointment::query()
                ->select('client_id', 'fecha')
                ->groupBy('client_id', 'fecha')
                ->havingRaw('COUNT(*) > 1')
                ->exists()
        );
    }
}
