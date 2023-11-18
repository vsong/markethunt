<?php

namespace App\DataTransferObject\Otc;

use App\Model\ItemInfo;

class ListingCombination implements \JsonSerializable
{
    public ItemInfo $item;
    public int $listingType;
    public string $listingTypeDescription;

    /**
     * @param int $itemId
     * @param string $listingType
     */
    public function __construct(ItemInfo $item, int $listingType, string $listingTypeDescription)
    {
        $this->item = $item;
        $this->listingType = $listingType;
        $this->listingTypeDescription = $listingTypeDescription;
    }

    public function jsonSerialize(): array {
        return [
            'item' => $this->item,
            'listing_type' => $this->listingType,
            'listing_type_description' => $this->listingTypeDescription
        ];
    }
}