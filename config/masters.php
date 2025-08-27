<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Strategy
    |--------------------------------------------------------------------------
    |
    | Define how the package should handle multi-tenancy:
    | - 'single_db': Single database with tenant_id column
    | - 'multi_db': Multiple databases per tenant
    | - 'schema': Schema-based separation
    |
    */
    'tenancy_strategy' => env('MASTERS_TENANCY_STRATEGY', 'single_db'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Column Name
    |--------------------------------------------------------------------------
    |
    | The column name used to identify tenants in single_db strategy
    |
    */
    'tenant_column' => 'tenant_id',

    /*
    |--------------------------------------------------------------------------
    | Global Data Support
    |--------------------------------------------------------------------------
    |
    | Enable global master data that can be shared across all tenants
    |
    */
    'enable_global_data' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for master data. Laravel 12 supports enhanced
    | cache tagging and atomic locks for better performance.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'masters',
        'driver' => env('MASTERS_CACHE_DRIVER', 'redis'), // Recommended for Laravel 12
        'tags' => [
            'masters',
            'master_types',
            'master_data',
        ],
        'atomic_locks' => true, // Laravel 12 feature for cache consistency
        'serialization' => 'json', // Laravel 12 improved serialization
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Laravel 12 performance optimizations
    |
    */
    'performance' => [
        'eager_loading' => true,
        'chunk_size' => 1000,
        'lazy_collections' => true, // Use lazy collections for large datasets
        'database_connection_pooling' => true,
        'query_optimization' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Master Data Types
    |--------------------------------------------------------------------------
    |
    | Pre-defined master data types that will be created during installation
    |
    */
    'default_types' => [
        'countries' => [
            'name' => 'Countries',
            'slug' => 'countries',
            'is_hierarchical' => false,
            'is_global' => true,
        ],
        'states' => [
            'name' => 'States/Provinces',
            'slug' => 'states',
            'is_hierarchical' => true,
            'parent_type' => 'countries',
            'is_global' => true,
        ],
        'cities' => [
            'name' => 'Cities',
            'slug' => 'cities',
            'is_hierarchical' => true,
            'parent_type' => 'states',
            'is_global' => true,
        ],
        'categories' => [
            'name' => 'Categories',
            'slug' => 'categories',
            'is_hierarchical' => true,
            'is_global' => false,
        ],
        'currencies' => [
            'name' => 'Currencies',
            'slug' => 'currencies',
            'is_hierarchical' => false,
            'is_global' => true,
        ],
        'languages' => [
            'name' => 'Languages',
            'slug' => 'languages',
            'is_hierarchical' => false,
            'is_global' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for API endpoints with Laravel 12 enhancements
    |
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'api/masters',
        'middleware' => ['api'],
        'rate_limit' => '60,1', // 60 requests per minute
        'version' => 'v1',
        'response_format' => 'json',
        'pagination' => [
            'default_per_page' => 15,
            'max_per_page' => 100,
        ],
        'features' => [
            'json_api' => false, // Laravel 12 JSON:API support
            'api_resources' => true, // Use Eloquent API Resources
            'openapi_spec' => true, // Generate OpenAPI specification
            'request_validation' => true, // Enhanced request validation
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the web-based admin panel
    |
    */
    'admin' => [
        'enabled' => true,
        'prefix' => 'admin/masters',
        'middleware' => ['web', 'auth'],
        'permission' => 'manage-masters',
    ],

    /*
    |--------------------------------------------------------------------------
    | Import/Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for bulk import/export operations
    |
    */
    'import_export' => [
        'enabled' => true,
        'chunk_size' => 1000,
        'allowed_formats' => ['csv', 'xlsx'],
        'max_file_size' => '10M',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Default validation rules for master data
    |
    */
    'validation' => [
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50',
        'iso_code' => 'nullable|string|max:10',
        'description' => 'nullable|string|max:1000',
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | Enable soft deletes for master data
    |
    */
    'soft_deletes' => true,

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    |
    | Enable audit trail for tracking changes with Laravel 12 enhancements
    |
    */
    'audit_trail' => true,

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Laravel 12 security enhancements
    |
    */
    'security' => [
        'csrf_protection' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'input_sanitization' => true,
        'sql_injection_protection' => true,
        'xss_protection' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Observability
    |--------------------------------------------------------------------------
    |
    | Laravel 12 monitoring features
    |
    */
    'monitoring' => [
        'enabled' => env('MASTERS_MONITORING_ENABLED', false),
        'metrics' => [
            'api_requests' => true,
            'cache_hit_ratio' => true,
            'query_performance' => true,
            'tenant_isolation' => true,
        ],
        'logging' => [
            'level' => 'info',
            'channels' => ['single', 'slack'],
        ],
    ],
];
