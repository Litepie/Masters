<?php

namespace Litepie\Masters\Repositories;

use Illuminate\Support\Collection;
use Litepie\Masters\Contracts\MasterDataRepository;
use Litepie\Masters\Models\MasterData;
use Litepie\Masters\Models\MasterType;
use Litepie\Masters\Services\TenancyService;

class EloquentMasterDataRepository implements MasterDataRepository
{
    public function __construct(
        protected TenancyService $tenancyService
    ) {}

    /**
     * Get master data for a specific type.
     */
    public function get(string $type, array $filters = []): Collection
    {
        $query = MasterData::whereHas('masterType', function ($q) use ($type) {
            $q->where('slug', $type);
        })->active();

        // Apply filters
        foreach ($filters as $key => $value) {
            if ($key === 'parent_id') {
                $query->where('parent_id', $value);
            } elseif ($key === 'search') {
                $query->where(function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%")
                      ->orWhere('code', 'like', "%{$value}%")
                      ->orWhere('description', 'like', "%{$value}%");
                });
            } elseif (in_array($key, ['name', 'code', 'iso_code', 'is_active'])) {
                $query->where($key, $value);
            }
        }

        return $query->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * Create new master data.
     */
    public function create(string $type, array $data): mixed
    {
        $masterType = MasterType::where('slug', $type)->first();
        
        if (!$masterType) {
            throw new \InvalidArgumentException("Master type '{$type}' not found");
        }

        $data['master_type_id'] = $masterType->id;

        // Auto-generate slug if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        return MasterData::create($data);
    }

    /**
     * Update master data.
     */
    public function update(string $type, int $id, array $data): bool
    {
        $masterData = MasterData::whereHas('masterType', function ($q) use ($type) {
            $q->where('slug', $type);
        })->findOrFail($id);

        // Auto-update slug if name changed
        if (isset($data['name']) && $data['name'] !== $masterData->name && empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        return $masterData->update($data);
    }

    /**
     * Delete master data.
     */
    public function delete(string $type, int $id): bool
    {
        $masterData = MasterData::whereHas('masterType', function ($q) use ($type) {
            $q->where('slug', $type);
        })->findOrFail($id);

        return $masterData->delete();
    }

    /**
     * Get hierarchical data for a type.
     */
    public function getHierarchical(string $type, ?int $parentId = null): Collection
    {
        $query = MasterData::whereHas('masterType', function ($q) use ($type) {
            $q->where('slug', $type);
        })->active();

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        return $query->with(['children' => function ($q) {
            $q->active()->orderBy('sort_order')->orderBy('name');
        }])->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * Search master data.
     */
    public function search(string $type, string $query, array $filters = []): Collection
    {
        $filters['search'] = $query;
        return $this->get($type, $filters);
    }

    /**
     * Import data from array.
     */
    public function import(string $type, array $data): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($data as $index => $item) {
            try {
                $this->create($type, $item);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 1,
                    'data' => $item,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Export data to array.
     */
    public function export(string $type, array $filters = []): array
    {
        $data = $this->get($type, $filters);

        return $data->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'code' => $item->code,
                'iso_code' => $item->iso_code,
                'description' => $item->description,
                'parent_id' => $item->parent_id,
                'sort_order' => $item->sort_order,
                'is_active' => $item->is_active,
                'additional_data' => $item->additional_data,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();
    }
}
