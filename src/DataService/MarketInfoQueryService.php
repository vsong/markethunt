<?php

namespace App\DataService;

use App\DataTransferObject\ItemHeader;
use App\DataTransferObject\ItemMovement;
use App\DataTransferObject\ItemTotalVolume;
use App\Model\Event;
use App\Model\ItemInfo;
use App\Model\MarketDatapoint;
use App\Util\DateUtils;
use DateTime;
use PDO;

class MarketInfoQueryService
{
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * @return ItemHeader[] Item Headers ordered by Item ID ascending
     */
    public function getAllItemHeaders(): array {
        /** @var ItemHeader[] $result */
        $result = [];

        $statement = $this->db->query('SELECT * FROM v_item_headers');

        foreach ($statement as $row) {
            $itemMarketDatapoint = $row['date'] === null ? null : new MarketDatapoint(
                $row['item_id'],
                DateUtils::IsoDateToUtcDateTime($row['date']),
                $row['price'],
                $row['sb_price'],
                $row['raw_volume_day']);

            $itemInfo = new ItemInfo($row['item_id'], $row['name']);

            $result[] = new ItemHeader($itemInfo, $itemMarketDatapoint);
        }

        return $result;
    }

    /**
     * @param int $itemId
     * @return ItemInfo|null Returns ItemInfo if the item was found, or null if the item does not exist.
     */
    public function getItemInfo(int $itemId): ?ItemInfo {
        $statement = $this->db->prepare('SELECT `item_id`, `name` FROM item_meta WHERE `item_id` = :itemId');
        $statement->bindParam('itemId', $itemId);
        $statement->execute();
        $row = $statement->fetch();

        return ($row === false) ? null : new ItemInfo($row['item_id'], $row['name']);
    }

    /**
     * @param int $itemId
     * @return MarketDatapoint[] Array of MarketDatapoints sorted by date ascending, or empty if item is not found
     * or has no market history
     */
    public function getItemMarketHistory(int $itemId): array {
        /** @var MarketDatapoint[] $result */
        $result = [];

        $statement = $this->db->prepare('
        SELECT
            dp.`date`,
            raw_volume_day,
            dp.price,
            cast(dp.price / sbp.price as double) AS sb_price
        FROM
            daily_volume AS dv
        RIGHT JOIN daily_price AS dp USING(`date`, item_id)
        LEFT JOIN daily_price sbp ON
            dp.`date` = sbp.`date` AND sbp.item_id = 114
        WHERE
            dp.item_id = :itemId
        ORDER BY
            `date` ASC');
        $statement->bindParam('itemId', $itemId);
        $statement->execute();

        foreach ($statement->fetchAll() as $row) {
            $result[] = new MarketDatapoint(
                $itemId,
                DateUtils::IsoDateToUtcDateTime($row['date']),
                $row['price'],
                $row['sb_price'],
                $row['raw_volume_day']);
        }

        return $result;
    }

    /**
     * @return Event[] Array of Events sorted by start date ascending
     */
    public function getEvents(): array {
        $result[] = new Event("Ronza '20",
            DateUtils::IsoDateToUtcDateTime('2020-08-18'), DateUtils::IsoDateToUtcDateTime('2020-09-08'));
        $result[] = new Event("Hween '20",
            DateUtils::IsoDateToUtcDateTime('2020-10-14'), DateUtils::IsoDateToUtcDateTime('2020-11-03'));
        $result[] = new Event("GWH '20",
            DateUtils::IsoDateToUtcDateTime('2020-12-08'), DateUtils::IsoDateToUtcDateTime('2021-01-06'));
        $result[] = new Event("LNY '21",
            DateUtils::IsoDateToUtcDateTime('2021-02-09'), DateUtils::IsoDateToUtcDateTime('2021-02-23'));
        $result[] = new Event("BDay '21",
            DateUtils::IsoDateToUtcDateTime('2021-03-02'), DateUtils::IsoDateToUtcDateTime('2021-03-23'));
        $result[] = new Event("SEH '21",
            DateUtils::IsoDateToUtcDateTime('2021-03-30'), DateUtils::IsoDateToUtcDateTime('2021-04-27'));
        $result[] = new Event("KGA '21",
            DateUtils::IsoDateToUtcDateTime('2021-05-25'), DateUtils::IsoDateToUtcDateTime('2021-06-08'));
        $result[] = new Event("Jaq '21",
            DateUtils::IsoDateToUtcDateTime('2021-06-29'), DateUtils::IsoDateToUtcDateTime('2021-07-13'));
        $result[] = new Event("Ronza '21",
            DateUtils::IsoDateToUtcDateTime('2021-07-27'), DateUtils::IsoDateToUtcDateTime('2021-08-17'));
        $result[] = new Event("Hween '21",
            DateUtils::IsoDateToUtcDateTime('2021-10-13'), DateUtils::IsoDateToUtcDateTime('2021-11-02'));
        $result[] = new Event("GWH '21",
            DateUtils::IsoDateToUtcDateTime('2021-12-07'), DateUtils::IsoDateToUtcDateTime('2022-01-05'));
        $result[] = new Event("LNY '22",
            DateUtils::IsoDateToUtcDateTime('2022-01-31'), DateUtils::IsoDateToUtcDateTime('2022-02-15'));
        $result[] = new Event("BDay '22",
            DateUtils::IsoDateToUtcDateTime('2022-03-02'), DateUtils::IsoDateToUtcDateTime('2022-03-22'));
        $result[] = new Event("SEH '22",
            DateUtils::IsoDateToUtcDateTime('2022-03-29'), DateUtils::IsoDateToUtcDateTime('2022-04-26'));
        $result[] = new Event("Ronza '22",
            DateUtils::IsoDateToUtcDateTime('2022-05-25'), DateUtils::IsoDateToUtcDateTime('2022-06-15'));
        $result[] = new Event("Jet '22",
            DateUtils::IsoDateToUtcDateTime('2022-06-21'), DateUtils::IsoDateToUtcDateTime('2022-07-05'));
        $result[] = new Event("Hween '22",
            DateUtils::IsoDateToUtcDateTime('2022-10-12'), DateUtils::IsoDateToUtcDateTime('2022-11-02'));

        return $result;
    }
}