<?php

namespace App\Model;

use App\Util\DateUtils;
use DateTime;
use JsonSerializable;

class StockDatapoint implements JsonSerializable
{
    public int $itemId;
    public DateTime $timestamp;
    public ?int $bid;
    public ?int $ask;
    public int $supply;

    public function __construct(int $itemId, DateTime $timestamp, ?int $bid, ?int $ask, $supply)
    {
        $this->itemId = $itemId;
        $this->timestamp = $timestamp;
        $this->bid = $bid;
        $this->ask = $ask;
        $this->supply = $supply;
    }

    public function jsonSerialize() {
        return [
            'timestamp' => $this->timestamp->getTimestamp() * 1000,
            'bid' => $this->bid,
            'ask' => $this->ask,
            'supply' => $this->supply
        ];
    }
}