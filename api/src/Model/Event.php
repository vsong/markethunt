<?php

namespace App\Model;

use App\Util\DateUtils;
use DateTime;
use JsonSerializable;

class Event implements JsonSerializable
{
    public string $shortName;
    public DateTime $startDate;
    public DateTime $endDate;

    public function __construct(string $shortName, DateTime $startDate, DateTime $endDate)
    {
        $this->shortName = $shortName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function jsonSerialize(): array
    {
        return [
            'short_name' => $this->shortName,
            'start_date' => DateUtils::DateTimeToUtcIsoDate($this->startDate),
            'end_date' => DateUtils::DateTimeToUtcIsoDate($this->endDate),
        ];
    }
}