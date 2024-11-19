<?php

namespace App\DataTransferObject;

use App\Model\ItemInfo;
use App\Model\MarketDatapoint;
use InvalidArgumentException;

class ItemHeader implements \JsonSerializable
{
    public ItemInfo $itemInfo;
    public ?MarketDatapoint $latestMarketDatapoint;

    public function __construct(ItemInfo $itemInfo, ?MarketDatapoint $marketDatapoint) {
        if ($marketDatapoint != null && $itemInfo->itemId != $marketDatapoint->itemId) {
            throw new InvalidArgumentException("Item IDs of itemInfo and marketDatapoint do not match");
        }

        $this->itemInfo = $itemInfo;
        $this->latestMarketDatapoint = $marketDatapoint;
    }

    public function jsonSerialize(): array {
        return [
            'item_info' => $this->itemInfo,
            'latest_market_data' => $this->latestMarketDatapoint
        ];
    }
}