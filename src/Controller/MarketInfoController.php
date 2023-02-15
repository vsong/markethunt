<?php

namespace App\Controller;

use App\DataService\MarketInfoQueryService;
use App\DataTransferObject\ItemHeader;
use App\DataTransferObject\ItemMarketHistory;
use App\DataTransferObject\ItemStockHistory;
use App\Model\MarketDatapoint;
use App\Model\StockDatapoint;
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
                'item_id' => fn(ItemHeader $itemHeader) => $itemHeader->itemInfo->itemId,
                'name' => fn(ItemHeader $itemHeader) => $itemHeader->itemInfo->name,
                'latest_market_date' => function (ItemHeader $itemHeader) {
                    return $itemHeader->latestMarketDatapoint
                        ? DateUtils::DateTimeToUtcIsoDate($itemHeader->latestMarketDatapoint->date)
                        : null;
                },
                'latest_market_price' => function (ItemHeader $itemHeader) {
                    return $itemHeader->latestMarketDatapoint ? $itemHeader->latestMarketDatapoint->price : null;
                },
                'latest_market_sb_price' => function (ItemHeader $itemHeader) {
                    return $itemHeader->latestMarketDatapoint ? $itemHeader->latestMarketDatapoint->sbPrice : null;
                },
                'latest_market_volume' => function (ItemHeader $itemHeader) {
                    return $itemHeader->latestMarketDatapoint ? $itemHeader->latestMarketDatapoint->volume : null;
                }
            ]);
        }

        return $response->withJson($this->marketInfoQueryService->getAllItemHeaders());
    }

    public function GetItemMarketData(Request $request, Response $response, $args) {
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
                'date' => fn (MarketDatapoint $datapoint) => DateUtils::DateTimeToUtcIsoDate($datapoint->date),
                'price' => fn (MarketDatapoint $datapoint) => $datapoint->price,
                'sb_price' => fn (MarketDatapoint $datapoint) => $datapoint->sbPrice,
                'volume' => fn (MarketDatapoint $datapoint) => $datapoint->volume,
            ]);
        }

        return $response->withJson(new ItemMarketHistory($itemInfo, $marketData));
    }

    public function GetItemStockData(Request $request, Response $response, $args) {
        $itemId = filter_var($args['itemId'], FILTER_VALIDATE_INT);

        if ($itemId === false) {
            return ResponseUtils::Respond400($response, 'Item ID must be numeric');
        }

        $itemInfo = $this->marketInfoQueryService->getItemInfo($itemId);

        if ($itemInfo === null) {
            return ResponseUtils::Respond404($response, 'Item ID not found');
        }

        $stockData = $this->marketInfoQueryService->getItemStockHistory($itemId);

        if ($request->getQueryParam('format') === 'csv') {
            return ResponseUtils::RespondCsv($response, $stockData, [
                'timestamp' => fn (StockDatapoint $datapoint) => $datapoint->timestamp->getTimestamp() * 1000,
                'bid' => fn (StockDatapoint $datapoint) => $datapoint->bid,
                'ask' => fn (StockDatapoint $datapoint) => $datapoint->ask,
                'supply' => fn (StockDatapoint $datapoint) => $datapoint->supply
            ]);
        }

        return $response->withJson(new ItemStockHistory($itemInfo, $stockData));
    }

    public function GetEvents(Request $request, Response $response, $args) {
        return $response->withJson($this->marketInfoQueryService->getEvents());
    }
}