<?php

namespace App;

use App\Controller\MarketAnalyticsController;
use App\Controller\MarketInfoController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Routes
{
    public static function AddRoutes(App $app) {
        $app->get('/items', [MarketInfoController::class, 'GetAllItemHeaders']);
        $app->get('/items/{itemId}', [MarketInfoController::class, 'GetItem']);
        $app->get('/events', [MarketInfoController::class, 'GetEvents']);

        $app->group('/analytics', function (RouteCollectorProxy $group) {
            $group->get('/total-volumes/{fromDate}[/{toDate}]', [MarketAnalyticsController::class, 'GetTotalVolumes']);
            $group->get('/movers/{fromDate}', [MarketAnalyticsController::class, 'GetMarketMovement']);
        });
    }
}