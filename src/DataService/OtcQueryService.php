<?php

namespace App\DataService;

use App\DataTransferObject\ItemHeader;
use App\DataTransferObject\Otc\ListingCombination;
use App\Model\Event;
use App\Model\ItemInfo;
use App\Model\Listing;
use App\Model\MarketDatapoint;
use App\Model\StockDatapoint;
use App\Util\DateUtils;
use PDO;

class OtcQueryService
{
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * @return ItemInfo[] Item Info ordered by Item ID ascending
     */
    public function getAllItems(): array {
        /** @var ItemInfo[] $result */
        $result = [];

        $statement = $this->db->query('SELECT * FROM item_meta ORDER BY item_id');

        foreach ($statement as $row) {
            $result[] = new ItemInfo($row['item_id'], $row['name'], $row['currently_tradeable']);
        }

        return $result;
    }

    /**
     * @return ListingCombination[] Item Info ordered by Item ID ascending
     */
    public function getAllListingCombinations(): array {
        /** @var ListingCombination[] $result */
        $result = [];

        $statement = $this->db->query('SELECT DISTINCT item_id, listing_type FROM listing ORDER BY item_id, listing_type');

        foreach ($statement as $row) {
            $result[] = new ListingCombination($row['item_id'], $row['listing_type']);
        }

        return $result;
    }

    /**
     * @param int $itemId
     * @param int $listingType
     * @return Listing[] Listing ordered by date ascending
     */
    public function getListings(int $itemId, int $listingType): array {
        /** @var Listing[] $result */
        $result = [];

        $statement = $this->db->prepare('
        SELECT l.*, UNIX_TIMESTAMP(m.created_on)  as timestamp
        FROM listing l LEFT JOIN message m ON l.message_id = m.id 
        WHERE l.item_id = :itemId AND l.listing_type = :listingType
        ORDER BY m.created_on');

        $statement->bindParam('itemId', $itemId);
        $statement->bindParam('listingType', $listingType);
        $statement->execute();


        foreach ($statement->fetchAll() as $row) {
            print($row['created_on']);

            $result[] = new Listing(
                $row['item_id'],
                $row['sb_price'],
                $row['listing_type'],
                $row['is_selling'] == 1,
                $row['amount'],
                DateUtils::TimestampToUtcDateTime($row['timestamp']));
        }

        return $result;
    }
}