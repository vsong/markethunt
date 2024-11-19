<?php

namespace App;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Middleware
{
    public static function AllowAllCors(): callable {
        return function (Request $request, RequestHandler $handler) {
            $response = $handler->handle($request);
            return $response->withHeader('Access-Control-Allow-Origin', '*');
        };
    }
}