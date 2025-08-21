# Laravel Masters Package

A comprehensive Laravel package for managing master data with advanced multi-tenancy support. This package provides a robust foundation for handling reference data like countries, states, cities, categories, currencies, languages, and custom master data types in multi-tenant Laravel applications.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Multi-Tenancy](#multi-tenancy)
- [Master Data Types](#master-data-types)
- [Usage Examples](#usage-examples)
- [API Documentation](#api-documentation)
- [Console Commands](#console-commands)
- [Admin Interface](#admin-interface)
- [Advanced Features](#advanced-features)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

### 🏢 **Multi-Tenant Architecture**
- **Flexible Tenancy Strategies**: Single database, multi-database, or schema-based isolation
- **Global vs Tenant Data**: Share common data globally while maintaining tenant-specific customizations
- **Automatic Tenant Scoping**: Built-in query scoping for seamless tenant isolation
- **Tenant Context Management**: Easy tenant switching with context preservation

### 📊 **Comprehensive Master Data Management**
- **Pre-built Data Types**: Countries, states, cities, categories, currencies, languages
- **Custom Master Types**: Create unlimited custom master data types with validation rules
- **Hierarchical Support**: Unlimited parent-child relationships with tree traversal
- **Rich Metadata**: Store additional data and metadata for each master record

### 🚀 **Performance & Scalability**
- **Smart Caching**: Automatic caching with configurable TTL and cache invalidation
- **Optimized Queries**: Efficient database queries with eager loading support
- **Bulk Operations**: High-performance import/export capabilities
- **Database Indexing**: Optimized database indexes for fast lookups

### 🔧 **Developer Experience**
- **Eloquent Integration**: Seamless integration with Laravel's Eloquent ORM
- **Facade Support**: Clean, expressive API through Laravel facades
- **Validation Framework**: Built-in validation with customizable rules
- **Event System**: Hook into create, update, delete events

### 🌐 **API & Web Interface**
- **RESTful API**: Complete REST API with filtering, searching, and pagination
- **Admin Panel**: Web-based administration interface
- **Import/Export**: Support for CSV, JSON, and Excel formats
- **Rate Limiting**: Built-in API rate limiting and throttling

### 🔒 **Security & Reliability**
- **Soft Deletes**: Safe data deletion with recovery options
- **Audit Trail**: Track all changes with comprehensive logging
- **Data Validation**: Multi-layer validation for data integrity
- **Permission Integration**: Works with Laravel's authorization system

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or 11.0
- MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.8+
- Redis (optional, for caching)

## Installation

### Step 1: Install via Composer

```bash
composer require litepie/masters
```

### Step 2: Publish Package Assets

```bash
# Publish everything
php artisan vendor:publish --provider="Litepie\Masters\MastersServiceProvider"

# Or publish selectively
php artisan vendor:publish --provider="Litepie\Masters\MastersServiceProvider" --tag="config"
php artisan vendor:publish --provider="Litepie\Masters\MastersServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="Litepie\Masters\MastersServiceProvider" --tag="views"
php artisan vendor:publish --provider="Litepie\Masters\MastersServiceProvider" --tag="assets"
```

### Step 3: Run Database Migrations

```bash
php artisan migrate
```

### Step 4: Install and Setup Package

```bash
# Install package and create default master types
php artisan masters:install

# Seed sample data (optional)
php artisan masters:seed
```

## Configuration

The package configuration file `config/masters.php` provides extensive customization options:

### Multi-Tenancy Strategy

```php
// Single database with tenant_id column (default)
'tenancy_strategy' => 'single_db',

// Multiple databases per tenant
'tenancy_strategy' => 'multi_db',

// Schema-based separation
'tenancy_strategy' => 'schema',
```

### Cache Configuration

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'prefix' => 'masters',
],
```

### API Settings

```php
'api' => [
    'enabled' => true,
    'prefix' => 'api/masters',
    'middleware' => ['api', 'throttle:60,1'],
    'rate_limit' => '60,1',
],
```

## Quick Start

### Basic Usage with Facade

```php
use Litepie\Masters\Facades\Masters;

// Set tenant context
Masters::setTenant('tenant_123');

// Get all countries
$countries = Masters::get('countries');

// Create new master data
$country = Masters::create('countries', [
    'name' => 'United States',
    'code' => 'US',
    'iso_code' => 'USA',
    'is_active' => true
]);

// Update master data
Masters::update('countries', $country->id, [
    'name' => 'United States of America'
]);

// Search master data
$results = Masters::search('countries', 'United');
```

### Using Eloquent Models Directly

```php
use Litepie\Masters\Models\MasterData;
use Litepie\Masters\Models\MasterType;

// Get countries with states
$countries = MasterData::ofType('countries')
    ->with('children')
    ->active()
    ->get();

// Create hierarchical data
$usa = MasterData::ofType('countries')->where('code', 'US')->first();
$california = MasterData::create([
    'master_type_id' => MasterType::where('slug', 'states')->first()->id,
    'name' => 'California',
    'code' => 'CA',
    'parent_id' => $usa->id,
    'is_active' => true
]);
```

## Multi-Tenancy

### Tenant Context Management

```php
use Litepie\Masters\Facades\Masters;

// Set current tenant
Masters::setTenant('tenant_123');

// Get current tenant
$currentTenant = Masters::getCurrentTenant();

// Execute in tenant context
Masters::runForTenant('tenant_456', function() {
    $data = Masters::get('categories');
    // This will only get categories for tenant_456
});

// Clear tenant context
Masters::setTenant(null);
```

### Global vs Tenant-Specific Data

```php
// Global data (available to all tenants)
$countries = MasterData::ofType('countries')->global()->get();

// Tenant-specific data
$categories = MasterData::ofType('categories')->tenant('tenant_123')->get();

// Both global and tenant data
$currencies = MasterData::ofType('currencies')->tenantOrGlobal()->get();
```

## Master Data Types

### Default Master Types

The package comes with these pre-configured master types:

- **Countries** (Global): ISO countries with codes
- **States/Provinces** (Global): Hierarchical under countries
- **Cities** (Global): Hierarchical under states
- **Categories** (Tenant-specific): Product/service categories
- **Currencies** (Global): ISO currencies with codes
- **Languages** (Global): ISO languages with codes

### Creating Custom Master Types

```php
use Litepie\Masters\Models\MasterType;

$departmentType = MasterType::create([
    'name' => 'Departments',
    'slug' => 'departments',
    'description' => 'Organizational departments',
    'is_hierarchical' => true,
    'is_global' => false, // Tenant-specific
    'validation_rules' => [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10|unique:master_data,code',
        'manager_email' => 'nullable|email'
    ],
    'additional_fields' => [
        'manager_email' => 'string',
        'budget' => 'decimal',
        'location' => 'string'
    ]
]);

// Add data to custom type
$hr = Masters::create('departments', [
    'name' => 'Human Resources',
    'code' => 'HR',
    'additional_data' => [
        'manager_email' => 'hr.manager@company.com',
        'budget' => 50000,
        'location' => 'Building A, Floor 2'
    ]
]);
```

## Usage Examples

### Hierarchical Data Operations

```php
// Get full tree structure
$categoryTree = Masters::getHierarchical('categories');

// Get children of specific parent
$subCategories = Masters::getHierarchical('categories', $parentId);

// Get path of an item
$category = MasterData::find(123);
echo $category->getPath(); // "Electronics > Computers > Laptops"
echo $category->getPath(' / '); // "Electronics / Computers / Laptops"

// Check relationships
if ($category->hasChildren()) {
    $subcategories = $category->children;
}

if (!$category->isRoot()) {
    $parent = $category->parent;
}

// Get all ancestors
$ancestors = $category->ancestors();

// Get all descendants
$descendants = $category->descendants;
```

### Advanced Querying

```php
// Complex filtering
$data = Masters::get('countries', [
    'search' => 'United',
    'is_active' => true,
    'parent_id' => null
]);

// Using Eloquent scopes
$activeCountries = MasterData::ofType('countries')
    ->active()
    ->rootLevel()
    ->orderBy('name')
    ->get();

// Search with relationships
$countriesWithStates = MasterData::ofType('countries')
    ->whereHas('children', function($query) {
        $query->where('is_active', true);
    })
    ->with(['children' => function($query) {
        $query->active()->orderBy('name');
    }])
    ->get();
```

### Bulk Operations

```php
// Import from array
$countries = [
    ['name' => 'India', 'code' => 'IN', 'iso_code' => 'IND'],
    ['name' => 'China', 'code' => 'CN', 'iso_code' => 'CHN'],
    ['name' => 'Japan', 'code' => 'JP', 'iso_code' => 'JPN'],
];

$results = Masters::import('countries', $countries);
// Returns: ['success' => 3, 'failed' => 0, 'errors' => []]

// Export to array
$exportData = Masters::export('countries', [
    'is_active' => true
]);

// Export specific fields
$exportData = MasterData::ofType('countries')
    ->select(['name', 'code', 'iso_code'])
    ->active()
    ->get()
    ->toArray();
```

## API Documentation

### Authentication

All API endpoints support standard Laravel authentication and can be protected with middleware:

```php
// In config/masters.php
'api' => [
    'middleware' => ['api', 'auth:sanctum', 'throttle:60,1'],
],
```

### Endpoints

#### Master Types

```http
GET    /api/masters/types              # List all master types
POST   /api/masters/types              # Create new master type
GET    /api/masters/types/{id}         # Get specific master type
PUT    /api/masters/types/{id}         # Update master type
DELETE /api/masters/types/{id}         # Delete master type
```

#### Master Data

```http
GET    /api/masters/{type}             # List data for type
POST   /api/masters/{type}             # Create new data
GET    /api/masters/{type}/{id}        # Get specific data
PUT    /api/masters/{type}/{id}        # Update data
DELETE /api/masters/{type}/{id}        # Delete data

# Specialized endpoints
GET    /api/masters/{type}/tree        # Get hierarchical tree
GET    /api/masters/{type}/children/{parentId} # Get children
GET    /api/masters/{type}/search/{query}      # Search data
POST   /api/masters/{type}/import      # Bulk import
GET    /api/masters/{type}/export      # Export data
```

#### Convenience Endpoints

```http
GET    /api/masters/countries          # List countries
GET    /api/masters/states/{countryId} # List states by country
GET    /api/masters/cities/{stateId}   # List cities by state
GET    /api/masters/currencies         # List currencies
GET    /api/masters/languages          # List languages
```

### Request/Response Examples

#### Create Master Data

```http
POST /api/masters/categories
Content-Type: application/json

{
    "name": "Electronics",
    "code": "ELEC",
    "description": "Electronic products and devices",
    "parent_id": null,
    "is_active": true,
    "additional_data": {
        "icon": "fas fa-laptop",
        "color": "#3498db"
    }
}
```

Response:
```json
{
    "data": {
        "id": 123,
        "name": "Electronics",
        "code": "ELEC",
        "slug": "electronics",
        "description": "Electronic products and devices",
        "parent_id": null,
        "is_active": true,
        "additional_data": {
            "icon": "fas fa-laptop",
            "color": "#3498db"
        },
        "created_at": "2025-08-21T10:30:00Z",
        "updated_at": "2025-08-21T10:30:00Z"
    },
    "message": "Master data created successfully"
}
```

#### Get Hierarchical Data

```http
GET /api/masters/categories/tree
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "name": "Electronics",
            "children": [
                {
                    "id": 2,
                    "name": "Computers",
                    "children": [
                        {
                            "id": 3,
                            "name": "Laptops",
                            "children": []
                        }
                    ]
                }
            ]
        }
    ],
    "type": "categories"
}
```

## Console Commands

### Installation & Setup

```bash
# Install package and create default types
php artisan masters:install

# Force reinstall
php artisan masters:install --force

# Seed all sample data
php artisan masters:seed

# Seed specific type
php artisan masters:seed countries
```

### Data Import

```bash
# Import from CSV
php artisan masters:import countries /path/to/countries.csv

# Import from JSON
php artisan masters:import currencies /path/to/currencies.json
```

### Maintenance

```bash
# Clear all masters cache
php artisan cache:clear --tags=masters

# Validate data integrity
php artisan masters:validate

# Generate master data report
php artisan masters:report
```

## Admin Interface

### Accessing the Admin Panel

The package provides a web-based admin interface accessible at `/admin/masters` (configurable):

- **Dashboard**: Overview of all master types and data counts
- **Master Types Management**: Create, edit, delete master types
- **Master Data Management**: CRUD operations for all master data
- **Import/Export Interface**: Web-based bulk operations
- **Search & Filter**: Advanced search across all data types

### Customizing Admin Routes

```php
// In config/masters.php
'admin' => [
    'enabled' => true,
    'prefix' => 'admin/masters',
    'middleware' => ['web', 'auth', 'can:manage-masters'],
],
```

## Advanced Features

### Custom Validation Rules

```php
// In MasterType creation
$type = MasterType::create([
    'name' => 'Products',
    'slug' => 'products',
    'validation_rules' => [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|unique:master_data,code',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:master_data,id'
    ]
]);
```

### Event Hooks

```php
use Litepie\Masters\Models\MasterData;

// Listen for master data events
MasterData::creating(function ($masterData) {
    // Auto-generate slug
    if (empty($masterData->slug)) {
        $masterData->slug = Str::slug($masterData->name);
    }
});

MasterData::created(function ($masterData) {
    // Clear cache, send notifications, etc.
    Log::info("Master data created: {$masterData->name}");
});
```

### Custom Cache Strategies

```php
// Custom cache key generation
class CustomMasterData extends MasterData
{
    public function getCacheKey(): string
    {
        return "custom:master:{$this->masterType->slug}:{$this->id}:{$this->tenant_id}";
    }
}
```

### Database Optimization

```php
// Add custom indexes in migration
Schema::table('master_data', function (Blueprint $table) {
    $table->index(['tenant_id', 'master_type_id', 'is_active']);
    $table->index(['parent_id', 'sort_order']);
    $table->fullText(['name', 'description']); // For advanced search
});
```

## Testing

### Unit Tests

```php
use Litepie\Masters\Facades\Masters;
use Litepie\Masters\Models\MasterType;

class MasterDataTest extends TestCase
{
    public function test_can_create_master_data()
    {
        // Create master type
        $type = MasterType::create([
            'name' => 'Test Categories',
            'slug' => 'test-categories',
            'is_global' => false
        ]);

        // Set tenant
        Masters::setTenant('test_tenant');

        // Create master data
        $category = Masters::create('test-categories', [
            'name' => 'Test Category',
            'code' => 'TEST'
        ]);

        $this->assertNotNull($category);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test_tenant', $category->tenant_id);
    }

    public function test_hierarchical_relationships()
    {
        $parent = Masters::create('categories', [
            'name' => 'Parent Category',
            'code' => 'PARENT'
        ]);

        $child = Masters::create('categories', [
            'name' => 'Child Category',
            'code' => 'CHILD',
            'parent_id' => $parent->id
        ]);

        $this->assertTrue($parent->hasChildren());
        $this->assertFalse($child->isRoot());
        $this->assertEquals($parent->id, $child->parent->id);
    }
}
```

### API Tests

```php
public function test_api_endpoints()
{
    $response = $this->getJson('/api/masters/countries');
    $response->assertStatus(200)
             ->assertJsonStructure(['data', 'count']);

    $response = $this->postJson('/api/masters/countries', [
        'name' => 'Test Country',
        'code' => 'TC',
        'is_active' => true
    ]);
    $response->assertStatus(201);
}
```

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/litepie/masters.git

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer cs-check

# Fix code style
composer cs-fix
```

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## Security

If you discover any security-related issues, please email security@litepie.com instead of using the issue tracker.

## Credits

- [Litepie Team](https://github.com/litepie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
