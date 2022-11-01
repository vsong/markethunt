<?php

namespace App\Controller;

use App\DataService\MarketInfoQueryService;
use App\DataTransferObject\ItemMarketHistory;
use App\Util\DateUtils;
use App\Util\ResponseUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response as Response;

class MarketAnalyticsController
{
    private MarketInfoQueryService $marketInfoQueryService;

    public function __construct(ContainerInterface $container) {
        $this->marketInfoQueryService = $container->get('marketInfoQueryService');
    }

    public function GetTotalVolumes(Request $request, Response $response, $args) {
        $fromDateString = $args['fromDate'];
        $toDateString = $args['toDate'] ?? DateUtils::CurrentUtcIsoDate();

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

    public function GetMarketMovement(Request $request, Response $response, $args) {
        $fromDateString = $args['fromDate'];

        if (DateUtils::validateISODate($fromDateString)) {
            $fromDate = DateUtils::IsoDateToUtcDateTime($fromDateString);
        } else {
            return ResponseUtils::Respond400($response, 'Dates must be in the format yyyy-mm-dd');
        }

        return $response->withJson([
            'from' => DateUtils::DateTimeToUtcIsoDate($fromDate),
            'to' => DateUtils::CurrentUtcIsoDate(),
            'market_movement' => $this->marketInfoQueryService->getMarketMovement($fromDate)
        ]);
    }
}