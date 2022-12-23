<?php

namespace App\DataTransferObject;

use App\Model\ItemInfo;
use App\Model\StockDatapoint;

class ItemStockHistory implements \JsonSerializable
{
    public ItemInfo $itemInfo;
    /** @var StockDatapoint[] */
    public array $stockData;

    /**
     * @param ItemInfo $itemInfo
     * @param StockDatapoint[] $stockData
     */
    public function __construct(ItemInfo $itemInfo, array $stockData) {
        $this->itemInfo = $itemInfo;
        $this->stockData = $stockData;
    }

    public function jsonSerialize(): array {
        return [
            'item_info' => $this->itemInfo,
            'stock_data' => $this->stockData
        ];
    }
}