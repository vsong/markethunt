<?php

namespace App\Model;

class ItemInfo implements \JsonSerializable
{
    public int $itemId;
    public string $name;
    public bool $currentlyTradeable;

    public function __construct(int $itemId, string $name, bool $currentlyTradeable) {
        $this->itemId = $itemId;
        $this->name = $name;
        $this->currentlyTradeable = $currentlyTradeable;
    }

    public function jsonSerialize(): array {
        return [
            'item_id' => $this->itemId,
            'name' => $this->name,
            'currently_tradeable' => $this->currentlyTradeable
        ];
    }
}