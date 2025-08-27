# Laravel 12 Compatibility Guide

This document outlines the Laravel 12 specific features and enhancements implemented in the Litepie Masters package.

## Laravel 12 Features Integration

### 1. Enhanced Performance

#### Lazy Collections
```php
// Memory-efficient processing of large datasets
$countries = MasterData::ofType('countries')->lazy()->chunk(100);

foreach ($countries as $chunk) {
    $chunk->each(function ($country) {
        // Process individual country
        $this->processCountry($country);
    });
}
```

#### Cursor Pagination
```php
// Efficient pagination for large datasets
$cursor = MasterData::ofType('countries')
    ->where('is_active', true)
    ->cursor();

foreach ($cursor as $country) {
    // Memory-efficient iteration
    $this->processCountry($country);
}
```

### 2. Advanced Caching

#### Cache Locks (Atomic Operations)
```php
// Prevent cache stampede with atomic locks
$result = Cache::lock('master-data-refresh', 10)->get(function () {
    return MasterData::refreshAllCache();
});
```

#### Enhanced Cache Tags
```php
// Clear specific cache groups
Cache::tags(['masters', 'countries'])->flush();

// Store with multiple tags
Cache::tags(['masters', 'hierarchical'])->put('country-tree', $treeData, 3600);
```

#### Improved Serialization
```php
// Laravel 12's enhanced JSON serialization
config(['masters.cache.serialization' => 'json']);

// Better handling of complex data structures
$complexData = [
    'metadata' => ['nested' => ['deep' => 'value']],
    'timestamps' => [now(), now()->addDay()],
];

Masters::create('complex-type', $complexData);
```

### 3. Database Enhancements

#### Full-Text Search
```php
// Native full-text search capabilities
$results = MasterData::ofType('countries')
    ->whereFullText(['name', 'description'], 'United States')
    ->get();

// Advanced search with boolean mode
$results = MasterData::ofType('products')
    ->whereFullText(['name', 'description'], '+phone -case', ['mode' => 'boolean'])
    ->get();
```

#### Improved Query Builder
```php
// Enhanced when() method with closures
$results = MasterData::query()
    ->when($searchTerm, fn($q) => $q->whereFullText(['name'], $searchTerm))
    ->when($categoryId, fn($q) => $q->where('parent_id', $categoryId))
    ->when($isActive, fn($q) => $q->where('is_active', true))
    ->orderByRaw('CASE WHEN featured = 1 THEN 0 ELSE 1 END, sort_order')
    ->get();
```

### 4. Validation Improvements

#### Enhanced Email Validation
```php
// Laravel 12's improved email validation
MasterType::create([
    'name' => 'Suppliers',
    'slug' => 'suppliers',
    'validation_rules' => [
        'email' => 'required|email:rfc,dns,spoof,filter',
        'website' => 'nullable|url:http,https',
        'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
    ],
]);
```

#### Custom Validation Rules
```php
// Register custom validation rules for master data
Validator::extend('unique_within_tenant', function ($attribute, $value, $parameters, $validator) {
    $tenantId = app('masters')->getCurrentTenant();
    $table = $parameters[0] ?? 'master_data';
    $column = $parameters[1] ?? $attribute;
    
    return !DB::table($table)
        ->where($column, $value)
        ->where('tenant_id', $tenantId)
        ->exists();
});
```

### 5. Event System Enhancements

#### Improved Model Events
```php
// More granular event handling
MasterData::creating(function ($masterData) {
    // Auto-assign tenant
    if (!$masterData->tenant_id) {
        $masterData->tenant_id = app('masters')->getCurrentTenant();
    }
    
    // Auto-generate slug
    if (!$masterData->slug) {
        $masterData->slug = Str::slug($masterData->name);
    }
});

MasterData::updated(function ($masterData) {
    // Clear related caches
    Cache::tags(['masters', "type-{$masterData->master_type_id}"])->flush();
});
```

### 6. API Enhancements

#### Rate Limiting with Redis
```php
// Advanced rate limiting
Route::middleware([
    'throttle:api',
    'throttle:masters:100,1', // 100 requests per minute for masters
])->group(function () {
    Route::apiResource('masters/{type}', MasterDataController::class);
});
```

#### Response Caching
```php
// Automatic response caching for API endpoints
Route::get('/api/masters/{type}', [MasterDataController::class, 'index'])
    ->middleware('cache.headers:public;max_age=3600;etag');
```

### 7. Multi-Tenancy Enhancements

#### Dynamic Database Connections
```php
// Laravel 12's improved connection management
class TenantDatabaseManager
{
    public function setTenantConnection(string $tenantId): void
    {
        $config = $this->getTenantDatabaseConfig($tenantId);
        
        Config::set("database.connections.tenant_{$tenantId}", $config);
        
        // Use Laravel 12's connection purging
        DB::purge("tenant_{$tenantId}");
        DB::reconnect("tenant_{$tenantId}");
    }
}
```

