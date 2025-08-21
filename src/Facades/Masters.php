<?php

namespace Litepie\Masters\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection get(string $type, array $filters = [])
 * @method static mixed create(string $type, array $data)
 * @method static bool update(string $type, int $id, array $data)
 * @method static bool delete(string $type, int $id)
 * @method static \Illuminate\Support\Collection getHierarchical(string $type, ?int $parentId = null)
 * @method static \Illuminate\Support\Collection search(string $type, string $query, array $filters = [])
 * @method static array import(string $type, array $data)
 * @method static array export(string $type, array $filters = [])
 * @method static \Litepie\Masters\MastersManager setTenant(?string $tenantId)
 * @method static ?string getCurrentTenant()
 */
class Masters extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'masters';
    }
}
