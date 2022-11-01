<?php

namespace App\DataTransferObject;

use App\Util\DateUtils;
use DateTime;
use JsonSerializable;

class ItemMovement implements JsonSerializable
{
    public int $itemId;
    public int $startPrice;
    public DateTime $startDate;
    public int $endPrice;
    public DateTime $endDate;
    public int $weeklyVolume;
    public int $weeklyGoldVolume;

    public function __construct(int $itemId, int $startPrice, DateTime $startDate, int $endPrice, DateTime $endDate, int $weeklyVolume, int $weeklyGoldVolume)
    {
        $this->itemId = $itemId;
        $this->startPrice = $startPrice;
        $this->startDate = $startDate;
        $this->endPrice = $endPrice;
        $this->endDate = $endDate;
        $this->weeklyVolume = $weeklyVolume;
        $this->weeklyGoldVolume = $weeklyGoldVolume;
    }

    public function getPercentChange(): float {
        return $this->endPrice / $this->startPrice * 100 - 100;
    }

    public function jsonSerialize(): array {
        return [
            'item_id' => $this->itemId,
            'start_price' => $this->startPrice,
            'start_date' => DateUtils::DateTimeToUtcIsoDate($this->startDate),
            'end_price' => $this->endPrice,
            'end_date' => DateUtils::DateTimeToUtcIsoDate($this->endDate),
            'percent_change' => $this->getPercentChange(),
            'weekly_volume' => $this->weeklyVolume,
            'weekly_gold_volume' => $this->weeklyGoldVolume
        ];
    }
}