<?php

namespace App\Controller;

use App\DataService\MarketInfoQueryService;
use App\DataTransferObject\ItemMarketHistory;
use App\Util\DateUtils;
use App\Util\ResponseUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response as Response;

class MarketInfoController
{
    private MarketInfoQueryService $marketInfoQueryService;

    public function __construct(ContainerInterface $container) {
        $this->marketInfoQueryService = $container->get('marketInfoQueryService');
    }

    public function GetAllItemHeaders(Request $request, Response $response, $args) {
        return $response->withJson($this->marketInfoQueryService->getAllItemHeaders());
    }

    public function GetItem(Request $request, Response $response, $args) {
        $itemId = filter_var($args['itemId'], FILTER_VALIDATE_INT);

        if ($itemId === false) {
            return ResponseUtils::Respond400($response, 'Item ID must be numeric');
        }

        $itemInfo = $this->marketInfoQueryService->getItemInfo($itemId);

        if ($itemInfo === null) {
            return ResponseUtils::Respond404($response, 'Item ID not found');
        }

        $marketData = $this->marketInfoQueryService->getItemMarketHistory($itemId);

        return $response->withJson(new ItemMarketHistory($itemInfo, $marketData));
    }

    public function GetTotalVolumes(Request $request, Response $response, $args) {
        $fromDateString = $args['fromDate'];
        $toDateString = $args['toDate'] ?? DateUtils::DateTimeToUtcIsoDate(DateUtils::CurrentDateTimeUtc());

        if (DateUtils::validateISODate($fromDateString) && DateUtils::validateISODate($toDateString)) {
            $fromDate = DateUtils::IsoDateToUtcDateTime($fromDateString);
            $toDate = DateUtils::IsoDateToUtcDateTime($toDateString);
        } else {
            return ResponseUtils::Respond400($response, 'Dates must be in the format yyyy-mm-dd');
        }

        if ($fromDate >= $toDate) {
            return ResponseUtils::Respond400($response, '"From" date must be earlier than "To" date');
        }

        return $response->withJson([
            'from' => DateUtils::DateTimeToUtcIsoDate($fromDate),
            'to' => DateUtils::DateTimeToUtcIsoDate($toDate),
            'total_volumes' => $this->marketInfoQueryService->getTotalVolumes($fromDate, $toDate)
        ]);
    }
}