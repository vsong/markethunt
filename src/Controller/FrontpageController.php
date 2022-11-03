<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;

class FrontpageController
{
    public function __construct(ContainerInterface $container) {}

    public function Frontpage(Request $request, Response $response, $args) {
        $html = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../template/routes.html');

        $html = str_replace("{{markethunt_app_hostname}}", $_ENV['MARKETHUNT_APP_HOSTNAME'], $html);
        $html = str_replace("{{api_hostname}}", $_ENV['API_HOSTNAME'], $html);

        $response->getBody()->write($html);

        return $response;
    }
}