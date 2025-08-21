<?php

namespace Litepie\Masters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Litepie\Masters\Traits\HasTenancy;
use Litepie\Masters\Traits\Cacheable;

class MasterType extends Model
{
    use SoftDeletes, HasTenancy, Cacheable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_hierarchical',
        'is_global',
        'parent_type_slug',
        'validation_rules',
        'additional_fields',
        'is_active',
        'tenant_id',
    ];

    protected $casts = [
        'is_hierarchical' => 'boolean',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'validation_rules' => 'array',
        'additional_fields' => 'array',
    ];

    /**
     * Get the master data for this type.
     */
    public function masterData(): HasMany
    {
        return $this->hasMany(MasterData::class);
    }

    /**
     * Get the parent type.
     */
    public function parentType(): ?MasterType
    {
        if (!$this->parent_type_slug) {
            return null;
        }

        return static::where('slug', $this->parent_type_slug)->first();
    }

    /**
     * Get child types.
     */
    public function childTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('parent_type_slug', $this->slug)->get();
    }

    /**
     * Scope for global types.
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope for tenant-specific types.
     */
    public function scopeTenantSpecific($query)
    {
        return $query->where('is_global', false);
    }

    /**
     * Scope for active types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get cache key for this model.
     */
    public function getCacheKey(): string
    {
        return "master_type:{$this->slug}:{$this->tenant_id}";
    }
}
