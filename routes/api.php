<?php

use Illuminate\Support\Facades\Route;
use Litepie\Masters\Http\Controllers\Api\MasterDataController;
use Litepie\Masters\Http\Controllers\Api\MasterTypeController;

Route::group([
    'prefix' => config('masters.api.prefix', 'api/masters'),
    'middleware' => config('masters.api.middleware', ['api', 'auth']),
], function () {
    
    // Master Types
    Route::apiResource('types', MasterTypeController::class);
    
    // Master Data
    Route::get('/{type}', [MasterDataController::class, 'index']);
    Route::post('/{type}', [MasterDataController::class, 'store']);
    Route::get('/{type}/{id}', [MasterDataController::class, 'show']);
    Route::put('/{type}/{id}', [MasterDataController::class, 'update']);
    Route::delete('/{type}/{id}', [MasterDataController::class, 'destroy']);
    
    // Hierarchical data
    Route::get('/{type}/tree', [MasterDataController::class, 'tree']);
    Route::get('/{type}/children/{parentId?}', [MasterDataController::class, 'children']);
    
    // Search
    Route::get('/{type}/search/{query}', [MasterDataController::class, 'search']);
    
    // Import/Export
    Route::post('/{type}/import', [MasterDataController::class, 'import']);
    Route::get('/{type}/export', [MasterDataController::class, 'export']);
    
    // Common master data endpoints
    Route::get('/countries', [MasterDataController::class, 'countries']);
    Route::get('/states/{countryId?}', [MasterDataController::class, 'states']);
    Route::get('/cities/{stateId?}', [MasterDataController::class, 'cities']);
    Route::get('/currencies', [MasterDataController::class, 'currencies']);
    Route::get('/languages', [MasterDataController::class, 'languages']);
});
