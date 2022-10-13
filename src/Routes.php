<?php

namespace App;

use App\Controller\AsdfController;
use Slim\App;
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Routes
{
    public static function AddRoutes(App $app) {
        $app->get('/test/{id}', [AsdfController::class, 'Asdf']);
    }
}