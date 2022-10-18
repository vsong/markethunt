<?php

namespace App;

use App\Controller\MarketInfoController;
use Slim\App;
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Routes
{
    public static function AddRoutes(App $app) {
        $app->get('/allItems', [MarketInfoController::class, 'GetAllItemHeaders']);
        $app->get('/item/{itemId}', [MarketInfoController::class, 'GetItem']);
    }
}