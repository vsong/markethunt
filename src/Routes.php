<?php

namespace App;

use App\Controller\MarketInfoController;
use Slim\App;
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

class Routes
{
    public static function AddRoutes(App $app) {
        $app->get('/all-items', [MarketInfoController::class, 'GetAllItemHeaders']);
        $app->get('/item/{itemId}', [MarketInfoController::class, 'GetItem']);
        $app->group('/analytics', function (RouteCollectorProxy $group) {
            $group->get('/total-volumes/{fromDate}[/{toDate}]', [MarketInfoController::class, 'GetTotalVolumes']);
        });
    }
}