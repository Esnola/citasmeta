<?php

namespace App\Exports;

use App\Models\Client;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        public ?string $filterNombre = null,
        public ?string $filterApellidos = null,
        public ?string $filterTelefono = null,
    ) {}

    public function collection(): Collection
    {
        return Client::query()
            ->when($this->filterNombre, fn ($query) => $query->where('nombre', 'like', '%'.$this->filterNombre.'%'))
            ->when($this->filterApellidos, fn ($query) => $query->where('apellidos', 'like', '%'.$this->filterApellidos.'%'))
            ->when($this->filterTelefono, fn ($query) => $query->where('telefono', 'like', '%'.$this->filterTelefono.'%'))
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();
    }

    public function headings(): array
    {
        return ['Nombre', 'Apellidos', 'Teléfono'];
    }

    public function map($client): array
    {
        return [
            $client->nombre,
            $client->apellidos,
            $client->telefono,
        ];
    }
}
