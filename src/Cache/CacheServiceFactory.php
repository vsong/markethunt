<?php

namespace App\Cache;

use Predis\Client;

class CacheServiceFactory
{
    /**
     * Creates an {@see ICacheService} instance based on the environment settings
     * @return ICacheService
     */
    public static function CreateFromEnv(): ICacheService {
        if ($_ENV['USE_REDIS'] !== 'true') {
            return new NullCacheService();
        }

        $client = new Client([
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
        ]);

        return new RedisCacheService($client, $_ENV['REDIS_PREFIX']);
    }
}