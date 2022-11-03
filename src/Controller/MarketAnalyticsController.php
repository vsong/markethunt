<?php

namespace App\Controller;

use App\DataService\MarketAnalyticsQueryService;
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

        $volumeData = $this->marketAnalyticsQueryService->getTotalVolumes($fromDate, $toDate);

        if ($request->getQueryParam('format') === 'csv') {
            return ResponseUtils::RespondCsv(
                $response,
                $volumeData,
                [
                    'item_id' => fn ($datapoint) => $datapoint->itemId,
                    'total_volume' => fn ($datapoint) => $datapoint->volume,
                    'total_gold_volume' => fn ($datapoint) => $datapoint->goldVolume
                ],
                "total_volumes");
        }

        return $response->withJson([
            'from' => DateUtils::DateTimeToUtcIsoDate($fromDate),
            'to' => DateUtils::DateTimeToUtcIsoDate($toDate),
            'total_volume' => array_reduce($volumeData, fn ($sum, $datapoint) => $sum + $datapoint->volume, 0),
            'total_gold_volume' => array_reduce($volumeData, fn ($sum, $datapoint) => $sum + $datapoint->goldVolume, 0),
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

        $movementData = $this->marketAnalyticsQueryService->getMarketMovement($fromDate, $toDate);

        if ($request->getQueryParam('format') === 'csv') {
            return ResponseUtils::RespondCsv(
                $response,
                $movementData,
                [
                    'item_id' => fn ($datapoint) => $datapoint->itemId,
                    'start_price' => fn ($datapoint) => $datapoint->startPrice,
                    'start_date' => fn ($datapoint) => DateUtils::DateTimeToUtcIsoDate($datapoint->startDate),
                    'end_price' => fn ($datapoint) => $datapoint->endPrice,
                    'end_date' => fn ($datapoint) => DateUtils::DateTimeToUtcIsoDate($datapoint->endDate),
                    'percent_change' => fn ($datapoint) => $datapoint->getPercentChange(),
                    'weekly_volume' => fn ($datapoint) => $datapoint->weeklyVolume,
                    'weekly_gold_volume' => fn ($datapoint) => $datapoint->weeklyGoldVolume,
                ],
                "movers");
        }

        return $response->withJson([
            'from' => DateUtils::DateTimeToUtcIsoDate($fromDate),
            'to' => DateUtils::DateTimeToUtcIsoDate($toDate),
            'market_movement' => $movementData
        ]);
    }
}