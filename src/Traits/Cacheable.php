<?php

namespace Litepie\Masters\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /**
     * Get cache TTL from config.
     */
    protected function getCacheTtl(): int
    {
        return config('masters.cache.ttl', 3600);
    }

    /**
     * Get cache prefix from config.
     */
    protected function getCachePrefix(): string
    {
        return config('masters.cache.prefix', 'masters');
    }

    /**
     * Check if caching is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return config('masters.cache.enabled', true);
    }

    /**
     * Get cache key for this model.
     */
    abstract public function getCacheKey(): string;

    /**
     * Get data from cache or execute callback.
     */
    public function remember(callable $callback, ?int $ttl = null)
    {
        if (!$this->isCacheEnabled()) {
            return $callback();
        }

        $key = $this->getCacheKey();
        $ttl = $ttl ?? $this->getCacheTtl();

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Clear cache for this model.
     */
    public function clearCache(): void
    {
        if ($this->isCacheEnabled()) {
            Cache::forget($this->getCacheKey());
        }
    }

    /**
     * Clear all cache with prefix.
     */
    public static function clearAllCache(): void
    {
        $prefix = config('masters.cache.prefix', 'masters');
        
        // This depends on the cache driver being used
        // For Redis, you could use Cache::tags() if supported
        Cache::flush(); // This clears all cache - use with caution
    }

    /**
     * Boot the trait.
     */
    protected static function bootCacheable(): void
    {
        static::saved(function ($model) {
            $model->clearCache();
        });

        static::deleted(function ($model) {
            $model->clearCache();
        });
    }
}
