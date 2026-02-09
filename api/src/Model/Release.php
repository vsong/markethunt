<?php

namespace App\Model;

use App\Util\DateUtils;
use DateTime;
use JsonSerializable;

class Release implements JsonSerializable
{
    public string $shortName;
    public DateTime $startDate;

    public function __construct(string $shortName, string $longName, DateTime $startDate)
    {
        $this->shortName = $shortName;
        $this->longName = $longName;
        $this->startDate = $startDate;
    }

    public function jsonSerialize(): array
    {
        return [
            'short_name' => $this->shortName,
            'long_name' => $this->longName,
            'start_date' => DateUtils::DateTimeToUtcIsoDate($this->startDate),
        ];
    }
}