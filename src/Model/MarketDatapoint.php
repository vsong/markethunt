<?php

namespace App\Model;

use App\Util\DateUtils;
use DateTime;
use JsonSerializable;

class MarketDatapoint implements JsonSerializable
{
    public int $itemId;
    public DateTime $date;
    public int $price;
    public float $sbPrice;
    public ?int $volume;

    public function __construct(int $itemId, DateTime $date, int $price, float $sbPrice, ?int $volume) {
        $this->itemId = $itemId;
        $this->date = $date;
        $this->price = $price;
        $this->sbPrice = $sbPrice;
        $this->volume = $volume;
    }

    public function jsonSerialize() {
        return [
            'date' => DateUtils::DateTimeToUtcIsoDate($this->date),
            'price' => $this->price,
            'sb_price' => $this->sbPrice,
            'volume' => $this->volume
        ];
    }
}