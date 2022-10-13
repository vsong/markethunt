<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response as Response;

class AsdfController
{
    private $datetime;

    public function __construct(ContainerInterface $container)
    {
        $this->datetime = $container->get('date');
    }

    public function Asdf(Request $request, Response $response, $args) {
        $id = $args['id'];

        $data = [
            'id' => $id,
            'date' => $this->datetime
        ];

        return $response->withJson($data);
    }
}