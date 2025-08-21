<?php

namespace Litepie\Masters\Console\Commands;

use Illuminate\Console\Command;
use Litepie\Masters\Models\MasterData;
use Litepie\Masters\Models\MasterType;

class SeedMasterDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'masters:seed {type? : Specific master type to seed}';

    /**
     * The console command description.
     */
    protected $description = 'Seed sample master data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');

        if ($type) {
            $this->seedSpecificType($type);
        } else {
            $this->seedAllTypes();
        }

        return Command::SUCCESS;
    }

    /**
     * Seed a specific master type.
     */
    protected function seedSpecificType(string $typeSlug): void
    {
        $masterType = MasterType::where('slug', $typeSlug)->first();

        if (!$masterType) {
            $this->error("Master type '{$typeSlug}' not found.");
            return;
        }

        $this->info("Seeding data for: {$masterType->name}");

        switch ($typeSlug) {
            case 'countries':
                $this->seedCountries();
                break;
            case 'currencies':
                $this->seedCurrencies();
                break;
            case 'languages':
                $this->seedLanguages();
                break;
            default:
                $this->warn("No sample data available for '{$typeSlug}'");
        }
    }

    /**
     * Seed all available types.
     */
    protected function seedAllTypes(): void
    {
        $this->info('Seeding all master data...');
        
        $this->seedCountries();
        $this->seedCurrencies();
        $this->seedLanguages();
        
        $this->info('All master data seeded successfully!');
    }

    /**
     * Seed countries data.
     */
    protected function seedCountries(): void
    {
        $countries = [
            ['name' => 'United States', 'code' => 'US', 'iso_code' => 'USA'],
            ['name' => 'United Kingdom', 'code' => 'GB', 'iso_code' => 'GBR'],
            ['name' => 'Canada', 'code' => 'CA', 'iso_code' => 'CAN'],
            ['name' => 'Australia', 'code' => 'AU', 'iso_code' => 'AUS'],
            ['name' => 'Germany', 'code' => 'DE', 'iso_code' => 'DEU'],
            ['name' => 'France', 'code' => 'FR', 'iso_code' => 'FRA'],
            ['name' => 'India', 'code' => 'IN', 'iso_code' => 'IND'],
            ['name' => 'China', 'code' => 'CN', 'iso_code' => 'CHN'],
            ['name' => 'Japan', 'code' => 'JP', 'iso_code' => 'JPN'],
            ['name' => 'Brazil', 'code' => 'BR', 'iso_code' => 'BRA'],
        ];

        $this->seedMasterData('countries', $countries);
    }

    /**
     * Seed currencies data.
     */
    protected function seedCurrencies(): void
    {
        $currencies = [
            ['name' => 'US Dollar', 'code' => 'USD', 'iso_code' => '840'],
            ['name' => 'Euro', 'code' => 'EUR', 'iso_code' => '978'],
            ['name' => 'British Pound', 'code' => 'GBP', 'iso_code' => '826'],
            ['name' => 'Japanese Yen', 'code' => 'JPY', 'iso_code' => '392'],
            ['name' => 'Canadian Dollar', 'code' => 'CAD', 'iso_code' => '124'],
            ['name' => 'Australian Dollar', 'code' => 'AUD', 'iso_code' => '036'],
            ['name' => 'Swiss Franc', 'code' => 'CHF', 'iso_code' => '756'],
            ['name' => 'Chinese Yuan', 'code' => 'CNY', 'iso_code' => '156'],
            ['name' => 'Indian Rupee', 'code' => 'INR', 'iso_code' => '356'],
            ['name' => 'Brazilian Real', 'code' => 'BRL', 'iso_code' => '986'],
        ];

        $this->seedMasterData('currencies', $currencies);
    }

    /**
     * Seed languages data.
     */
    protected function seedLanguages(): void
    {
        $languages = [
            ['name' => 'English', 'code' => 'en', 'iso_code' => 'eng'],
            ['name' => 'Spanish', 'code' => 'es', 'iso_code' => 'spa'],
            ['name' => 'French', 'code' => 'fr', 'iso_code' => 'fra'],
            ['name' => 'German', 'code' => 'de', 'iso_code' => 'deu'],
            ['name' => 'Italian', 'code' => 'it', 'iso_code' => 'ita'],
            ['name' => 'Portuguese', 'code' => 'pt', 'iso_code' => 'por'],
            ['name' => 'Russian', 'code' => 'ru', 'iso_code' => 'rus'],
            ['name' => 'Chinese', 'code' => 'zh', 'iso_code' => 'zho'],
            ['name' => 'Japanese', 'code' => 'ja', 'iso_code' => 'jpn'],
            ['name' => 'Arabic', 'code' => 'ar', 'iso_code' => 'ara'],
        ];

        $this->seedMasterData('languages', $languages);
    }

    /**
     * Seed master data for a specific type.
     */
    protected function seedMasterData(string $typeSlug, array $data): void
    {
        $masterType = MasterType::where('slug', $typeSlug)->first();

        if (!$masterType) {
            $this->error("Master type '{$typeSlug}' not found.");
            return;
        }

        $count = 0;
        foreach ($data as $item) {
            $existing = MasterData::where('master_type_id', $masterType->id)
                ->where('code', $item['code'])
                ->first();

            if (!$existing) {
                MasterData::create(array_merge($item, [
                    'master_type_id' => $masterType->id,
                    'slug' => \Str::slug($item['name']),
                    'is_active' => true,
                ]));
                $count++;
            }
        }

        $this->line("Seeded {$count} {$typeSlug} records");
    }
}
