<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            ['nombre' => 'Juan Jose', 'apellidos' => 'Gonzalez Vega', 'telefono' => '618287914'],
            ['nombre' => 'Esther', 'apellidos' => 'Amado Calviño', 'telefono' => '659366775'],
        ];

        foreach ($clients as $client) {
            Client::query()->updateOrCreate(
                ['telefono' => Client::normalizePhone($client['telefono'])],
                [
                    'nombre' => $client['nombre'],
                    'apellidos' => $client['apellidos'],
                ]
            );
        }
    }
}
