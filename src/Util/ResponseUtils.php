<?php

namespace App\Util;

use Slim\Http\Response;

class ResponseUtils
{
    public static function Respond400(Response $response, string $message) {
        return $response->withStatus(400)
            ->withHeader('Content-Type', 'text/html')
            ->write('400 Invalid Request: ' . $message);
    }

    public static function Respond404(Response $response, string $message) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('404 not found: ' . $message);
    }
}