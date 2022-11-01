<?php

namespace App\DataService;

use App\DataTransferObject\ItemHeader;
use App\DataTransferObject\ItemMovement;
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
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @return ItemTotalVolume[]
     */
    public function getTotalVolumes(DateTime $fromDate, DateTime $toDate): array {
        /** @var ItemTotalVolume[] $result */
        $result = [];

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
            $result[] = new ItemTotalVolume($row['item_id'], $row['total_vol'], $row['total_goldvol']);
        }

        return $result;
    }

    /**
     * @param DateTime $fromDate
     * @return ItemMovement[]
     */
    public function getMarketMovement(DateTime $fromDate): array {
        /** @var ItemMovement[] $result */
        $result = [];

        // PDO does not allow us to use same parameter name twice, so have to use fromDate1 and fromDate2
        $statement = $this->db->prepare("
        WITH
        latest AS (
            SELECT
                item_id,
                price,
                date,
            	name
            FROM
                `v_latest_price`
            WHERE date >= :fromDate1
        ),
        past AS (
            SELECT
                p1.item_id,
                p1.price past_price,
                p1.DATE past_date
            FROM daily_price p1
            RIGHT JOIN (
                SELECT
                    item_id,
                    MAX(DATE) AS DATE
                FROM daily_price
                    WHERE DATE <= :fromDate2
                GROUP BY item_id
            ) p2 USING(item_id, DATE)
        ),
        weekly_volumes AS (
            SELECT
				dv.item_id,
				sum(dp.price * dv.raw_volume_day) AS weekly_gold_volume,
				sum(dv.raw_volume_day) AS weekly_volume
			FROM
				daily_volume dv
			INNER JOIN daily_price dp USING(item_id, DATE)
			WHERE dv.date >= (NOW() - INTERVAL 7 DAY)
			GROUP BY dv.item_id
        )
        SELECT * FROM (
            SELECT
                latest.name,
                latest.item_id,
                latest.price,
                latest.date,
                past.past_price,
                past.past_date,
                CAST(latest.price / past.past_price * 100 - 100 AS DOUBLE) AS change_pct,
				v.weekly_gold_volume,
				v.weekly_volume
            FROM latest
            INNER JOIN past USING(item_id)
            LEFT JOIN weekly_volumes v USING(item_id)
        ) AS tbl
        WHERE change_pct <> 0
        ORDER BY change_pct DESC, name ASC");
        $statement->bindValue('fromDate1', DateUtils::DateTimeToUtcIsoDate($fromDate));
        $statement->bindValue('fromDate2', DateUtils::DateTimeToUtcIsoDate($fromDate));
        $statement->execute();

        foreach ($statement->fetchAll() as $row) {
            $result[] = new ItemMovement(
                $row['item_id'],
                $row['past_price'],
                DateUtils::IsoDateToUtcDateTime($row['past_date']),
                $row['price'],
                DateUtils::IsoDateToUtcDateTime($row['date']),
                $row['weekly_volume'] ?? 0,
                $row['weekly_gold_volume'] ?? 0
            );
        }

        return $result;
    }
}