<?php

namespace App\Cache;

interface ICacheService
{
    /**
     * @param string $key
     * @return string|null The value if key exists, otherwise returns null
     */
    public function get(string $key): ?string;

    /**
     * @param string $key
     * @param string $value
     * @param int $timeoutSeconds
     * @return void
     */
    public function set(string $key, string $value, int $timeoutSeconds = 60);

    /**
     * @param int $item_id
     * @return void
     */
    public function registerItemView(int $item_id): void;

    /**
     * @return int[]
     */
    public function getTopViewedItemIds(): array;
}