<?php

namespace Litepie\Masters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Litepie\Masters\Traits\HasTenancy;
use Litepie\Masters\Traits\Cacheable;

class MasterData extends Model
{
    use SoftDeletes, HasTenancy, Cacheable;

    protected $table = 'master_data';

    protected $fillable = [
        'master_type_id',
        'name',
        'slug',
        'code',
        'iso_code',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'additional_data',
        'meta_data',
        'tenant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'additional_data' => 'array',
        'meta_data' => 'array',
    ];

    /**
     * Get the master type.
     */
    public function masterType(): BelongsTo
    {
        return $this->belongsTo(MasterType::class);
    }

    /**
     * Get the parent master data.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MasterData::class, 'parent_id');
    }

    /**
     * Get child master data.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MasterData::class, 'parent_id');
    }

    /**
     * Get all descendants (recursive children).
     */
    public function descendants(): HasMany
    {
        return $this->hasMany(MasterData::class, 'parent_id')->with('descendants');
    }

    /**
     * Get all ancestors (recursive parents).
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Scope for active data.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root level data (no parent).
     */
    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for specific master type.
     */
    public function scopeOfType($query, $typeSlug)
    {
        return $query->whereHas('masterType', function ($q) use ($typeSlug) {
            $q->where('slug', $typeSlug);
        });
    }

    /**
     * Scope for hierarchical tree structure.
     */
    public function scopeTree($query)
    {
        return $query->with('children')->whereNull('parent_id');
    }

    /**
     * Get cache key for this model.
     */
    public function getCacheKey(): string
    {
        $typeSlug = $this->masterType?->slug ?? 'unknown';
        return "master_data:{$typeSlug}:{$this->id}:{$this->tenant_id}";
    }

    /**
     * Get full hierarchical path.
     */
    public function getPath(string $separator = ' > '): string
    {
        $path = collect([$this->name]);
        $current = $this->parent;

        while ($current) {
            $path->prepend($current->name);
            $current = $current->parent;
        }

        return $path->implode($separator);
    }

    /**
     * Check if this data has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this data is a root level item.
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this data is a leaf (has no children).
     */
    public function isLeaf(): bool
    {
        return !$this->hasChildren();
    }
}
