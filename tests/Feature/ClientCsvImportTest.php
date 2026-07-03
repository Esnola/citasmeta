<?php

namespace Tests\Feature;

use App\Livewire\ClientCsvImporter;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ClientCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_two_different_clients_with_same_phone(): void
    {
        $admin = User::factory()->create();
        $csv = <<<'CSV'
nombre,apellidos,telefono
Ana,Pérez,600123123
Ana María,Pérez López,600123123
CSV;

        $this->actingAs($admin);

        Livewire::test(ClientCsvImporter::class)
            ->set('file', UploadedFile::fake()->createWithContent('clientes.csv', $csv))
            ->call('import')
            ->assertSet('status', 'Importación completada: 2 nuevo(s), 0 omitido(s), 0 restaurado(s).');

        $this->assertSame(2, Client::query()->count());
        $this->assertSame(
            ['Ana', 'Ana María'],
            Client::query()->orderBy('nombre')->pluck('nombre')->all()
        );
        $this->assertSame(
            ['600123123'],
            Client::query()->pluck('telefono')->unique()->values()->all()
        );
    }

    public function test_admin_can_import_duplicate_same_client_without_creating_extra_rows(): void
    {
        $admin = User::factory()->create();
        $csv = <<<'CSV'
nombre;apellidos;telefono
Ana;Pérez;600123123
Ana;Pérez;600123123
CSV;

        $this->actingAs($admin);

        Livewire::test(ClientCsvImporter::class)
            ->set('file', UploadedFile::fake()->createWithContent('clientes.csv', $csv))
            ->call('import')
            ->assertSet('status', 'Importación completada: 1 nuevo(s), 1 omitido(s), 0 restaurado(s).');

        $this->assertSame(1, Client::query()->count());

        $client = Client::query()->firstOrFail();

        $this->assertSame('Ana', $client->nombre);
        $this->assertSame('Pérez', $client->apellidos);
        $this->assertSame('600123123', $client->telefono);
    }

    public function test_admin_can_import_same_csv_twice_without_creating_duplicates(): void
    {
        $admin = User::factory()->create();
        $csv = <<<'CSV'
nombre;apellidos;telefono
Ana;Pérez;600123123
CSV;

        $this->actingAs($admin);

        Livewire::test(ClientCsvImporter::class)
            ->set('file', UploadedFile::fake()->createWithContent('clientes.csv', $csv))
            ->call('import')
            ->assertSet('status', 'Importación completada: 1 nuevo(s), 0 omitido(s), 0 restaurado(s).');

        Livewire::test(ClientCsvImporter::class)
            ->set('file', UploadedFile::fake()->createWithContent('clientes.csv', $csv))
            ->call('import')
            ->assertSet('status', 'Importación completada: 0 nuevo(s), 1 omitido(s), 0 restaurado(s).');

        $this->assertSame(1, Client::query()->count());
    }

    public function test_admin_can_import_same_person_with_different_name_split_without_creating_duplicate(): void
    {
        $admin = User::factory()->create();

        Client::query()->create([
            'nombre' => 'Juan Carlos',
            'apellidos' => 'Pérez',
            'telefono' => '+34600123123',
        ]);

        $csv = <<<'CSV'
nombre;apellidos;telefono
Juan;Carlos Pérez;600123123
CSV;

        $this->actingAs($admin);

        Livewire::test(ClientCsvImporter::class)
            ->set('file', UploadedFile::fake()->createWithContent('clientes.csv', $csv))
            ->call('import')
            ->assertSet('status', 'Importación completada: 0 nuevo(s), 1 omitido(s), 0 restaurado(s).');

        $this->assertSame(1, Client::query()->count());
    }

    public function test_admin_can_import_without_overwriting_existing_client(): void
    {
        $admin = User::factory()->create();

        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '+34600123123',
        ]);

        $csv = <<<'CSV'
nombre;apellidos;telefono
Ana;Pérez;600123123
CSV;

        $this->actingAs($admin);

        Livewire::test(ClientCsvImporter::class)
            ->set('file', UploadedFile::fake()->createWithContent('clientes.csv', $csv))
            ->call('import')
            ->assertSet('status', 'Importación completada: 0 nuevo(s), 1 omitido(s), 0 restaurado(s).');

        $this->assertSame(1, Client::query()->count());

        $client->refresh();

        $this->assertSame('Ana', $client->nombre);
        $this->assertSame('Pérez', $client->apellidos);
        $this->assertSame('600123123', $client->telefono);
    }

    public function test_import_restores_soft_deleted_same_client_instead_of_creating_duplicate(): void
    {
        $admin = User::factory()->create();

        $client = Client::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Pérez',
            'telefono' => '+34600123123',
        ]);

        $client->delete();

        $csv = <<<'CSV'
nombre;apellidos;telefono
Ana;Pérez;600123123
CSV;

        $this->actingAs($admin);

        Livewire::test(ClientCsvImporter::class)
            ->set('file', UploadedFile::fake()->createWithContent('clientes.csv', $csv))
            ->call('import')
            ->assertSet('status', 'Importación completada: 0 nuevo(s), 0 omitido(s), 1 restaurado(s).');

        $this->assertSame(1, Client::withTrashed()->count());
        $this->assertSame(1, Client::query()->count());

        $client = Client::query()->firstOrFail();

        $this->assertSame('Ana', $client->nombre);
        $this->assertSame('Pérez', $client->apellidos);
        $this->assertFalse($client->trashed());
    }
}
