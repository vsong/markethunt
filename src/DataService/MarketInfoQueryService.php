<?php

namespace App\DataService;

use App\DataTransferObject\ItemHeader;
use App\DataTransferObject\ItemTotalVolume;
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
        $results = [];

        $statement = $this->db->query('SELECT * FROM v_item_headers');

        foreach ($statement as $row) {
            $itemMarketDatapoint = $row['date'] === null ? null : new MarketDatapoint(
                $row['item_id'],
                DateUtils::IsoDateToUtcDateTime($row['date']),
                $row['price'],
                $row['sb_price'],
                $row['raw_volume_day']);

            $itemInfo = new ItemInfo($row['item_id'], $row['name']);

            $results[] = new ItemHeader($itemInfo, $itemMarketDatapoint);
        }

        return $results;
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
        /** @var MarketDatapoint[] $datapoints */
        $datapoints = [];

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
            $datapoints[] = new MarketDatapoint(
                $itemId,
                DateUtils::IsoDateToUtcDateTime($row['date']),
                $row['price'],
                $row['sb_price'],
                $row['raw_volume_day']);
        }

        return $datapoints;
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @return ItemTotalVolume[]
     */
    public function getTotalVolumes(DateTime $fromDate, DateTime $toDate) {
        $totalVolumes = [];

        $statement = $this->db->prepare("
        WITH dgv as 
        (
            SELECT
                p.item_id,
                p.`date`,
                p.price * v.raw_volume_day as goldvol,
                v.raw_volume_day as volume 
            FROM
                daily_price p LEFT JOIN daily_volume v USING (item_id, `date`) 
            WHERE
                v.raw_volume_day > 0
            ORDER BY
                item_id DESC
        )
        SELECT
            dgv.item_id,
            sum(dgv.goldvol) as total_goldvol,
            sum(dgv.volume) as total_vol
        FROM
            dgv
        WHERE
            dgv.`date` >= :fromDate
            AND dgv.`date` <= :toDate
        GROUP BY
            item_id
        ORDER BY
            total_goldvol DESC");
        $statement->bindValue('fromDate', DateUtils::DateTimeToUtcIsoDate($fromDate));
        $statement->bindValue('toDate', DateUtils::DateTimeToUtcIsoDate($toDate));
        $statement->execute();

        foreach ($statement->fetchAll() as $row) {
            $totalVolumes[] = new ItemTotalVolume($row['item_id'], $row['total_vol'], $row['total_goldvol']);
        }

        return $totalVolumes;
    }
}