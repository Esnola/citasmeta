<?php

namespace App\Livewire;

use App\Imports\ClientsImport;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ClientCsvImporter extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    public string $status = '';

    public string $statusType = 'neutral';

    public array $previewRows = [];

    public bool $previewLoaded = false;

    public function preview(): void
    {
        try {
            $this->validate([
                'file' => ['required', 'file', 'extensions:csv,txt', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,text/comma-separated-values', 'max:10240'],
            ]);

            $delimiter = $this->detectCsvDelimiter();
            $import = new ClientsImport(false, $delimiter);
            Excel::import($import, $this->file);

            $this->previewRows = $import->previewRows();
            $this->previewLoaded = true;

            if ($import->processedRows() === 0) {
                $this->setStatus('El CSV se leyó, pero no contenía filas válidas para previsualizar.', 'error');

                return;
            }

            $this->setStatus('Previsualización generada correctamente.');
        } catch (\Throwable $throwable) {
            $this->setStatus('No se pudo generar la previsualización: '.$throwable->getMessage(), 'error');
        }
    }

    public function import(): void
    {
        try {
            $this->validate([
                'file' => ['required', 'file', 'extensions:csv,txt', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,text/comma-separated-values', 'max:10240'],
            ]);

            $delimiter = $this->detectCsvDelimiter();
            $import = new ClientsImport(true, $delimiter);
            Excel::import($import, $this->file);

            if ($import->processedRows() === 0) {
                $this->setStatus('El CSV se leyó, pero no contenía filas válidas para importar.', 'error');

                return;
            }

            $this->reset('file');
            $this->setStatus(sprintf(
                'Importación completada: %d nuevo(s), %d omitido(s), %d restaurado(s).',
                $import->createdRows(),
                $import->skippedRows(),
                $import->restoredRows(),
            ));
        } catch (\Throwable $throwable) {
            $this->setStatus('No se pudo importar el CSV: '.$throwable->getMessage(), 'error');
        }
    }

    public function render()
    {
        return view('livewire.client-csv-importer', [
            'previewRows' => $this->previewRows,
        ]);
    }

    private function setStatus(string $message, string $type = 'success'): void
    {
        $this->status = $message;
        $this->statusType = $type;
    }

    private function detectCsvDelimiter(): string
    {
        $path = $this->file?->getRealPath();

        if (! is_string($path) || $path === '') {
            return ',';
        }

        $sample = file_get_contents($path, false, null, 0, 4096);

        if (! is_string($sample) || $sample === '') {
            return ',';
        }

        $sample = preg_replace('/^\xEF\xBB\xBF/', '', $sample) ?? $sample;
        $counts = [
            ';' => substr_count($sample, ';'),
            ',' => substr_count($sample, ','),
            "\t" => substr_count($sample, "\t"),
        ];

        arsort($counts);

        $delimiter = array_key_first($counts);

        return is_string($delimiter) && $counts[$delimiter] > 0 ? $delimiter : ',';
    }
}
