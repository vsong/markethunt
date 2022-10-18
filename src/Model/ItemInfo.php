<?php

namespace App\Model;

class ItemInfo implements \JsonSerializable
{
    public int $itemId;
    public string $name;

    public function __construct(int $itemId, string $name) {
        $this->itemId = $itemId;
        $this->name = $name;
    }

    public function jsonSerialize() {
        return [
            'item_id' => $this->itemId,
            'name' => $this->name
        ];
    }
}