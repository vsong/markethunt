<?php

namespace App\DataTransferObject\Otc;

class ListingCombination implements \JsonSerializable
{
    public int $itemId;
    public int $listingType;

    /**
     * @param int $itemId
     * @param int $listingType
     */
    public function __construct(int $itemId, int $listingType)
    {
        $this->itemId = $itemId;
        $this->listingType = $listingType;
    }

    public function jsonSerialize(): array {
        return [
            'item_id' => $this->itemId,
            'listing_type' => $this->listingType
        ];
    }
}