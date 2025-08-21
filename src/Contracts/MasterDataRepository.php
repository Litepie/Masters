<?php

namespace Litepie\Masters\Contracts;

use Illuminate\Support\Collection;

interface MasterDataRepository
{
    /**
     * Get master data for a specific type.
     */
    public function get(string $type, array $filters = []): Collection;

    /**
     * Create new master data.
     */
    public function create(string $type, array $data): mixed;

    /**
     * Update master data.
     */
    public function update(string $type, int $id, array $data): bool;

    /**
     * Delete master data.
     */
    public function delete(string $type, int $id): bool;

    /**
     * Get hierarchical data for a type.
     */
    public function getHierarchical(string $type, ?int $parentId = null): Collection;

    /**
     * Search master data.
     */
    public function search(string $type, string $query, array $filters = []): Collection;

    /**
     * Import data from array.
     */
    public function import(string $type, array $data): array;

    /**
     * Export data to array.
     */
    public function export(string $type, array $filters = []): array;
}
