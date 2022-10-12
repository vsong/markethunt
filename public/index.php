<?php
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->safeLoad();

$app = AppFactory::create();
$container = $app->getContainer();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(false, true, true);

$app->get('/test/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    return $response->withJson(array('id' => $id));
});

$app->run();