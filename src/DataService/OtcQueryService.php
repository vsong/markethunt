<?php

namespace App\DataService;

use App\DataTransferObject\Otc\ListingCombination;
use App\Model\ItemInfo;
use App\Model\Listing;
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
        SELECT item_id, sb_price, listing_type, is_selling, amount, UNIX_TIMESTAMP(timestamp) as timestamp
        FROM listing WHERE item_id = :itemId AND listing_type = :listingType ORDER BY timestamp');

        $statement->bindParam('itemId', $itemId);
        $statement->bindParam('listingType', $listingType);
        $statement->execute();

        foreach ($statement->fetchAll() as $row) {
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