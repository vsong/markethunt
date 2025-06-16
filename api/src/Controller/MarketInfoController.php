<?php

namespace App\Controller;

use App\Cache\ICacheService;
use App\DataService\MarketInfoQueryService;
use App\DataTransferObject\ItemMarketHistory;
use App\DataTransferObject\ItemStockHistory;
use App\Util\DateUtils;
use App\Util\ResponseUtils;
use DateInterval;
use Psr\Container\ContainerInterface;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;

class MarketInfoController
{
    private MarketInfoQueryService $marketInfoQueryService;
    private ICacheService $cacheService;

    public function __construct(ContainerInterface $container) {
        $this->marketInfoQueryService = $container->get('marketInfoQueryService');
        $this->cacheService = $container->get('cacheService');
    }

    public function GetAllItemHeaders(Request $request, Response $response, $args) {
        return $response->withJson($this->marketInfoQueryService->getAllItemHeaders());
    }

    public function SearchItems(Request $request, Response $response, $args) {
        $query = trim($request->getQueryParam('query', ''));
        if (strlen($query) === 0) {
            return ResponseUtils::Respond400($response, 'Must provide a query');
        }

        $queryNormalized = $this->NormalizeSearchTerm($query);
        $items = $this->marketInfoQueryService->getAllItemHeaders();
        usort($items, function($a, $b) {
            $aName = $a->itemInfo->name;
            $bName = $b->itemInfo->name;

            if ($aName == $bName) {
                return 0;
            }

            return ($aName < $bName) ? -1 : 1;
        });

        $foundItems = array_values(array_filter($items, function($item) use ($queryNormalized) {
            $itemNameNormalized = $this->NormalizeSearchTerm($item->itemInfo->name);
            $itemAcronym = $this->GetAcronym($itemNameNormalized);

            return strpos($itemNameNormalized, $queryNormalized) !== false || strpos($itemAcronym, $queryNormalized) !== false;
        }));

        return $response->withJson($foundItems);
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

        if ($request->getQueryParam("plugin_ver") != null) {
            $this->cacheService->registerItemView($itemId);
        }

        $marketData = $this->marketInfoQueryService->getItemMarketHistory($itemId);
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

        $fromDateString = trim($request->getQueryParam('from', ''));
        $toDateString = trim($request->getQueryParam('to', ''));

        if (strlen($fromDateString) !== 0 || strlen($toDateString) !== 0) {
            if (DateUtils::ValidateISODate($fromDateString) && DateUtils::ValidateISODate($toDateString)) {
                $fromDate = DateUtils::IsoDateToUtcDateTime($fromDateString);
                $toDate = DateUtils::IsoDateToUtcDateTime($toDateString);
            } else {
                return ResponseUtils::Respond400($response, 'From and To dates must be in the format yyyy-mm-dd');
            }

            if ($fromDate >= $toDate) {
                return ResponseUtils::Respond400($response, '"From" date must be earlier than "To" date');
            }

            if ($fromDate < DateUtils::IsoDateToUtcDateTime('2021-12-01')) {
                return ResponseUtils::Respond404($response, 'Stock data does not exist before December 1, 2021');
            }

            $stockData = $this->marketInfoQueryService->getItemStockHistory($itemId, $fromDate, $toDate);
            return $response->withJson(new ItemStockHistory($itemInfo, $stockData));
        }

        $fineGrainedFromDate = DateUtils::CurrentUtcDateTime()->setTime(0, 0)->sub(new DateInterval('P180D'));
        $toDate = DateUtils::CurrentUtcDateTime();

        $courseGrainedStockData = $this->marketInfoQueryService->getDailyItemStockHistory($itemId, $fineGrainedFromDate);
        $fineGrainedStockData = $this->marketInfoQueryService->getItemStockHistory($itemId, $fineGrainedFromDate, $toDate);
        return $response->withJson(new ItemStockHistory($itemInfo, array_merge($courseGrainedStockData, $fineGrainedStockData)));
    }

    public function GetEvents(Request $request, Response $response, $args) {
        return $response->withJson($this->marketInfoQueryService->getEvents());
    }

    public function GetTrendingItems(Request $request, Response $response, $args) {
        $trendingIds = $this->cacheService->getTopViewedItemIds();
        $headers = $this->marketInfoQueryService->getAllItemHeaders();

        $lookup = [];
        foreach ($headers as $header) {
            $lookup[$header->itemInfo->itemId] = $header;
        }

        $trendingItemHeaders = [];
        foreach ($trendingIds as $itemId) {
            $trendingItemHeaders[] = $lookup[$itemId];
        }

        return $response->withJson($trendingItemHeaders);
    }

    private function NormalizeSearchTerm(string $str): string {
        $str = strtolower($str);
        $str = str_replace(['.', ',', '|', '+'], '', $str);
        $str = str_replace('-', ' ', $str);

        return $str;
    }

    private function GetAcronym(string $str): string {
        $acronym = '';

        foreach (explode(' ', $str) as $word) {
            if (strlen($word) > 0) {
                $acronym .= $word[0];
            }
        }

        return $acronym;
    }
}