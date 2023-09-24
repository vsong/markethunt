<?php

namespace App\Controller;

use App\DataService\MarketInfoQueryService;
use App\DataService\OtcQueryService;
use App\DataTransferObject\ItemHeader;
use App\DataTransferObject\ItemMarketHistory;
use App\DataTransferObject\ItemStockHistory;
use App\Model\MarketDatapoint;
use App\Model\StockDatapoint;
use App\Util\DateUtils;
use App\Util\ResponseUtils;
use Psr\Container\ContainerInterface;
use Slim\Http\Response as Response;
use Slim\Http\ServerRequest as Request;

class OtcController
{
    private OtcQueryService $otcQueryService;

    public function __construct(ContainerInterface $container) {
        $this->otcQueryService = $container->get('otcQueryService');
    }

    public function GetAllItems(Request $request, Response $response, $args) {
        return $response->withJson($this->otcQueryService->getAllItems());
    }

    public function GetAllListingCombinations(Request $request, Response $response, $args) {
        return $response->withJson($this->otcQueryService->getAllListingCombinations());
    }

    public function GetListings(Request $request, Response $response, $args) {
        $listingType = filter_var($args['listingType'], FILTER_VALIDATE_INT);
        $itemId = filter_var($args['itemId'], FILTER_VALIDATE_INT);

        if ($itemId === false) {
            return ResponseUtils::Respond400($response, 'Item ID must be numeric');
        }

        if ($listingType === false) {
            return ResponseUtils::Respond400($response, 'Listing Type must be numeric');
        }

        $listings = $this->otcQueryService->getListings($itemId, $listingType);

        return $response->withJson($listings);
    }
}