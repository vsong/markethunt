<?php

namespace App;

use App\Controller\FrontpageController;
use App\Controller\MarketAnalyticsController;
use App\Controller\MarketInfoController;
use App\Middleware\ApiAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Routes
{
    public static function AddRoutes(App $app) {
        $app->get('/', [FrontpageController::class, 'Frontpage']);

        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/items', [MarketInfoController::class, 'GetAllItemHeaders']);
            $group->get('/items/{itemId}', [MarketInfoController::class, 'GetItemMarketData']);
            $group->get('/items/{itemId}/stock', [MarketInfoController::class, 'GetItemStockData'])
                ->add(ApiAuthMiddleware::class);
            $group->get('/events', [MarketInfoController::class, 'GetEvents']);

            $group->group('/analytics', function (RouteCollectorProxy $group) {
                $group->get('/total-volumes/{fromDate}[/{toDate}]', [MarketAnalyticsController::class, 'GetTotalVolumes']);
                $group->get('/movers/{fromDate}[/{toDate}]', [MarketAnalyticsController::class, 'GetMarketMovement']);
            });
        })->add(Middleware::AllowAllCors());
    }
}