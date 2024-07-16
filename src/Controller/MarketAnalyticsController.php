<?php

namespace App\Controller;

use App\DataService\MarketAnalyticsQueryService;
use App\DataTransferObject\ItemTotalVolume;
use App\Util\DateUtils;
use App\Util\ResponseUtils;
use Psr\Container\ContainerInterface;
use Slim\Http\ServerRequest as Request;
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

        if (DateUtils::ValidateISODate($fromDateString) && DateUtils::ValidateISODate($toDateString)) {
            $fromDate = DateUtils::IsoDateToUtcDateTime($fromDateString);
            $toDate = DateUtils::IsoDateToUtcDateTime($toDateString);
        } else {
            return ResponseUtils::Respond400($response, 'Dates must be in the format yyyy-mm-dd');
        }

        if ($fromDate >= $toDate) {
            return ResponseUtils::Respond400($response, '"From" date must be earlier than "To" date');
        }

        if ($fromDate < DateUtils::IsoDateToUtcDateTime('2021-12-01')) {
            return ResponseUtils::Respond404($response, 'Volume data does not exist before December 1, 2021');
        }

        $volumeData = $this->marketAnalyticsQueryService->getTotalVolumes($fromDate, $toDate);

        return $response->withJson([
            'from' => DateUtils::DateTimeToUtcIsoDate($fromDate),
            'to' => DateUtils::DateTimeToUtcIsoDate($toDate),
            'total_volume' => array_reduce(
                $volumeData,
                fn ($sum, ItemTotalVolume $datapoint) => $sum + $datapoint->volume,
                0),
            'total_gold_volume' => array_reduce(
                $volumeData,
                fn ($sum, ItemTotalVolume $datapoint) => $sum + $datapoint->goldVolume,
                0),
            'total_volumes' => $volumeData
        ]);
    }

    public function GetMarketMovement(Request $request, Response $response, $args) {
        $fromDateString = $args['fromDate'];
        $toDateString = $args['toDate'] ?? DateUtils::CurrentUtcIsoDate();

        if (DateUtils::ValidateISODate($fromDateString) && DateUtils::ValidateISODate($toDateString)) {
            $fromDate = DateUtils::IsoDateToUtcDateTime($fromDateString);
            $toDate = DateUtils::IsoDateToUtcDateTime($toDateString);
        } else {
            return ResponseUtils::Respond400($response, 'Dates must be in the format yyyy-mm-dd');
        }

        if ($fromDate >= $toDate) {
            return ResponseUtils::Respond400($response, '"From" date must be earlier than "To" date');
        }

        if ($fromDate < DateUtils::IsoDateToUtcDateTime('2020-08-20')) {
            return ResponseUtils::Respond404($response, 'Price data does not exist before August 20, 2020');
        }

        $movementData = $this->marketAnalyticsQueryService->getMarketMovement($fromDate, $toDate);

        return $response->withJson([
            'from' => DateUtils::DateTimeToUtcIsoDate($fromDate),
            'to' => DateUtils::DateTimeToUtcIsoDate($toDate),
            'market_movement' => $movementData
        ]);
    }
}