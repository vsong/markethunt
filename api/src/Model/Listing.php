<?php

namespace App\Model;

use App\Util\DateUtils;
use DateTime;

class Listing implements \JsonSerializable
{
    public int $itemId;
    public float $sbPrice;
    public int $listingType;
    public bool $isSelling;
    public ?int $amount;
    public DateTime $timestamp;

    /**
     * @param int $itemId
     * @param float $sbPrice
     * @param int $listingType
     * @param bool $isSelling
     * @param int|null $amount
     * @param DateTime $date
     */
    public function __construct(int $itemId, float $sbPrice, int $listingType, bool $isSelling, ?int $amount, DateTime $timestamp)
    {
        $this->itemId = $itemId;
        $this->sbPrice = $sbPrice;
        $this->listingType = $listingType;
        $this->isSelling = $isSelling;
        $this->amount = $amount;
        $this->timestamp = $timestamp;
    }

    public function jsonSerialize(): array {
        return [
            'item_id' => $this->itemId,
            'sb_price' => $this->sbPrice,
            'listing_type' => $this->listingType,
            'is_selling' => $this->isSelling,
            'amount' => $this->amount,
            'timestamp' => $this->timestamp->getTimestamp() * 1000
        ];
    }
}