<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->orderBy('email')
            ->get();
    }

    public function headings(): array
    {
        return ['Nombre', 'Correo', 'Administrador'];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->is_admin ? 'Sí' : 'No',
        ];
    }
}