#### Tenant-Aware Caching
```php
// Automatic tenant isolation in cache
class TenantCacheManager
{
    public function remember(string $key, $ttl, callable $callback)
    {
        $tenantId = app('masters')->getCurrentTenant();
        $tenantKey = "tenant_{$tenantId}:{$key}";
        
        return Cache::tags(['masters', "tenant_{$tenantId}"])
            ->remember($tenantKey, $ttl, $callback);
    }
}
```

### 8. Security Enhancements

#### Enhanced Permission Checks
```php
// Laravel 12's improved authorization
Gate::define('masters.view', function ($user, $masterType) {
    return $user->hasPermissionTo("view_{$masterType->slug}", $user->currentTenant);
});

Gate::define('masters.create', function ($user, $masterType) {
    return $user->hasPermissionTo("create_{$masterType->slug}", $user->currentTenant)
        && $this->checkTenantAccess($user, $masterType);
});
```

### 9. Testing Improvements

#### Laravel 12 Testing Features
```php
// Enhanced testing with Laravel 12
class MasterDataTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    /** @test */
    public function it_supports_parallel_testing()
    {
        // Laravel 12's improved parallel testing
        $this->parallel(function () {
            $country = Masters::create('countries', [
                'name' => $this->faker->country,
                'code' => $this->faker->countryCode,
            ]);
            
            $this->assertDatabaseHas('master_data', [
                'id' => $country->id,
                'name' => $country->name,
            ]);
        });
    }
    
    /** @test */
    public function it_handles_concurrent_operations()
    {
        // Test race conditions
        $this->withoutExceptionHandling();
        
        collect(range(1, 10))->each(function ($i) {
            dispatch(function () use ($i) {
                Masters::create('countries', [
                    'name' => "Country {$i}",
                    'code' => "C{$i}",
                ]);
            });
        });
        
        $this->assertEquals(10, MasterData::ofType('countries')->count());
    }
}
```

### 10. Configuration Enhancements

#### Environment-Specific Optimizations
```php
// config/masters.php - Laravel 12 optimizations
return [
    'performance' => [
        'lazy_loading' => env('MASTERS_LAZY_LOADING', true),
        'cursor_pagination' => env('MASTERS_CURSOR_PAGINATION', true),
        'bulk_operations' => env('MASTERS_BULK_OPERATIONS', true),
    ],
    
    'cache' => [
        'driver' => env('MASTERS_CACHE_DRIVER', 'redis'),
        'serialization' => env('MASTERS_CACHE_SERIALIZATION', 'json'),
        'compression' => env('MASTERS_CACHE_COMPRESSION', false),
    ],
    
    'security' => [
        'rate_limiting' => [
            'enabled' => env('MASTERS_RATE_LIMITING', true),
            'requests_per_minute' => env('MASTERS_RATE_LIMIT', 100),
        ],
        'csrf_protection' => env('MASTERS_CSRF_PROTECTION', true),
    ],
];
```

## Migration from Earlier Versions

### Breaking Changes

1. **Minimum PHP Version**: Now requires PHP 8.2+
2. **Laravel Version**: Requires Laravel 10.0+
3. **Cache Driver**: Redis is now the recommended cache driver
4. **Database**: MySQL 8.0+ or PostgreSQL 13+ recommended

### Upgrade Steps

1. Update composer.json:
```bash
composer require litepie/masters:^2.0
```

2. Update configuration:
```bash
php artisan vendor:publish --tag=masters-config --force
```

3. Run migrations:
```bash
php artisan migrate
```

4. Clear caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Performance Optimizations

1. **Enable Redis**: Configure Redis for caching and sessions
2. **Use Lazy Collections**: For processing large datasets
3. **Implement Cursor Pagination**: For memory-efficient pagination
4. **Enable Cache Tags**: For granular cache management

## Troubleshooting

### Common Issues

1. **Memory Issues**: Use lazy collections and cursor pagination
2. **Cache Problems**: Clear cache tags specifically
3. **Performance**: Enable Redis and adjust cache TTL
4. **Multi-Tenancy**: Ensure proper tenant isolation

### Performance Monitoring

```php
// Monitor cache hit rates
$stats = Cache::getRedis()->info('stats');
$hitRate = $stats['keyspace_hits'] / ($stats['keyspace_hits'] + $stats['keyspace_misses']);

// Monitor query performance
DB::listen(function ($query) {
    if ($query->time > 1000) { // More than 1 second
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time,
            'bindings' => $query->bindings,
        ]);
    }
});
```

This package is fully optimized for Laravel 12 and takes advantage of all the latest performance and security enhancements.
