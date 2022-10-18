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
        foreach ($marketData as $dataPoint) {
            if ($itemInfo->itemId != $dataPoint->itemId) {
                throw new InvalidArgumentException("Item IDs of provided itemInfo and marketData do not match");
            }
        }

        $this->itemInfo = $itemInfo;
        $this->marketData = $marketData;
    }

    public function jsonSerialize() {
        $array = $this->itemInfo->jsonSerialize();
        $array['market_data'] = $this->marketData;
        return $array;
    }
}