<?php

namespace Litepie\Masters\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasTenancy
{
    /**
     * Boot the trait.
     */
    protected static function bootHasTenancy(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenancy = app('masters')->getCurrentTenant();
            
            if ($tenancy && !$builder->getQuery()->wheres) {
                $builder->where(function ($query) use ($tenancy) {
                    $query->where('tenant_id', $tenancy)
                          ->orWhere('is_global', true);
                });
            }
        });

        static::creating(function ($model) {
            if (empty($model->tenant_id) && !$model->is_global) {
                $model->tenant_id = app('masters')->getCurrentTenant();
            }
        });
    }

    /**
     * Scope for tenant-specific data.
     */
    public function scopeTenant(Builder $query, ?string $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? app('masters')->getCurrentTenant();
        
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope for global data.
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope for both tenant and global data.
     */
    public function scopeTenantOrGlobal(Builder $query, ?string $tenantId = null): Builder
    {
        $tenantId = $tenantId ?? app('masters')->getCurrentTenant();
        
        return $query->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhere('is_global', true);
        });
    }

    /**
     * Check if the model belongs to a specific tenant.
     */
    public function belongsToTenant(?string $tenantId = null): bool
    {
        $tenantId = $tenantId ?? app('masters')->getCurrentTenant();
        
        return $this->tenant_id === $tenantId;
    }

    /**
     * Check if the model is global.
     */
    public function isGlobal(): bool
    {
        return $this->is_global ?? false;
    }
}
