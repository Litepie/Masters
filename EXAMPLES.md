# Laravel Masters Package - Usage Examples

## Installation

```bash
# Install via Composer
composer require litepie/masters

# Publish migrations and config
php artisan vendor:publish --provider="Litepie\Masters\MastersServiceProvider"

# Run migrations
php artisan migrate

# Install package and seed sample data
php artisan masters:install
```

## Multi-Tenancy Setup

### Setting Current Tenant

```php
use Litepie\Masters\Facades\Masters;

// Set current tenant for subsequent operations
Masters::setTenant('tenant_123');

// Or use dependency injection
app('masters')->setTenant('tenant_123');
```

### Working with Global vs Tenant Data

```php
// Get global countries (available to all tenants)
$countries = Masters::get('countries');

// Set tenant and get tenant-specific categories
Masters::setTenant('tenant_123');
$categories = Masters::get('categories');

// Explicitly get global data
$currencies = \Litepie\Masters\Models\MasterData::ofType('currencies')
    ->global()
    ->get();

// Get tenant-specific data
$departments = \Litepie\Masters\Models\MasterData::ofType('departments')
    ->tenant('tenant_123')
    ->get();
```

## Basic CRUD Operations

### Creating Master Data

```php
// Create a new country
$country = Masters::create('countries', [
    'name' => 'United States',
    'code' => 'US',
    'iso_code' => 'USA',
    'is_active' => true
]);

// Create hierarchical data (state under country)
$state = Masters::create('states', [
    'name' => 'California',
    'code' => 'CA',
    'parent_id' => $country->id,
    'is_active' => true
]);
```

### Reading Master Data

```php
// Get all countries
$countries = Masters::get('countries');

// Get with filters
$activeCountries = Masters::get('countries', ['is_active' => true]);

// Search
$results = Masters::search('countries', 'United');

// Get hierarchical data
$countriesWithStates = Masters::getHierarchical('countries');

// Get children of a specific parent
$californiaStates = Masters::getHierarchical('states', $californiaId);
```

### Updating Master Data

```php
Masters::update('countries', $countryId, [
    'name' => 'United States of America',
    'description' => 'North American country'
]);
```

### Deleting Master Data

```php
// Soft delete
Masters::delete('countries', $countryId);
```

## Advanced Usage

### Custom Master Types

```php
use Litepie\Masters\Models\MasterType;
use Litepie\Masters\Models\MasterData;

// Create custom master type
$departmentType = MasterType::create([
    'name' => 'Departments',
    'slug' => 'departments',
    'is_hierarchical' => true,
    'is_global' => false, // Tenant-specific
    'validation_rules' => [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10|unique:master_data,code'
    ]
]);

// Add data to custom type
$hr = Masters::create('departments', [
    'name' => 'Human Resources',
    'code' => 'HR',
    'description' => 'Manages employee relations'
]);

$payroll = Masters::create('departments', [
    'name' => 'Payroll',
    'code' => 'PAY',
    'parent_id' => $hr->id,
    'description' => 'Handles salary processing'
]);
```

### Bulk Import/Export

```php
// Import from array
$data = [
    ['name' => 'India', 'code' => 'IN', 'iso_code' => 'IND'],
    ['name' => 'China', 'code' => 'CN', 'iso_code' => 'CHN'],
];

$results = Masters::import('countries', $data);
// Returns: ['success' => 2, 'failed' => 0, 'errors' => []]

// Export to array
$exportData = Masters::export('countries', ['is_active' => true]);

// CLI import
php artisan masters:import countries /path/to/countries.csv
php artisan masters:import currencies /path/to/currencies.json
```

### Using Models Directly

```php
use Litepie\Masters\Models\MasterData;
use Litepie\Masters\Models\MasterType;

// Query with relationships
$countries = MasterData::with(['children' => function($query) {
    $query->where('is_active', true);
}])
->ofType('countries')
->active()
->get();

// Get hierarchical path
$city = MasterData::find(123);
echo $city->getPath(); // "United States > California > Los Angeles"

// Check relationships
if ($city->hasChildren()) {
    $districts = $city->children;
}

if (!$city->isRoot()) {
    $state = $city->parent;
}
```

### Caching

```php
// Cache is automatically handled, but you can control it
$country = MasterData::find(1);

// Clear specific cache
$country->clearCache();

// Clear all master data cache
MasterData::clearAllCache();

// Disable cache for specific operation
config(['masters.cache.enabled' => false]);
$data = Masters::get('countries');
```

### API Usage

```javascript
// Frontend JavaScript examples

// Get all countries
fetch('/api/masters/countries')
    .then(response => response.json())
    .then(data => console.log(data));

// Get states for a country
fetch('/api/masters/states/1') // where 1 is country ID
    .then(response => response.json())
    .then(states => console.log(states));

// Search currencies
fetch('/api/masters/currencies/search/USD')
    .then(response => response.json())
    .then(results => console.log(results));

// Create new master data
fetch('/api/masters/categories', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        name: 'Electronics',
        code: 'ELEC',
        is_active: true
    })
});
```

## Multi-Database Tenancy

```php
// For multi-database tenancy
// Set database connection per tenant
config(['database.connections.tenant_123' => [
    'driver' => 'mysql',
    'host' => 'tenant123.db.server.com',
    'database' => 'tenant_123_masters',
    // ... other config
]]);

// Use tenant-specific connection
Masters::setTenant('tenant_123');
$data = Masters::get('categories');
```

## Testing

```php
use Litepie\Masters\Facades\Masters;
use Litepie\Masters\Models\MasterType;

class MasterDataTest extends TestCase
{
    public function test_can_create_master_data()
    {
        // Create master type
        MasterType::create([
            'name' => 'Test Categories',
            'slug' => 'test-categories',
            'is_global' => false
        ]);

        // Set test tenant
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
}
```
