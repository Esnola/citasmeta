<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class AdminDatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_a_sqlite_backup_zip(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'López',
            'telefono' => '+34 600 000 000',
            'fecha' => today()->toDateString(),
            'hora' => '10:30:00',
        ]);

        Appointment::query()->create([
            'client_id' => $client->id,
            'fecha' => today()->addDay()->toDateString(),
            'hora' => '11:00:00',
            'enviado' => false,
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.export.database'))
            ->assertDownload('citas-dentista-backup.zip');

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;

        $this->assertTrue($zip->open($zipPath));

        $sql = $zip->getFromName('citas-dentista-backup.sql');

        $this->assertIsString($sql);
        $this->assertStringContainsString('PRAGMA foreign_keys=OFF;', $sql);
        $this->assertStringContainsString('INSERT INTO "users"', $sql);
        $this->assertStringContainsString('INSERT INTO "clients"', $sql);
        $this->assertStringContainsString('INSERT INTO "appointments"', $sql);
        $this->assertStringContainsString($admin->email, $sql);
        $this->assertStringContainsString('Ana', $sql);

        $zip->close();
    }
}
