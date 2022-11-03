<?php

namespace App\Util;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;

class DateUtils
{
    /**
     * Validates that a string is a valid ISO date in the format yyyy-mm-dd
     * @param string $dateString The string to check
     * @return bool Result of validation
     */
    public static function ValidateISODate(string $dateString): bool
    {
        // check datestring is an actual date in ISO format
        $parsed_date = date_parse_from_format('Y-m-d', $dateString);

        if ($parsed_date['error_count'] > 0 || !checkdate((int)$parsed_date['month'], (int)$parsed_date['day'], (int)$parsed_date['year'])) {
            return false;
        }

        return true;
    }

    public static function CurrentUtcDateTime(): DateTime {
        return new DateTime('now', new DateTimeZone('UTC'));
    }

    public static function CurrentUtcIsoDate(): string {
        return self::DateTimeToUtcIsoDate(new DateTime('now', new DateTimeZone('UTC')));
    }

    /**
     * Given a DateTime object, returns an ISO date in the UTC timezone
     * @param DateTime $dateTime
     * @return string An ISO date in the format yyyy-mm-dd
     */
    public static function DateTimeToUtcIsoDate(DateTime $dateTime): string
    {
        $dateTimeClone = clone $dateTime;
        $dateTimeClone->setTimezone(new DateTimeZone('UTC'));
        return $dateTimeClone->format('Y-m-d');
    }

    /**
     * Given an ISO formatted date string, converts it to a DateTime object in the UTC timezone
     * @param string $dateString
     * @return DateTime
     */
    public static function IsoDateToUtcDateTime(string $dateString): DateTime
    {
        if (!self::ValidateISODate($dateString)) {
            throw new InvalidArgumentException('Provided an incorrectly formatted date string');
        }

        return new DateTime($dateString, new DateTimeZone('UTC'));
    }
}