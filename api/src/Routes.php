<?php

namespace App;

use App\Controller\FrontpageController;
use App\Controller\MarketAnalyticsController;
use App\Controller\MarketInfoController;
use App\Controller\OtcController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Routes
{
    public static function AddRoutes(App $app) {
        $app->get('/', [FrontpageController::class, 'Frontpage']);

        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/items', [MarketInfoController::class, 'GetAllItemHeaders']);
            $group->get('/items/search', [MarketInfoController::class, 'SearchItems']);
            $group->get('/items/{itemId}', [MarketInfoController::class, 'GetItemMarketData']);
            $group->get('/items/{itemId}/stock', [MarketInfoController::class, 'GetItemStockData']);
            $group->get('/events', [MarketInfoController::class, 'GetEvents']);
            $group->get('/trending', [MarketInfoController::class, 'GetTrendingItems']);

            $group->group('/analytics', function (RouteCollectorProxy $group) {
                $group->get('/total-volumes/{fromDate}[/{toDate}]', [MarketAnalyticsController::class, 'GetTotalVolumes']);
                $group->get('/movers/{fromDate}[/{toDate}]', [MarketAnalyticsController::class, 'GetMarketMovement']);
            });

            $group->group('/otc', function (RouteCollectorProxy $group) {
                $group->get('/listings', [OtcController::class, 'GetAllListingCombinations']);
                $group->get('/listings/{listingType}/{itemId}', [OtcController::class, 'GetListings']);
            });
        })->add(Middleware::AllowAllCors());
    }
}