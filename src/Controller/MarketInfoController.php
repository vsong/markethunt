<?php

namespace App\Controller;

use App\DataService\MarketInfoQueryService;
use App\DataTransferObject\ItemMarketHistory;
use App\Util\CsvUtils;
use App\Util\DateUtils;
use App\Util\ResponseUtils;
use Psr\Container\ContainerInterface;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;

class MarketInfoController
{
    private MarketInfoQueryService $marketInfoQueryService;

    public function __construct(ContainerInterface $container) {
        $this->marketInfoQueryService = $container->get('marketInfoQueryService');
    }

    public function GetAllItemHeaders(Request $request, Response $response, $args) {
        $data = $this->marketInfoQueryService->getAllItemHeaders();

        if ($request->getQueryParam('format') === 'csv') {
            return ResponseUtils::RespondCsv($response, $data, [
                'item_id' => fn($itemHeader) => $itemHeader->itemInfo->itemId,
                'name' => fn($itemHeader) => $itemHeader->itemInfo->name,
                'latest_market_date' => function ($itemHeader) {
                    return $itemHeader->latestMarketDatapoint
                        ? DateUtils::DateTimeToUtcIsoDate($itemHeader->latestMarketDatapoint->date)
                        : null;
                },
                'latest_market_price' => function ($itemHeader) {
                    return $itemHeader->latestMarketDatapoint ? $itemHeader->latestMarketDatapoint->price : null;
                },
                'latest_market_sb_price' => function ($itemHeader) {
                    return $itemHeader->latestMarketDatapoint ? $itemHeader->latestMarketDatapoint->sbPrice : null;
                },
                'latest_market_volume' => function ($itemHeader) {
                    return $itemHeader->latestMarketDatapoint ? $itemHeader->latestMarketDatapoint->volume : null;
                }
            ]);
        }

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

        if ($request->getQueryParam('format') === 'csv') {
            return ResponseUtils::RespondCsv($response, $marketData, [
                'date' => fn ($datapoint) => DateUtils::DateTimeToUtcIsoDate($datapoint->date),
                'price' => fn ($datapoint) => $datapoint->price,
                'sb_price' => fn ($datapoint) => $datapoint->sbPrice,
                'volume' => fn ($datapoint) => $datapoint->volume,
            ]);
        }

        return $response->withJson(new ItemMarketHistory($itemInfo, $marketData));
    }

    public function GetEvents(Request $request, Response $response, $args) {
        return $response->withJson($this->marketInfoQueryService->getEvents());
    }
}