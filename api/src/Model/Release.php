<?php

namespace App\Model;

use App\Util\DateUtils;
use DateTime;
use JsonSerializable;

class Release implements JsonSerializable
{
    public string $shortName;
    public string $description;
    public DateTime $releaseDate;

    public function __construct(string $shortName, string $description, DateTime $releaseDate)
    {
        $this->shortName = $shortName;
        $this->description = $description;
        $this->releaseDate = $releaseDate;
    }

    public function jsonSerialize(): array
    {
        return [
            'short_name' => $this->shortName,
            'description' => $this->description,
            'release_date' => DateUtils::DateTimeToUtcIsoDate($this->releaseDate),
        ];
    }
}