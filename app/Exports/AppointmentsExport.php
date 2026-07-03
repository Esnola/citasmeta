<?php

namespace App\Exports;

use App\Models\Appointment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppointmentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        public ?int $clientId = null,
        public bool $sentOnly = false,
    ) {}

    public function collection(): Collection
    {
        return Appointment::query()
            ->with('client')
            ->when($this->clientId, fn ($query) => $query->where('client_id', $this->clientId))
            ->when($this->sentOnly, fn ($query) => $query->where('enviado', true))
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();
    }

    public function headings(): array
    {
        return ['Cliente', 'Teléfono', 'Fecha', 'Hora', 'Enviado', 'Entregado', 'Activo', 'Cita activa'];
    }

    public function map($appointment): array
    {
        return [
            $appointment->client?->full_name ?? '',
            $appointment->client?->telefono ?? '',
            $appointment->fecha?->format('d/m/Y') ?? '',
            $appointment->hora ?? '',
            $appointment->enviado ? 'Sí' : 'No',
            $appointment->entregado ? 'Sí' : 'No',
            $appointment->activo ? 'Sí' : 'No',
            $appointment->cita_activa ? 'Sí' : 'No',
        ];
    }
}
