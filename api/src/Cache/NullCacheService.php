<?php

namespace App\Cache;

class NullCacheService implements ICacheService
{
    public function get(string $key): ?string
    {
        return null;
    }

    public function set(string $key, string $value, int $timeoutSeconds = 60)
    {
    }

    public function registerItemView(int $item_id): void
    {
    }

    public function getTopViewedItemIds(): array
    {
        return [];
    }
}