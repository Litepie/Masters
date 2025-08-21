<?php

use Illuminate\Support\Facades\Route;
use Litepie\Masters\Http\Controllers\Web\MasterDataController;
use Litepie\Masters\Http\Controllers\Web\MasterTypeController;

Route::group([
    'prefix' => config('masters.admin.prefix', 'admin/masters'),
    'middleware' => config('masters.admin.middleware', ['web', 'auth']),
], function () {
    
    // Dashboard
    Route::get('/', [MasterDataController::class, 'dashboard'])->name('masters.dashboard');
    
    // Master Types Management
    Route::resource('types', MasterTypeController::class, [
        'names' => [
            'index' => 'masters.types.index',
            'create' => 'masters.types.create',
            'store' => 'masters.types.store',
            'show' => 'masters.types.show',
            'edit' => 'masters.types.edit',
            'update' => 'masters.types.update',
            'destroy' => 'masters.types.destroy',
        ]
    ]);
    
    // Master Data Management
    Route::get('/{type}', [MasterDataController::class, 'index'])->name('masters.data.index');
    Route::get('/{type}/create', [MasterDataController::class, 'create'])->name('masters.data.create');
    Route::post('/{type}', [MasterDataController::class, 'store'])->name('masters.data.store');
    Route::get('/{type}/{id}', [MasterDataController::class, 'show'])->name('masters.data.show');
    Route::get('/{type}/{id}/edit', [MasterDataController::class, 'edit'])->name('masters.data.edit');
    Route::put('/{type}/{id}', [MasterDataController::class, 'update'])->name('masters.data.update');
    Route::delete('/{type}/{id}', [MasterDataController::class, 'destroy'])->name('masters.data.destroy');
    
    // Import/Export
    Route::get('/{type}/import', [MasterDataController::class, 'importForm'])->name('masters.data.import');
    Route::post('/{type}/import', [MasterDataController::class, 'import'])->name('masters.data.import.process');
    Route::get('/{type}/export', [MasterDataController::class, 'export'])->name('masters.data.export');
});
