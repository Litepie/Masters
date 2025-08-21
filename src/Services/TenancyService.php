<?php

namespace Litepie\Masters\Services;

class TenancyService
{
    protected ?string $currentTenant = null;

    /**
     * Set the current tenant.
     */
    public function setCurrentTenant(?string $tenantId): void
    {
        $this->currentTenant = $tenantId;
    }

    /**
     * Get the current tenant.
     */
    public function getCurrentTenant(): ?string
    {
        return $this->currentTenant;
    }

    /**
     * Clear the current tenant.
     */
    public function clearCurrentTenant(): void
    {
        $this->currentTenant = null;
    }

    /**
     * Execute a callback in the context of a specific tenant.
     */
    public function runForTenant(?string $tenantId, callable $callback)
    {
        $previousTenant = $this->currentTenant;
        
        try {
            $this->setCurrentTenant($tenantId);
            return $callback();
        } finally {
            $this->setCurrentTenant($previousTenant);
        }
    }

    /**
     * Execute a callback without any tenant context.
     */
    public function runWithoutTenant(callable $callback)
    {
        return $this->runForTenant(null, $callback);
    }

    /**
     * Get the tenancy strategy from config.
     */
    public function getTenancyStrategy(): string
    {
        return config('masters.tenancy_strategy', 'single_db');
    }

    /**
     * Get the tenant column name from config.
     */
    public function getTenantColumn(): string
    {
        return config('masters.tenant_column', 'tenant_id');
    }

    /**
     * Check if global data is enabled.
     */
    public function isGlobalDataEnabled(): bool
    {
        return config('masters.enable_global_data', true);
    }
}
