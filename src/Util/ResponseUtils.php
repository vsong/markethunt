<?php

namespace App\Util;

use Slim\Http\Response;

class ResponseUtils
{
    public static function Basic404Response(Response $response, string $message) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('404 not found: ' . $message);
    }
}