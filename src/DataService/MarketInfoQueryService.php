<?php

namespace App\DataService;

use App\DataTransferObject\ItemHeader;
use App\Model\Event;
use App\Model\ItemInfo;
use App\Model\MarketDatapoint;
use App\Model\StockDatapoint;
use App\Util\DateUtils;
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

            $itemInfo = new ItemInfo($row['item_id'], $row['name'], $row['currently_tradeable']);

            $result[] = new ItemHeader($itemInfo, $itemMarketDatapoint);
        }

        return $result;
    }

    /**
     * @param int $itemId
     * @return ItemInfo|null Returns ItemInfo if the item was found, or null if the item does not exist.
     */
    public function getItemInfo(int $itemId): ?ItemInfo {
        $statement = $this->db->prepare('
        SELECT 
            `item_id`, 
            `name`, 
            `currently_tradeable` 
        FROM item_meta 
        WHERE `item_id` = :itemId AND `historically_tradeable` = 1');
        $statement->bindParam('itemId', $itemId);
        $statement->execute();
        $row = $statement->fetch();

        return ($row === false) ? null : new ItemInfo($row['item_id'], $row['name'], $row['currently_tradeable']);
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
     * @param int $itemId
     * @return StockDatapoint[] Array of StockDatapoints sorted by date ascending, or empty if item is not found
     * or has no stock history
     */
    public function getItemStockHistory(int $itemId): array {
        /** @var StockDatapoint[] $result */
        $result = [];

        $statement = $this->db->prepare('
        SELECT
            unix_timestamp(`timestamp`) as `unix_timestamp`, `bid`, `ask`, `sell_listing_volume`
        FROM
            bid_ask
        WHERE
            item_id = :itemId
        ORDER BY
            `timestamp` ASC');
        $statement->bindParam('itemId', $itemId);
        $statement->execute();

        foreach ($statement->fetchAll() as $row) {
            $result[] = new StockDatapoint(
                $itemId,
                DateUtils::TimestampToUtcDateTime($row['unix_timestamp']),
                $row['bid'],
                $row['ask'],
                $row['sell_listing_volume']
            );
        }

        return $result;
    }

    /**
     * @return Event[] Array of Events sorted by start date ascending
     */
    public function getEvents(): array {
        /** @var Event[] $result */
        $result = [];

        $statement = $this->db->prepare('
        SELECT
           `short_name`, `start_date`, `end_date`
        FROM
            events
        ORDER BY
            `start_date` ASC');
        $statement->execute();

        foreach ($statement->fetchAll() as $row) {
            $result[] = new Event(
                $row['short_name'],
                DateUtils::IsoDateToUtcDateTime($row['start_date']),
                DateUtils::IsoDateToUtcDateTime($row['end_date'])
            );
        }

        return $result;
    }
}