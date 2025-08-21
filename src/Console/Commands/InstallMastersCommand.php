<?php

namespace Litepie\Masters\Console\Commands;

use Illuminate\Console\Command;
use Litepie\Masters\Models\MasterType;

class InstallMastersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'masters:install {--force : Force installation even if already installed}';

    /**
     * The console command description.
     */
    protected $description = 'Install the Masters package and create default master types';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Masters package...');

        // Check if already installed
        if (!$this->option('force') && MasterType::count() > 0) {
            $this->warn('Masters package appears to be already installed. Use --force to reinstall.');
            return Command::FAILURE;
        }

        // Create default master types
        $this->createDefaultMasterTypes();

        $this->info('Masters package installed successfully!');
        
        // Ask if user wants to seed sample data
        if ($this->confirm('Would you like to seed sample master data?')) {
            $this->call('masters:seed');
        }

        return Command::SUCCESS;
    }

    /**
     * Create default master types from config.
     */
    protected function createDefaultMasterTypes(): void
    {
        $this->info('Creating default master types...');

        $defaultTypes = config('masters.default_types', []);

        foreach ($defaultTypes as $slug => $typeData) {
            $masterType = MasterType::firstOrCreate(
                ['slug' => $slug],
                array_merge($typeData, ['slug' => $slug])
            );

            $this->line("Created/Updated: {$masterType->name}");
        }

        $this->info('Default master types created.');
    }
}
