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
     * @return ListingCombination[] Item Info ordered by Item ID ascending
     */
    public function getAllListingCombinations(): array {
        /** @var ListingCombination[] $result */
        $result = [];

        $statement = $this->db->query('
            SELECT im.name, im.currently_tradeable, l.item_id, l.listing_type, lt.description 
            FROM item_meta im 
            RIGHT JOIN (SELECT DISTINCT item_id, listing_type FROM listing) l ON l.item_id = im.item_id 
            LEFT JOIN listing_type lt ON l.listing_type = lt.id
            ORDER BY l.listing_type, l.item_id');

        foreach ($statement as $row) {
            $result[] = new ListingCombination(
                new ItemInfo($row['item_id'], $row['name'], $row['currently_tradeable']),
                $row['listing_type'],
                $row['description']);
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