<?php

namespace App\DataTransferObject;

use App\Model\ItemInfo;
use App\Model\MarketDatapoint;
use InvalidArgumentException;

class ItemMarketHistory implements \JsonSerializable
{
    public ItemInfo $itemInfo;
    /** @var MarketDatapoint[] */
    public array $marketData;

    /**
     * @param ItemInfo $itemInfo
     * @param MarketDatapoint[] $marketData
     */
    public function __construct(ItemInfo $itemInfo, array $marketData) {
        $this->itemInfo = $itemInfo;
        $this->marketData = $marketData;
    }

    public function jsonSerialize(): array {
        return [
            'item_info' => $this->itemInfo,
            'market_data' => $this->marketData
        ];
    }
}