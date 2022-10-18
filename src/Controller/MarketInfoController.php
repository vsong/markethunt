<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response as Response;

class AsdfController
{
    private \PDO $db;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('db');
    }

    public function Asdf(Request $request, Response $response, $args) {
        $id = intval($args['id']);

        $stmt = $this->db->prepare('SELECT * FROM v_latest_price WHERE item_id = :item_id');
        $stmt->execute(['item_id' => $id]);
        $metadata = $stmt->fetchAll();

        $data = [
            'id' => $id,
            'metadata' => $metadata
        ];

        return $response->withJson($data);
    }
}