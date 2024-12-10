<?php

namespace App\Cache;

use App\Util\DateUtils;
use Predis\Client;

class RedisCacheService implements ICacheService
{
    private const TOP_ITEMS_HOURS_HISTORY = 48;
    private const TOP_ITEMS_QUEUE_KEY = "top-items-queue";
    private const TOP_ITEMS_COUNTS_KEY = "top-items-counts";

    private Client $client;
    private string $prefix; // TODO use Predis client's prefix functionality instead

    public function __construct(Client $client, string $prefix) {
        $this->client = $client;
        $this->prefix = $prefix . ':';
    }

    public function get(string $key): ?string {
        return $this->client->get($this->prefix . $key);
    }

    public function set(string $key, string $value, int $timeoutSeconds = 60) {
        $this->client->setex($this->prefix . $key, $timeoutSeconds, $value);
    }

    public function registerItemView(int $item_id): void
    {
        $timestamp = DateUtils::CurrentUnixEpochMillis();

        // timestamp can already exist, so we only increment counter if a new entry was created
        if ($this->client->zadd(RedisCacheService::TOP_ITEMS_QUEUE_KEY, ["$item_id:$timestamp" => $timestamp]) === 1) {
            $this->client->zincrby(RedisCacheService::TOP_ITEMS_COUNTS_KEY, 1, $item_id);
        }

        $this->expireViews($timestamp - RedisCacheService::TOP_ITEMS_HOURS_HISTORY * 3600 * 1000);
    }

    public function getTopViewedItemIds(): array
    {
        $this->expireViews(DateUtils::CurrentUnixEpochMillis() - RedisCacheService::TOP_ITEMS_HOURS_HISTORY * 3600 * 1000);

        $res = $this->client->zrevrange(RedisCacheService::TOP_ITEMS_COUNTS_KEY, 0, 9);
        return array_map('intval', $res);
    }

    private function expireViews(int $timestamp): void 
    {
        // pop expired views
        $txnResponses = $this->client->transaction()
            ->zrangebyscore(RedisCacheService::TOP_ITEMS_QUEUE_KEY, 0, $timestamp)
            ->zremrangebyscore(RedisCacheService::TOP_ITEMS_QUEUE_KEY, 0, $timestamp)
            ->execute();

        $expired_keys = $txnResponses[0];
        if (count($expired_keys) == 0) {
            return;
        }

        // group expired view counts by item
        $expire_counts = [];
        foreach ($expired_keys as $expired_key) {
            $expired_item_id = explode(":", $expired_key)[0];

            if (array_key_exists($expired_item_id, $expire_counts)) {
                $expire_counts[$expired_item_id]++;
            } else {
                $expire_counts[$expired_item_id] = 1;
            }
        }

        // commit expire counts
        $pipe = $this->client->pipeline();

        foreach ($expire_counts as $expired_item_id => $expire_count) {
            $pipe->zincrby(RedisCacheService::TOP_ITEMS_COUNTS_KEY, $expire_count * -1, $expired_item_id);
        }

        $pipe->execute();

        // remove empty counts
        $this->client->zremrangebyscore(RedisCacheService::TOP_ITEMS_COUNTS_KEY, "-inf", 0);
    }
}
