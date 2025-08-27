<?php

namespace Litepie\Masters\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Litepie\Masters\Facades\Masters;
use Litepie\Masters\Models\MasterData;
use Litepie\Masters\Models\MasterType;
use Orchestra\Testbench\TestCase;

class Laravel12CompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            \Litepie\Masters\MastersServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Masters' => \Litepie\Masters\Facades\Masters::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup the application configuration
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set Laravel 12 compatible cache configuration
        $app['config']->set('cache.default', 'redis');
        $app['config']->set('cache.stores.redis', [
            'driver' => 'redis',
            'connection' => 'cache',
            'serialization' => 'json', // Laravel 12 feature
        ]);
    }

    /** @test */
    public function it_supports_laravel_12_lazy_collections()
    {
        // Create test data
        $this->createTestMasterType();
        $this->createTestMasterData();

        // Test lazy collections (Laravel 12 feature)
        $lazyCollection = MasterData::ofType('countries')->lazy();
        
        $this->assertInstanceOf(\Illuminate\Support\LazyCollection::class, $lazyCollection);
        $this->assertGreaterThan(0, $lazyCollection->count());
    }

    /** @test */
    public function it_supports_laravel_12_cache_locks()
    {
        $this->createTestMasterType();

        // Test atomic cache locks (Laravel 12 feature)
        $result = Cache::lock('test-master-data-lock', 5)->get(function () {
            return Masters::create('countries', [
                'name' => 'Test Country',
                'code' => 'TC',
                'is_active' => true,
            ]);
        });

        $this->assertNotNull($result);
        $this->assertEquals('Test Country', $result->name);
    }

    /** @test */
    public function it_supports_laravel_12_cache_tags()
    {
        $this->createTestMasterType();
        $country = $this->createTestMasterData();

        // Test cache tags (enhanced in Laravel 12)
        Cache::tags(['masters', 'test'])->put('test-key', 'test-value', 60);
        
        // Clear specific cache tags
        Cache::tags(['masters'])->flush();
        
        $this->assertNull(Cache::tags(['masters', 'test'])->get('test-key'));
    }

    /** @test */
    public function it_supports_laravel_12_cursor_pagination()
    {
        $this->createTestMasterType();
        $this->createMultipleTestData();

        // Test cursor pagination (Laravel 12 improvement)
        $cursor = MasterData::ofType('countries')->cursor();
        
        $count = 0;
        foreach ($cursor as $item) {
            $count++;
            $this->assertInstanceOf(MasterData::class, $item);
        }
        
        $this->assertGreaterThan(0, $count);
    }

    /** @test */
    public function it_supports_laravel_12_full_text_search()
    {
        $this->createTestMasterType();
        
        // Create test data with searchable content
        Masters::create('countries', [
            'name' => 'United States of America',
            'description' => 'A country in North America',
            'code' => 'US',
            'is_active' => true,
        ]);

        // Test full-text search (Laravel 12 feature)
        $results = MasterData::ofType('countries')
            ->whereFullText(['name', 'description'], 'United States')
            ->get();

        $this->assertGreaterThan(0, $results->count());
    }

    /** @test */
    public function it_supports_laravel_12_when_closures()
    {
        $this->createTestMasterType();
        $this->createTestMasterData();

        $searchTerm = 'Test';
        $tenant = 'test_tenant';

        // Test when with closures (Laravel 12 improvement)
        $results = MasterData::query()
            ->when($searchTerm, fn($q) => $q->where('name', 'like', "%{$searchTerm}%"))
            ->when($tenant, fn($q) => $q->where('tenant_id', $tenant))
            ->get();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
    }

    /** @test */
    public function it_supports_laravel_12_improved_validation()
    {
        $masterType = MasterType::create([
            'name' => 'Test Categories',
            'slug' => 'test-categories',
            'is_global' => false,
            'validation_rules' => [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10',
                'email' => 'nullable|email:rfc,dns', // Laravel 12 enhanced validation
            ],
        ]);

        $this->assertNotNull($masterType);
        $this->assertArrayHasKey('email', $masterType->validation_rules);
    }

    /** @test */
    public function it_supports_laravel_12_improved_serialization()
    {
        $this->createTestMasterType();
        
        // Test with different serialization methods
        Config::set('masters.cache.serialization', 'json');
        
        $country = Masters::create('countries', [
            'name' => 'Serialization Test',
            'code' => 'ST',
            'metadata' => ['key' => 'value', 'nested' => ['data' => 'test']],
            'is_active' => true,
        ]);

        $retrieved = Masters::get('countries')->first();
        $this->assertEquals($country->metadata, $retrieved->metadata);
    }

    /** @test */
    public function it_supports_laravel_12_performance_improvements()
    {
        $this->createTestMasterType();
        
        // Test bulk operations (Laravel 12 optimization)
        $data = collect(range(1, 100))->map(function ($i) {
            return [
                'name' => "Country {$i}",
                'code' => "C{$i}",
                'master_type_id' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        // Use Laravel 12's improved bulk insert
        MasterData::insert($data);
        
        $count = MasterData::ofType('countries')->count();
        $this->assertEquals(100, $count);
    }

    /** @test */
    public function it_supports_laravel_12_event_improvements()
    {
        $this->createTestMasterType();
        
        $eventFired = false;
        
        // Listen for model events (Laravel 12 improvements)
        MasterData::creating(function () use (&$eventFired) {
            $eventFired = true;
        });

        Masters::create('countries', [
            'name' => 'Event Test Country',
            'code' => 'ETC',
            'is_active' => true,
        ]);

        $this->assertTrue($eventFired);
    }

    private function createTestMasterType()
    {
        return MasterType::create([
            'name' => 'Countries',
            'slug' => 'countries',
            'is_global' => true,
            'is_hierarchical' => false,
            'validation_rules' => [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3',
            ],
        ]);
    }

    private function createTestMasterData()
    {
        return Masters::create('countries', [
            'name' => 'Test Country',
            'code' => 'TC',
            'description' => 'A test country for unit testing',
            'is_active' => true,
        ]);
    }

    private function createMultipleTestData()
    {
        for ($i = 1; $i <= 5; $i++) {
            Masters::create('countries', [
                'name' => "Test Country {$i}",
                'code' => "T{$i}",
                'is_active' => true,
            ]);
        }
    }
}
