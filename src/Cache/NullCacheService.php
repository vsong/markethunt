<?php

namespace App\Cache;

class NullCacheService implements ICacheService
{

    /**
     * @inheritDoc
     */
    public function get(string $key): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, string $value, int $timeoutSeconds = 60)
    {
    }
}