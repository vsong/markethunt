<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response as Response;

class FrontpageController
{
    public function __construct(ContainerInterface $container) {}

    public function Frontpage(Request $request, Response $response, $args) {
        $html = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../template/routes.html');

        $html = str_replace("replaceme", "replacement", $html);

        $response->getBody()->write($html);

        return $response;
    }
}