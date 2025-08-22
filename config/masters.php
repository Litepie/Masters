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
    | Configure caching for master data
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'masters',
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
    | Configuration for API endpoints
    |
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'api/masters',
        'middleware' => ['api'],
        'rate_limit' => '60,1', // 60 requests per minute
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
    | Enable audit trail for tracking changes
    |
    */
    'audit_trail' => true,
];
