<?php

namespace Litepie\Masters\Console\Commands;

use Illuminate\Console\Command;
use Litepie\Masters\Facades\Masters;

class ImportMasterDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'masters:import {type : Master data type} {file : Path to import file}';

    /**
     * The console command description.
     */
    protected $description = 'Import master data from CSV or JSON file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Importing {$type} data from {$filePath}...");

        $data = $this->parseFile($filePath);

        if (empty($data)) {
            $this->error("No data found in file or invalid format.");
            return Command::FAILURE;
        }

        $results = Masters::import($type, $data);

        $this->info("Import completed:");
        $this->line("- Successfully imported: {$results['success']} records");
        $this->line("- Failed: {$results['failed']} records");

        if ($results['failed'] > 0) {
            $this->warn("Errors occurred during import:");
            foreach ($results['errors'] as $error) {
                $this->line("  Row {$error['row']}: {$error['error']}");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Parse the import file based on its extension.
     */
    protected function parseFile(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'json':
                return $this->parseJsonFile($filePath);
            case 'csv':
                return $this->parseCsvFile($filePath);
            default:
                $this->error("Unsupported file format: {$extension}");
                return [];
        }
    }

    /**
     * Parse JSON file.
     */
    protected function parseJsonFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON format: " . json_last_error_msg());
            return [];
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Parse CSV file.
     */
    protected function parseCsvFile(string $filePath): array
    {
        $data = [];
        $headers = [];
        $rowIndex = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if ($rowIndex === 0) {
                    $headers = $row;
                } else {
                    $data[] = array_combine($headers, $row);
                }
                $rowIndex++;
            }
            fclose($handle);
        }

        return $data;
    }
}
