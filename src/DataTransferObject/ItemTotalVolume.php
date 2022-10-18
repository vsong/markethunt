<?php

namespace App\DataTransferObject;

class ItemTotalVolume implements \JsonSerializable
{
    public int $itemId;
    public int $volume;
    public int $goldVolume;

    public function __construct(int $itemId, int $volume, int $goldVolume) {
        $this->itemId = $itemId;
        $this->volume = $volume;
        $this->goldVolume = $goldVolume;
    }

    public function jsonSerialize() {
        return [
            'item_id' => $this->itemId,
            'total_volume' => $this->volume,
            'total_gold_volume' => $this->goldVolume
        ];
    }
}