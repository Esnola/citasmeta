<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AppointmentsExport;
use App\Exports\ClientsExport;
use App\Exports\UsersExport;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use PDO;
use ZipArchive;

class ExportController extends Controller
{
    public function appointments()
    {
        $export = new AppointmentsExport;

        return $this->downloadCsv(
            $export->headings(),
            $export->collection()->map(fn ($row) => $export->map($row))->all(),
            'citas.csv'
        );
    }

    public function clients()
    {
        $export = new ClientsExport;

        return $this->downloadCsv(
            $export->headings(),
            $export->collection()->map(fn ($row) => $export->map($row))->all(),
            'clientes.csv'
        );
    }

    public function users()
    {
        $export = new UsersExport;

        return $this->downloadCsv(
            $export->headings(),
            $export->collection()->map(fn ($row) => $export->map($row))->all(),
            'usuarios.csv'
        );
    }

    public function database()
    {
        abort_unless(DB::connection()->getDriverName() === 'sqlite', 501, 'La copia SQL solo está disponible para SQLite.');

        abort_unless(class_exists(ZipArchive::class), 500, 'ZipArchive no está disponible en este servidor.');

        $zipPath = tempnam(sys_get_temp_dir(), 'citas-backup-');
        abort_if($zipPath === false, 500, 'No se pudo crear el archivo temporal.');

        $zip = new ZipArchive;
        abort_if($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true, 500, 'No se pudo crear el ZIP.');

        $zip->addFromString('citas-dentista-backup.sql', $this->dumpSqliteDatabase(DB::connection()->getPdo()));
        $zip->close();

        return response()
            ->download($zipPath, 'citas-dentista-backup.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    private function downloadCsv(array $headings, array $rows, string $fileName)
    {
        $callback = function () use ($headings, $rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headings, ',');

            foreach ($rows as $row) {
                fputcsv($handle, $row, ',');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function dumpSqliteDatabase(PDO $pdo): string
    {
        $lines = [
            'PRAGMA foreign_keys=OFF;',
            'BEGIN TRANSACTION;',
        ];

        $tables = $pdo->query(
            "SELECT name, sql FROM sqlite_master WHERE type = 'table' AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY name"
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tables as $table) {
            $lines[] = $table['sql'].';';
            $lines = array_merge($lines, $this->dumpSqliteTableRows($pdo, $table['name']));
        }

        $otherObjects = $pdo->query(
            "SELECT sql FROM sqlite_master WHERE type IN ('index', 'trigger', 'view') AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY CASE type WHEN 'index' THEN 0 WHEN 'trigger' THEN 1 ELSE 2 END, name"
        )->fetchAll(PDO::FETCH_COLUMN);

        foreach ($otherObjects as $sql) {
            $lines[] = $sql.';';
        }

        $lines[] = 'COMMIT;';

        return implode("\n", $lines)."\n";
    }

    private function dumpSqliteTableRows(PDO $pdo, string $table): array
    {
        $columns = array_column(
            $pdo->query('PRAGMA table_info('.$this->quoteIdentifier($table).')')->fetchAll(PDO::FETCH_ASSOC),
            'name'
        );

        if ($columns === []) {
            return [];
        }

        $statement = $pdo->query('SELECT * FROM '.$this->quoteIdentifier($table));
        $rows = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = 'INSERT INTO '.$this->quoteIdentifier($table)
                .' ('.implode(', ', array_map([$this, 'quoteIdentifier'], $columns)).') VALUES ('
                .implode(', ', array_map(fn (string $column) => $this->quoteSqliteValue($pdo, $row[$column] ?? null), $columns))
                .');';
        }

        return $rows;
    }

    private function quoteSqliteValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $quoted = $pdo->quote((string) $value);

        return $quoted !== false ? $quoted : "'".str_replace("'", "''", (string) $value)."'";
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
