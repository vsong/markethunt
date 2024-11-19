<?php

namespace App\Cache;

use Predis\Client;

class RedisCacheService implements ICacheService
{
    private Client $client;
    private string $prefix;

    public function __construct(Client $client, string $prefix) {
        $this->client = $client;
        $this->prefix = $prefix . ':';
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): ?string {
        return $this->client->get($this->prefix . $key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, string $value, int $timeoutSeconds = 60) {
        $this->client->setex($this->prefix . $key, $timeoutSeconds, $value);
    }
}