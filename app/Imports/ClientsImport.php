<?php

namespace App\Imports;

use App\Models\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToCollection, WithCustomCsvSettings, WithHeadingRow
{
    use Importable;

    private array $previewRows = [];

    private int $processedRows = 0;

    private int $persistedRows = 0;

    private int $createdRows = 0;

    private int $restoredRows = 0;

    private int $skippedRows = 0;

    public function __construct(
        private readonly bool $persist = true,
        private readonly string $csvDelimiter = ','
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $this->processedRows++;
            $preparedRow = $this->prepareRow(is_array($row) ? $row : $row->toArray());

            if ($this->persist) {
                $client = Client::upsertFromImport($preparedRow);
                $this->persistedRows++;

                if ($client->wasRecentlyCreated) {
                    $this->createdRows++;
                } elseif ($client->wasChanged('deleted_at')) {
                    $this->restoredRows++;
                } else {
                    $this->skippedRows++;
                }

                continue;
            }

            $this->previewRows[] = $preparedRow;
        }
    }

    public function previewRows(): array
    {
        return $this->previewRows;
    }

    public function processedRows(): int
    {
        return $this->processedRows;
    }

    public function persistedRows(): int
    {
        return $this->persistedRows;
    }

    public function createdRows(): int
    {
        return $this->createdRows;
    }

    public function restoredRows(): int
    {
        return $this->restoredRows;
    }

    public function skippedRows(): int
    {
        return $this->skippedRows;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => $this->csvDelimiter,
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ];
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$this->normalizeKey((string) $key)] = $value;
        }

        return $normalized;
    }

    private function extractValue(array $row, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            $normalizedAlias = $this->normalizeKey($alias);

            if (array_key_exists($normalizedAlias, $row)) {
                return $row[$normalizedAlias];
            }
        }

        return null;
    }

    private function normalizeKey(string $key): string
    {
        $key = Str::ascii(trim($key));
        $key = strtolower($key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? $key;

        return trim($key, '_');
    }

    private function prepareRow(array $row): array
    {
        $normalized = $this->normalizeRow($row);
        $fullName = $this->extractValue($normalized, ['nombre_completo', 'nombre_y_apellidos', 'nombre_del_paciente', 'full_name', 'paciente', 'cliente']);
        $nombre = $this->extractValue($normalized, ['nombre', 'nombres', 'name', 'first_name', 'given_name']);
        $apellidos = $this->extractValue($normalized, ['apellidos', 'apellido', 'surname', 'last_name', 'family_name']);
        $telefono = $this->extractValue($normalized, ['telefono', 'teléfono', 'numero', 'numero_telefono', 'telefono_movil', 'whatsapp_number', 'phone', 'mobile', 'cell', 'phone_number']);

        if (($nombre === null || trim((string) $nombre) === '') && is_string($fullName) && trim($fullName) !== '') {
            $fullNameParts = preg_split('/\s+/', trim($fullName)) ?: [];

            $nombre = array_shift($fullNameParts);
            $apellidos = $apellidos ?: trim(implode(' ', $fullNameParts));
        }

        $errors = [];

        foreach (['nombre' => $nombre, 'telefono' => $telefono] as $field => $value) {
            if (trim((string) $value) === '') {
                $errors[$field] = 'El campo '.$field.' es obligatorio.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return [
            'nombre' => (string) $nombre,
            'apellidos' => (string) ($apellidos ?? ''),
            'telefono' => (string) $telefono,
        ];
    }
}
