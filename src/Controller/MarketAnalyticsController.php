<?php

namespace App\Controller;

use App\DataService\MarketAnalyticsQueryService;
use App\Util\DateUtils;
use App\Util\ResponseUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response as Response;

class MarketAnalyticsController
{
    private MarketAnalyticsQueryService $marketAnalyticsQueryService;

    public function __construct(ContainerInterface $container) {
        $this->marketAnalyticsQueryService = $container->get('marketAnalyticsQueryService');
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
            'total_volumes' => $this->marketAnalyticsQueryService->getTotalVolumes($fromDate, $toDate)
        ]);
    }

    public function GetMarketMovement(Request $request, Response $response, $args) {
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
            'market_movement' => $this->marketAnalyticsQueryService->getMarketMovement($fromDate, $toDate)
        ]);
    }
}