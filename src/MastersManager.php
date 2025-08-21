<?php

namespace Litepie\Masters;

use Illuminate\Support\Manager;
use Litepie\Masters\Contracts\MasterDataRepository;
use Litepie\Masters\Repositories\EloquentMasterDataRepository;
use Litepie\Masters\Services\TenancyService;

class MastersManager extends Manager
{
    protected ?string $currentTenant = null;

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return 'eloquent';
    }

    /**
     * Create an instance of the Eloquent driver.
     */
    protected function createEloquentDriver(): MasterDataRepository
    {
        return new EloquentMasterDataRepository(
            $this->container->make(TenancyService::class)
        );
    }

    /**
     * Set the current tenant.
     */
    public function setTenant(?string $tenantId): self
    {
        $this->currentTenant = $tenantId;
        
        if ($tenantService = $this->container->make(TenancyService::class)) {
            $tenantService->setCurrentTenant($tenantId);
        }

        return $this;
    }

    /**
     * Get the current tenant.
     */
    public function getCurrentTenant(): ?string
    {
        return $this->currentTenant;
    }

    /**
     * Get master data for a specific type.
     */
    public function get(string $type, array $filters = []): \Illuminate\Support\Collection
    {
        return $this->driver()->get($type, $filters);
    }

    /**
     * Create new master data.
     */
    public function create(string $type, array $data): mixed
    {
        return $this->driver()->create($type, $data);
    }

    /**
     * Update master data.
     */
    public function update(string $type, int $id, array $data): bool
    {
        return $this->driver()->update($type, $id, $data);
    }

    /**
     * Delete master data.
     */
    public function delete(string $type, int $id): bool
    {
        return $this->driver()->delete($type, $id);
    }

    /**
     * Get hierarchical data for a type.
     */
    public function getHierarchical(string $type, ?int $parentId = null): \Illuminate\Support\Collection
    {
        return $this->driver()->getHierarchical($type, $parentId);
    }

    /**
     * Search master data.
     */
    public function search(string $type, string $query, array $filters = []): \Illuminate\Support\Collection
    {
        return $this->driver()->search($type, $query, $filters);
    }

    /**
     * Import data from array.
     */
    public function import(string $type, array $data): array
    {
        return $this->driver()->import($type, $data);
    }

    /**
     * Export data to array.
     */
    public function export(string $type, array $filters = []): array
    {
        return $this->driver()->export($type, $filters);
    }
}
